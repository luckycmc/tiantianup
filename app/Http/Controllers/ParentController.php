<?php

namespace App\Http\Controllers;

use App\Models\ParentStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentController extends Controller
{
    /**
     * 我的学员
     * @return \Illuminate\Http\JsonResponse
     */
    public function my_students()
    {
        // 当前用户
        $user = Auth::user();
        // 学生
        $student_info = ParentStudent::where('user_id',$user->id)->get();
        return $this->success('我的学员',$student_info);
    }
}
