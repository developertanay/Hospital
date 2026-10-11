<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AmbulanceDriver extends Model
{
    protected $table='ambulance_drivers';
    protected $guarded=[];
    public $incrementing=false;
    protected $keyType='string';
    protected $casts=['on_duty'=>'boolean','follow_pickup'=>'boolean','seen_at'=>'integer','location_at'=>'integer'];
    public function user() { return $this->belongsTo(User::class); }
    public function ambulance() { return $this->belongsTo(Ambulance::class); }
}
