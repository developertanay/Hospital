<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CareEvent extends Model
{
    protected $table = 'care_events';
    protected $guarded = [];
    public $timestamps = false;
    protected $casts = ['at'=>'integer'];
}
