<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Collect;
use App\Models\Course;
use App\Models\Education;
use App\Models\Grade;
use App\Models\Region;
use App\Models\RotateImage;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $result = User::with(['teacher_experience','teacher_info','teacher_tags'])->where(['id' => $id])->first();
        if (!$result) {
            return $this->error('教师不存在');
        }
        $teaching_year = 0;
        $subject = [];
        $object = [];
        foreach ($result->teacher_experience as $experience) {
            $start_time = Carbon::parse($experience->start_time);
            $end_time = Carbon::parse($experience->end_time);
            $teaching_years = $start_time->diffInYears($end_time);
            $teaching_year += $teaching_years;
            // 课程
            $subject[] = explode(',',$experience->subject);
            // 授课对象
            $object[] = explode(',',$experience->object);
        }
        $result->teaching_year = $teaching_year;
        $result->subject = array_values(array_unique(array_reduce($subject,'array_merge',[])));
        $result->object = array_values(array_unique(array_reduce($object,'array_merge',[])));
        // 当前用户
        $user = Auth::user();
        // 是否收藏
        $result->has_collect = $user->has_collect_teacher($id);
        // 教师风采
        $result->teacher_demeanor = $result->teacher_demeanor($id);
        return $this->success('教师详情',$result);
    }

    /**
     * 推荐课程列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function course_list()
    {
        $data = \request()->all();
        $page_size = $data['page_size'] ?? 10;
        $longitude = $data['longitude'] ?? 0;
        $latitude = $data['latitude'] ?? 0;
        $where = [];
        if (isset($data['name'])) {
            $where[] = ['name','like','%'.$data['name'].'%'];
        }
        if (isset($data['subject'])) {
            $where[] = ['subject','=',$data['subject']];
        }
        if (isset($data['type'])) {
            $where[] = ['type','=',$data['type']];
        }
        if (isset($data['method'])) {
            $where[] = ['method','=',$data['method']];
        }
        $result = Course::with('organization')->where($where)->paginate($page_size);
        foreach ($result as $v) {
            $v->distance = calculate_distance($latitude,$longitude,$v->organization->latitude,$v->organization->longitude);
        }
        return $this->success('推荐课程列表',$result);
    }

    /**
     * 课程详情
     * @return \Illuminate\Http\JsonResponse
     */
    public function course_detail()
    {
        $data = \request()->all();
        $course_id = $data['course_id'] ?? 0;
        $longitude = $data['longitude'] ?? 0;
        $latitude = $data['latitude'] ?? 0;
        $result = Course::with('organization')->find($course_id);
        // 距离
        $result->distance = calculate_distance($latitude,$longitude,$result->organization->latitude,$result->organization->longitude);
        $province = $result->organization->province->region_name;
        $city = $result->organization->city->region_name;
        $district = $result->organization->district->region_name;
        $address = $result->organization->address;
        // 地址
        $result->address = $province.$city.$district.$address;
        // 当前用户
        $user = Auth::user();
        // 是否收藏
        $result->has_collect = $user->has_collect_course($course_id);
        return $this->success('课程详情',$result);
    }

    /**
     * 收藏&取消收藏
     * @return \Illuminate\Http\JsonResponse
     */
    public function collect()
    {
        $data = \request()->all();
        $type = $data['type'] ?? 1;
        $id = $data['id'] ?? 0;
        $field = $type == 1 ? 'teacher_id' : 'course_id';
        // 当前用户
        $user = Auth::user();
        $info = Collect::where(['user_id' => $user->id,$field => $id])->first();
        // 判断是否存在
        if ($info) {
            // 取消收藏
            $info->delete();
            return $this->success('取消收藏');
        }
        // 收藏
        $insert_data = [
            'user_id' => $user->id,
            $field => $id,
            'type' => $type,
            'created_at' => Carbon::now()
        ];
        $result = DB::table('collect')->insert($insert_data);
        if (!$result) {
            return $this->error('操作失败');
        }
        return $this->success('收藏成功');
    }

    /**
     * 获取轮播图
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_rotate()
    {
        $data = \request()->all();
        $platform = $data['platform'] ?? 0;
        $result = RotateImage::where('show_platform',$platform)->get();
        return $this->success('轮播图',$result);
    }

    /**
     * 个人主页
     * @return \Illuminate\Http\JsonResponse
     */
    public function my_profile()
    {
        // 当前用户
        $user = Auth::user();
        // 收益
        $user->commission = Bill::where(['user_id' => $user->id,['amount','>',0]])->sum('amount');
        // 我的收藏
        $user->collection = $user->collects()->count();
        // 我的报名
        $user->entry = $user->user_courses()->count();
        // 未读消息
        $user->message = $user->messages()->where('status',0)->count();
        return $this->success('个人主页',$user);
    }

    /**
     * 获取省份
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_province()
    {
        $province = Region::where('region_type',1)->get();
        $result = $province->sortBy('initial')->groupBy('initial');
        return $this->success('省份',$result);
    }

    /**
     * 获取城市
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_city()
    {
        $data = \request()->all();
        $province_id = $data['province_id'] ?? 0;
        // 查询省份
        $city = Region::where(['region_type' => 2,'parent_id' => $province_id])->get();
        $result = $city->sortBy('initial')->groupBy('initial');
        return $this->success('城市',$result);
    }

    /**
     * 获取区县
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_district()
    {
        $data = \request()->all();
        $city_id = $data['city_id'] ?? 0;
        // 查询省份
        $district = Region::where(['region_type' => 3,'parent_id' => $city_id])->get();
        $result = $district->sortBy('initial')->groupBy('initial');
        return $this->success('城市',$result);
    }

    /**
     * 获取省市区
     * @return \Illuminate\Http\JsonResponse
     */
    public function areas()
    {
        $data = \request()->all();
        $parent_id = $data['parent_id'] ?? 0;
        $result = Region::where('parent_id',$parent_id)->get();
        return $this->success('地区',$result);
    }

    /*
 * 省市区列表
 * */
    public function dgtx_places()
    {
        $data = Region::all();
        // foreach ($data as $item){
        //     $item->value = $item->label;
        // }
        $region =  $this->getTree($data,0,1);
        // $region_data = $this->removeEmptyFields($region);
        return $this->success('成功',$region);
    }

    function getTree($data, $pId,$level = 1)
    {
        $tree = [];
        foreach($data as $k => $v) {
            if($v->parent_id == $pId) {
                $v->children = $this->getTree($data, $v->id,$level +1);
                if ($level == 3) {
                    unset($v->children);
                }
                $tree[] = $v;
                unset($data[$k]);
            }
        }
        return $tree;
    }

    /**
     * 年级
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_grade()
    {
        $grade = Grade::all();
        return $this->success('年级',$grade);
    }

    /**
     * 获取学历
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_education()
    {
        $education = Education::all();
        return $this->success('学历',$education);
    }

    /**
     * 获取科目
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_subject()
    {
        $subject = Subject::all();
        return $this->success('科目',$subject);
    }
}
