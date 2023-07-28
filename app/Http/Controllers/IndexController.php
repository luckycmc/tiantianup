<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    /**
     * 上传文件
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload()
    {
        $data = request()->all();
        $file = request()->file('file');
        $pathname = $data['pathname'] ?? 'user';

        $disk = Storage::disk('cosv5');
        $upload_path = 'upload/imgs/'.$pathname.'/' . date("Ym/d", time());
        //将图片上传到OSS中，并返回图片路径信息 值如:imgs/1234.jpeg
        $path = $disk->put($upload_path, $file);
        $url = $disk->url($path);
        $url = explode('?',$url)[0];
        return $this->success('上传成功',compact('url'));
    }

    /**
     * 获取定位
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_location()
    {
        $data = \request()->all();
        $id = $data['id'] ?? 0;
        $type = $data['type'] ?? 1;
        $result = Region::where(['parent_id' => $id,'region_type' => $type])->orderBy('initial')->get();
        return $this->success('获取定位',$result);
    }

    /**
     * 推荐教师列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function teacher_list()
    {
        $data = \request()->all();
        $district_id = $data['district_id'] ?? 0;
        $page_size = $data['page_size'] ?? 10;
        // 查询当前位置的所有推荐教师
        $teachers = User::with(['teacher_experience','teacher_info'])->where(['district_id' => $district_id,'is_recommend' => 1,'role' => 3])->paginate($page_size);

        foreach ($teachers as $teacher) {
            $teaching_year = 0;
            $subject = [];
            foreach ($teacher->teacher_experience as $experience) {
                $start_time = Carbon::parse($experience->start_time);
                $end_time = Carbon::parse($experience->end_time);
                $teaching_years = $start_time->diffInYears($end_time);
                $teaching_year += $teaching_years;
                // 课程
                $subject[] = explode(',',$experience->subject);
            }
            $teacher->teaching_year = $teaching_year;
            $teacher->subject = array_values(array_unique(array_reduce($subject,'array_merge',[])));
        }
        return $this->success('推荐教师列表',$teachers);
    }

    /**
     * 教师详情
     * @return \Illuminate\Http\JsonResponse
     */
    public function teacher_detail()
    {
        $data = \request()->all();
        $id = $data['teacher_id'];
        $result = User::with(['teacher_experience','teacher_info'])->where(['id' => $id])->first();
        // dd($result->toArray());
        if (!$result) {
            return $this->error('教师不存在');
        }
        $teaching_year = 0;
        $subject = [];
        foreach ($result->teacher_experience as $experience) {
            $start_time = Carbon::parse($experience->start_time);
            $end_time = Carbon::parse($experience->end_time);
            $teaching_years = $start_time->diffInYears($end_time);
            $teaching_year += $teaching_years;
            // 课程
            $subject[] = explode(',',$experience->subject);
        }
        $result->teaching_year = $teaching_year;
        $result->subject = array_values(array_unique(array_reduce($subject,'array_merge',[])));
        return $this->success('教师详情',$result);
    }
}
