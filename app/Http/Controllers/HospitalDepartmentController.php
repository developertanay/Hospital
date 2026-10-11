<?php
namespace App\Http\Controllers;
class HospitalDepartmentController extends HospitalMasterCrudController
{
    protected $model = \App\Models\HospitalDepartment::class;
    protected $menu = 'HospitalDepartment';
    protected $title = 'Hospital Departments';
    protected $field = null;
}
