<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class MobileController extends Controller
{
    public function health() { DB::connection()->getPdo(); return response()->json(['service'=>'CareRoute','mobile_api'=>1,'mock_data'=>false,'storage'=>'mysql']); }

    public function login(Request $request)
    {
        $input=$request->validate(['email'=>'required|string|max:255','password'=>'required|string|max:255']);
        $key='mobile-login:'.hash('sha256',strtolower($input['email']).'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key,5)) return response()->json(['message'=>'Too many login attempts. Retry in '.RateLimiter::availableIn($key).' seconds.'],429);
        $user=User::where('email',$input['email'])->first();
        if (!$user || !Hash::check($input['password'],$user->password) || !RoleAccess::forUser($user)) {
            RateLimiter::hit($key,60);
            return response()->json(['message'=>'Invalid login details or account not enabled for CareRoute.'],401);
        }
        RateLimiter::clear($key);
        return $this->issue($user);
    }

    public function register(Request $request)
    {
        $input=$request->validate(['name'=>'required|string|max:80','email'=>'required|email|max:255|unique:users,email','password'=>'required|string|min:8|max:255|confirmed']);
        $role=DB::table('role_mast')->where('name','Patient Requester')->where('college_id',1)->value('id');
        if (!$role) return response()->json(['message'=>'Patient registration is not configured. Contact your administrator.'],409);
        $user=User::create(['name'=>$input['name'],'email'=>$input['email'],'password'=>Hash::make($input['password']),'college_id'=>1,'company_id'=>null,'role_id'=>$role,'register_type'=>null,'care_role'=>'patient','care_enabled'=>true]);
        return $this->issue($user,201);
    }

    private function issue(User $user,$status=200)
    {
        $token=$user->createToken('careroute-mobile',['careroute-mobile'])->plainTextToken;
        return response()->json(['token'=>$token,'profile'=>RoleAccess::forUser($user),'expires_at'=>now()->addDays(7)->toIso8601String()],$status);
    }

    public function logout(Request $request)
    {
        if ($request->user()->care_role==='driver' && $request->user()->ambulanceDriver) {
            $request->user()->ambulanceDriver->update(['on_duty'=>false]);
        }
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'Signed out.']);
    }
}
