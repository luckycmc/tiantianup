<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Http\Controllers\AuthController as BaseAuthController;
use Illuminate\Support\Facades\DB;

class AuthController extends BaseAuthController
{
    public function admin_users()
    {
        $data = DB::table('admin_users')->select('id','name as text')->get();
        if (!$data) {
            return [];
        }
        // dd($data);
        return $data->toArray();
    }
}
