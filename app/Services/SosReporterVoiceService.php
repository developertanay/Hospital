<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/** Private, short AAC recordings made automatically by the SOS sender's phone. */
class SosReporterVoiceService
{
    public function validate(Request $request)
    {
        $input=$request->validate([
            'reporter_voice_base64'=>'required|string|max:2796204',
            'reporter_voice_duration_ms'=>'required|integer|between:9000,11000',
        ]);
        $bytes=base64_decode($input['reporter_voice_base64'],true);
        if ($bytes===false || strlen($bytes)<64 || strlen($bytes)>2097152) $this->invalid();
        $duration=$this->duration($bytes);
        if ($duration<9000 || $duration>11000 || abs($duration-$input['reporter_voice_duration_ms'])>1000) $this->invalid();
        return ['bytes'=>$bytes,'duration_ms'=>$duration];
    }

    private function invalid()
    {
        throw ValidationException::withMessages(['reporter_voice_base64'=>'A complete 10-second AAC voice recording (M4A, up to 2 MB) is required. Please retry SOS.']);
    }

    // Check the container, codec and actual sample timing rather than trusting a filename or client duration.
    private function duration($bytes)
    {
        $top=$this->boxes($bytes,0,strlen($bytes));
        $ftyp=$this->one($top,'ftyp'); $moov=$this->one($top,'moov'); $mdat=$this->one($top,'mdat');
        if ($ftyp['end']-$ftyp['data']<8 || $mdat['end']-$mdat['data']<1) $this->invalid();
        $brands=substr($bytes,$ftyp['data'],4).substr($bytes,$ftyp['data']+8,$ftyp['end']-$ftyp['data']-8);
        if (!preg_match('/(?:M4A |isom|iso2|mp41|mp42)/',$brands)) $this->invalid();
        $movie=$this->boxes($bytes,$moov['data'],$moov['end']);
        $tracks=array_values(array_filter($movie,fn($box)=>$box['type']==='trak'));
        if (count($tracks)!==1) $this->invalid(); // No video, extra tracks or external media.
        $track=$this->boxes($bytes,$tracks[0]['data'],$tracks[0]['end']);
        $mdia=$this->one($track,'mdia'); $media=$this->boxes($bytes,$mdia['data'],$mdia['end']);
        $handler=$this->one($media,'hdlr');
        if ($handler['end']-$handler['data']<12 || substr($bytes,$handler['data']+8,4)!=='soun') $this->invalid();
        $header=$this->one($media,'mdhd');
        if ($header['end']-$header['data']<1) $this->invalid();
        $version=ord($bytes[$header['data']]);
        if (!in_array($version,[0,1],true) || $header['end']-$header['data']<($version?32:20)) $this->invalid();
        $scale=$this->u32($bytes,$header['data']+($version?20:12));
        $ticks=$version?$this->u64($bytes,$header['data']+24):$this->u32($bytes,$header['data']+16);
        if ($scale<8000 || $scale>96000 || $ticks<=0) $this->invalid();
        $minf=$this->one($media,'minf'); $info=$this->boxes($bytes,$minf['data'],$minf['end']);
        $dinf=$this->one($info,'dinf'); $references=$this->boxes($bytes,$dinf['data'],$dinf['end']);
        $dref=$this->one($references,'dref');
        if ($dref['end']-$dref['data']<8 || $this->u32($bytes,$dref['data']+4)!==1) $this->invalid();
        $entries=$this->boxes($bytes,$dref['data']+8,$dref['end']); $url=$this->one($entries,'url ');
        if (count($entries)!==1 || $url['end']-$url['data']!==4 || $this->u32($bytes,$url['data'])!==1) $this->invalid();
        $stbl=$this->one($info,'stbl'); $table=$this->boxes($bytes,$stbl['data'],$stbl['end']);
        $description=$this->one($table,'stsd');
        if ($description['end']-$description['data']<8 || $this->u32($bytes,$description['data']+4)!==1) $this->invalid();
        $formats=$this->boxes($bytes,$description['data']+8,$description['end']); $audio=$this->one($formats,'mp4a');
        if (count($formats)!==1 || $audio['end']-$audio['data']<28 || $this->u16($bytes,$audio['data']+6)!==1 || $this->u16($bytes,$audio['data']+8)!==0) $this->invalid();
        $channels=$this->u16($bytes,$audio['data']+16); $rate=$this->u32($bytes,$audio['data']+24)>>16;
        if (!in_array($channels,[1,2],true) || $rate!==$scale) $this->invalid();
        $codec=$this->one($this->boxes($bytes,$audio['data']+28,$audio['end']),'esds');
        $this->aac($bytes,$codec,$rate,$channels);
        $timing=$this->one($table,'stts'); $timeEntries=$this->rows($bytes,$timing,8,64);
        $sampleCount=0; $sampleTicks=0;
        foreach ($timeEntries as $row) {
            $count=$this->u32($row,0); $delta=$this->u32($row,4);
            if ($count<1 || $count>5000 || $delta<1 || $delta>1024) $this->invalid();
            $sampleCount+=$count; $sampleTicks+=$count*$delta;
        }
        if ($sampleCount<60 || $sampleCount>5000 || abs($ticks-$sampleTicks)>1024) $this->invalid();
        $sizeBox=$this->one($table,'stsz');
        if ($sizeBox['end']-$sizeBox['data']<12 || $this->u32($bytes,$sizeBox['data']+8)!==$sampleCount) $this->invalid();
        $constant=$this->u32($bytes,$sizeBox['data']+4); $sizes=[];
        if ($sizeBox['end']-$sizeBox['data']!==12+($constant?0:$sampleCount*4)) $this->invalid();
        for ($i=0;$i<$sampleCount;$i++) {
            $size=$constant?:$this->u32($bytes,$sizeBox['data']+12+$i*4);
            if ($size<1 || $size>8192) $this->invalid();
            $sizes[]=$size;
        }
        $offsetBoxes=array_values(array_filter($table,fn($box)=>in_array($box['type'],['stco','co64'],true)));
        if (count($offsetBoxes)!==1) $this->invalid();
        $offsetBox=$offsetBoxes[0]; $wide=$offsetBox['type']==='co64';
        $offsetRows=$this->rows($bytes,$offsetBox,$wide?8:4,5000);
        $mapping=$this->rows($bytes,$this->one($table,'stsc'),12,5000); $map=[]; $last=0;
        foreach ($mapping as $row) {
            $first=$this->u32($row,0); $count=$this->u32($row,4);
            if ($first<1 || $first<=$last || $first>count($offsetRows) || $count<1 || $count>$sampleCount || $this->u32($row,8)!==1) $this->invalid();
            $map[]=['first'=>$first,'count'=>$count]; $last=$first;
        }
        if ($map[0]['first']!==1) $this->invalid();
        $sample=0; $mapIndex=0; $previousEnd=$mdat['data'];
        foreach ($offsetRows as $index=>$row) {
            while (isset($map[$mapIndex+1]) && $map[$mapIndex+1]['first']<=$index+1) $mapIndex++;
            $offset=$wide?$this->u64($row,0):$this->u32($row,0); $length=0;
            for ($i=0;$i<$map[$mapIndex]['count'];$i++) {
                if (!isset($sizes[$sample])) $this->invalid();
                $length+=$sizes[$sample++];
            }
            if ($offset<$previousEnd || $offset+$length>$mdat['end']) $this->invalid();
            $previousEnd=$offset+$length;
        }
        if ($sample!==$sampleCount) $this->invalid();
        return (int)round($sampleTicks*1000/$scale);
    }

