<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AmbulanceTrip extends Model
{
    protected $table='ambulance_trips';
    protected $guarded=[];
    public $incrementing=false;
    protected $keyType='string';
    protected $casts=['payload'=>'array','owner_id'=>'integer'];
}
