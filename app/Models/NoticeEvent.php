<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoticeEvent extends Model
{
  
    protected $table='notice_event_module';
    protected $guarded=[];
    public $timestamps=false;
    public static function updateDataFromId($id, $arr_to_update) {
      return NoticeEvent::where('id', $id)
                  ->update($arr_to_update);
  }
}
 