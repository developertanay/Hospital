<?php
namespace App\Http\Controllers;
class AmbulanceCategoryController extends HospitalMasterCrudController
{
    protected $model = \App\Models\AmbulanceCategory::class;
    protected $menu = 'AmbulanceCategory';
    protected $title = 'Ambulance Categories';
    protected $field = 'name';
}
