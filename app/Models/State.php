<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $table = "state_mast";
    protected $guarded = [];

    public static function getAllRecords() {
        return State::where('status','!=',9)
                    ->orderBy('state_name', 'asc')
                    ->get();
    }
    public static function pluckCodeAndName() {
        return State::where('status','!=', 9)
                    ->orderBy('state_name', 'asc')
                    ->pluck('state_name', 'id');
    }
    public static function pluckActiveCodeAndName() {
        return State::where('status', 1)
                    ->orderBy('state_name', 'asc')
                    ->pluck('state_name', 'id');
    }

    public static function getDataFromId($id) {
        return State::where('id', $id)
                    ->first();
                }
}
