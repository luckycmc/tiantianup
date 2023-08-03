<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * 标签列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $result = Tag::all();
        return $this->success('标签列表',$result);
    }
}
