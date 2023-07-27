<?php

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use Helpers;
    public function success($msg='success',$data = [],$code=1000)
    {
        return response()->json([
            'code' => $code,
            'message' => $msg,
            'data' => $data,
        ]);
    }

    public function error($msg,$data = [],$code=2000)
    {
        return response()->json([
            'code' => $code,
            'message' => $msg,
            'data' => $data,
        ]);
    }
}
