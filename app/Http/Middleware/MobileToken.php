<?php
namespace App\Http\Middleware;
use Closure;
use Laravel\Sanctum\PersonalAccessToken;
class MobileToken
{
    public function handle($request,Closure $next)
    {
        $token=$request->user()->currentAccessToken();
        if (!$token instanceof PersonalAccessToken || !in_array('careroute-mobile',$token->abilities ?? [],true)) return response()->json(['message'=>'Mobile login required.'],401);
        if ($token->created_at->lt(now()->subDays(7))) { $token->delete(); return response()->json(['message'=>'Login expired. Please sign in again.'],401); }
        return $next($request);
    }
}
