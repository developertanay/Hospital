<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
// use DB;

class Department extends Model 
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "department_mast";
    protected $guarded = [];
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    public static function getAllRecords() {
        return Department::where('status','!=',9)
                    ->orderBy('department_name', 'asc')
                    ->get();
    }

    public static function pluckCodeAndName() {
        return Department::where('status','!=', 9)
                    ->pluck('department_name', 'id');
    }

    public static function pluckActiveCodeAndName() {
        return Department::whereNotIn('status', [9,2])
                    ->pluck('department_name', 'id');
    }


    public static function getActiveDepartments($college_id) {
        return Department::where('college_id', $college_id)
                        ->whereNotIn('status', [9,2])
                    ->pluck('department_name', 'id');
    }

    public static function getDepartmentsOfCollege($college_id) {
        return Department::where('college_id', $college_id)
                        ->whereNotIn('status', [9])
                    ->pluck('department_name', 'id');
    }

    public static function getDataFromId($id) {
        return Department::where('id', $id)
                    ->first();
    }
    public static function getDepartmentFromId($id) {
        return Department::where('id', $id)
                           ->pluck('department_name','id'); 
    }
    public static function getDeptById($dept_ids_arr) {
        return Department::whereIn('id',$dept_ids_arr)
                          ->pluck('department_name','id');
    }
    public static function updateDataFromId($id, $arr_to_update) {
        return Department::where('id', $id)
                    ->update($arr_to_update);
    }

    public static function getDepartmentsFromUniversity($university_id) {
        return Department::where('university_id', $university_id)
                    ->whereNotIn('status', [9])
                    ->pluck('department_name', 'id');
    }
    
    public static function getActiveDepartmentsFromUniversity($university_id) {
        return Department::where('university_id', $university_id)
                    ->whereNotIn('status', [2,9])
                    ->pluck('department_name', 'id');
    }

    public static function getCount() {
        // return Department::where('status','!=',9)->count();
        return Department::where('status',1)->count();
    }

    public static function getRemainingActiveDepartmentsFromUniversity($university_id, $dept_arr) {
        return Department::where('university_id', $university_id)
                    ->whereNotIn('id', $dept_arr)
                    ->whereNotIn('status', [2,9])
                    ->pluck('department_name', 'id');
    }

    public static function getDepartmentFromIds($departments_id){
            return Department::whereNotIn('status', [2,9])
                            ->whereIn('id',$departments_id)
                            ->pluck('department_name', 'id');

    }
}