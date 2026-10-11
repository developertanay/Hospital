<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Ambulance extends Model
{
    protected $table = 'ambulance_details';
    protected $guarded = [];
    public function category() { return $this->belongsTo(AmbulanceCategory::class,'ambulance_category_id'); }
    public function hospital() { return $this->belongsTo(Hospital::class); }
}
