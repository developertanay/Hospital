<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
// use DB;

class Module extends Model 
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "modules";
    protected $guarded = [];
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    public static function getAllRecords() {
        return Module::where('status','!=',9)
                    ->orderBy('sequence', 'asc')
                    ->get();
    }

    public static function pluckCodeAndName() {
        return Module::where('status','!=', 9)
                    ->pluck('name', 'id');
    }

    public static function pluckActiveParent() {
        return Module::where('status', 1)
                    ->where('url', '#')
                    ->pluck('name', 'id');
    }
    public static function getDataFromId($id) {
        return Module::where('id', $id)
                    ->first();
                }

    public static function updateDataFromId($id, $arr_to_update) {
        return Module::where('id', $id)
                    ->update($arr_to_update);
    }       

    public static function getModulesArr() {
        // $data = Module::where('status',1)->orderBy('sequence', 'asc')->get();
        // $parent_arr = [];
        // $child_arr = [];
        // foreach($data as $key => $value) {
        //     if($value->parent_id == NULL) {
        //         $parent_arr[$value->id]['name'] = $value->name;
        //         $parent_arr[$value->id]['icon'] = $value->icon;
        //         $parent_arr[$value->id]['url'] = $value->url;
        //     }
        //     else {
        //         $child_arr[$value->parent_id][$value->id]['name'] = $value->name;
        //         $child_arr[$value->parent_id][$value->id]['icon'] = $value->icon;
        //         $child_arr[$value->parent_id][$value->id]['url'] = $value->url;
        //     }
        // }   
             
        // dd($data);
    }     

    public static function pluckUrlAndNameByCollege($college_id) {
        return Modules::where('status','!=', 9)
                    ->where('college_id', $college_id)
                    ->pluck('name', 'url');
    }

}