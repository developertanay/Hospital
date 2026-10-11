<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CareBed extends Model
{
    protected $table = 'care_beds';
    protected $guarded = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';
    public function hospital() { return $this->belongsTo(Hospital::class); }
    public function type() { return $this->belongsTo(bedtype::class, 'bed_type_id'); }
}
