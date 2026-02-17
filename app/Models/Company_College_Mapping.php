<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company_College_Mapping extends Model
{
    protected $table = "company_college_mapping";
    protected $guarded = [];

    public static function pluckCompanyFromCollegeId($college_id) {
        //this function is used for getting companies made by the college
        //it is not mandatory that if a company is mapped to a college then it is also made by the college
        return Company_College_Mapping::join('users', 'company_college_mapping.created_by', 'users.id')
                                    ->where('users.college_id', $college_id)
                                    ->pluck('company_college_mapping.company_id')
                                    ->toArray();
    }
}

