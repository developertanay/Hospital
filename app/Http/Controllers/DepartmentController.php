<?php
namespace App\Http\Controllers;
class DepartmentController extends HospitalMasterCrudController
{
    protected $model = \App\Models\Department::class;
    protected $menu = 'Department';
    protected $title = 'Department Master';
    protected $field = 'department_name';
}