    private function aac($bytes,$box,$rate,$channels)
    {
        if ($box['end']-$box['data']<6) $this->invalid();
        $position=$box['data']+4; $es=$this->descriptor($bytes,$position,$box['end'],3);
        if ($es['end']-$es['data']<3) $this->invalid();
        $position=$es['data']+3; $flags=ord($bytes[$es['data']+2]);
        if ($flags&0xe0) $this->invalid(); // External URL/stream dependency is not a phone recording.
        $config=$this->descriptor($bytes,$position,$es['end'],4);
        if ($config['end']-$config['data']<13 || ord($bytes[$config['data']])!==0x40 || (ord($bytes[$config['data']+1])>>2)!==5) $this->invalid();
        $position=$config['data']+13; $specific=$this->descriptor($bytes,$position,$config['end'],5);
        if ($specific['end']-$specific['data']<2) $this->invalid();
        $bits=$this->u16($bytes,$specific['data']); $object=$bits>>11; $index=($bits>>7)&15; $channel=($bits>>3)&15;
        $rates=[96000,88200,64000,48000,44100,32000,24000,22050,16000,12000,11025,8000,7350];
        if ($object!==2 || !isset($rates[$index]) || $rates[$index]!==$rate || $channel!==$channels) $this->invalid();
    }

