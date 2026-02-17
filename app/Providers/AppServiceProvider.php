<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Modules;
use Auth;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // dd(Auth::user());
        //
        // $role_id = !empty(Auth::user()->role_id)?Auth::user()->role_id:'';
        // $college_id = !empty(Auth::user()->college_id)?Auth::user()->college_id:'';
        // dd($college_id, $role_id);
        // // die();

        // //get modules here and share globally on view
        // $module_arr = Modules::getAssignedModules();
        // \View::share('module_arr', $module_arr);
    }
}
