<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Redis;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        try {
            if (!JWTAuth::parseToken()->authenticate()) {  //获取到用户数据，并赋值给$user
                return response()->json([
                    'code' => 1001,
                    'message' => '无此用户',
                ], 404);
            }
            $user = JWTAuth::parseToken()->authenticate();
            /*$usertoken = Redis::get('TOKEN:'.$user->id);
            if($usertoken!=$request->bearerToken()){
                return response()->json([
                    'code' => 1005,
                    'message' => '账户已在其他地方登陆'

                ], 404);
            }*/
            $userinfo = ['uid'=>$user->id,'status'=>$user->status,'token'=>$request->bearerToken()];
            if($userinfo['status'] != 1){
                return response()->json([
                    'code' => 1004,
                    'message' => '账户被封禁'

                ], 404);
            }
            $request->attributes->add($userinfo);//添加参数
            return $next($request);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'code' => 1002,
                'message' => 'token 过期' , //token已过期
            ]);

        } catch (TokenInvalidException $e) {
            return response()->json([
                'code' => 1003,
                'message' => 'token无效',  //token无效
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'code' => 1004,
                'message' => 'token为空', //token为空
            ],401);

        }
    }
}