    private function descriptor($bytes,&$position,$end,$tag)
    {
        if ($position+2>$end || ord($bytes[$position++])!==$tag) $this->invalid();
        $length=0; $done=false;
        for ($i=0;$i<4;$i++) {
            if ($position>=$end) $this->invalid();
            $part=ord($bytes[$position++]); $length=($length<<7)|($part&127);
            if (!($part&128)) { $done=true; break; }
        }
        if (!$done || $position+$length>$end) $this->invalid();
        $result=['data'=>$position,'end'=>$position+$length]; $position+=$length; return $result;
    }

    private function rows($bytes,$box,$width,$limit)
    {
        if ($box['end']-$box['data']<8) $this->invalid();
        $count=$this->u32($bytes,$box['data']+4);
        if ($count<1 || $count>$limit || $box['end']-$box['data']!==8+$count*$width) $this->invalid();
        $rows=[];
        for ($i=0;$i<$count;$i++) $rows[]=substr($bytes,$box['data']+8+$i*$width,$width);
        return $rows;
    }

    private function boxes($bytes,$start,$end)
    {
        $result=[];
        while ($start<$end) {
            if (count($result)>=1000 || $end-$start<8) $this->invalid();
            $size=$this->u32($bytes,$start); $type=substr($bytes,$start+4,4); $header=8;
            if ($size===1) { if ($end-$start<16) $this->invalid(); $size=$this->u64($bytes,$start+8); $header=16; }
            if ($size===0) $size=$end-$start;
            if ($size<$header || $size>$end-$start) $this->invalid();
            $result[]=['type'=>$type,'data'=>$start+$header,'end'=>$start+$size]; $start+=$size;
        }
        return $result;
    }

    private function one($boxes,$type)
    {
        $matches=array_values(array_filter($boxes,fn($box)=>$box['type']===$type));
        if (count($matches)!==1) $this->invalid();
        return $matches[0];
    }

    private function u16($bytes,$offset) { return unpack('n',substr($bytes,$offset,2))[1]; }
    private function u32($bytes,$offset) { return unpack('N',substr($bytes,$offset,4))[1]; }
    private function u64($bytes,$offset) { $high=$this->u32($bytes,$offset); if ($high!==0) $this->invalid(); return $this->u32($bytes,$offset+4); }

    public function store($recording)
    {
        $path='sos-reporter-voices/'.bin2hex(random_bytes(16)).'.m4a'; $disk=Storage::disk('local');
        try { if (!$disk->put($path,$recording['bytes'])) throw new RuntimeException('Voice write failed.'); }
        catch (\Throwable $e) { $disk->delete($path); throw new RuntimeException('Unable to save the SOS voice recording. Please retry.',0,$e); }
        return ['path'=>$path,'mime'=>'audio/mp4','duration_ms'=>$recording['duration_ms'],'stored_at'=>time()];
    }

    public function delete($voice) { if (!empty($voice['path'])) Storage::disk('local')->delete($voice['path']); }

    public function response($voice)
    {
        $path=$voice['path']??''; $disk=Storage::disk('local');
        abort_unless(preg_match('#^sos-reporter-voices/[a-f0-9]{32}\.m4a$#',$path) && $disk->exists($path),404);
        // BinaryFileResponse supports byte ranges for authenticated browser audio playback.
        return response()->file($disk->path($path),[
            'Content-Type'=>'audio/mp4','Cache-Control'=>'private, no-store',
            'X-Audio-Duration-Ms'=>(string)$voice['duration_ms'],'X-Content-Type-Options'=>'nosniff',
            'Content-Disposition'=>'inline; filename="sos-sender.m4a"','Content-Security-Policy'=>"default-src 'none'",
        ]);
    }
}
