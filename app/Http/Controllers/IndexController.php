<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\BaseInformation;
use App\Models\Bill;
use App\Models\Collect;
use App\Models\Constant;
use App\Models\Course;
use App\Models\CourseSetting;
use App\Models\DeliverLog;
use App\Models\Education;
use App\Models\Grade;
use App\Models\Notice;
use App\Models\OrganType;
use App\Models\Region;
use App\Models\RotateImage;
use App\Models\Subject;
use App\Models\TeacherCourseOrder;
use App\Models\TeachingMethod;
use App\Models\TeachingType;
use App\Models\TrianingType;
use App\Models\User;
use App\Models\UserCourse;
use App\Models\UserTeacherOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    /**
     * 推荐教师列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function teacher_list()
    {
        $data = \request()->all();
        $district_id = $data['district_id'] ?? 0;
        $page_size = $data['page_size'] ?? 10;
        $where = [];
        if ((isset($data['longitude']) && isset($data['latitude'])) && !isset($data['district_id']) && !isset($data['city'])) {
            // 根据经纬度获取省市区
            $location = get_location($data['longitude'],$data['latitude']);
            if (!$location) {
                return $this->error('定位出错');
            }
            $district_id = Region::where('code',$location['adcode'])->value('id');
            $where[] = ['district_id', '=', $district_id];
        }
        if (isset($data['city']) && !isset($data['district_id'])) {
            $city_id = Region::where('region_name',$data['city'])->value('id');
            $where[] = ['city_id','=',$city_id];
        }
        if (isset($data['district_id'])) {
            $where[] = ['district_id','=',$data['district_id']];
        }
        Log::info('where: ',$where);
        // 查询当前位置的所有推荐教师
        $teachers = User::with(['teacher_experience','teacher_info','teacher_education'])->where($where)->where(['is_recommend' => 1,'role' => 3,'status' => 1])->orderByDesc('recommend_time')->paginate($page_size);

        // 当前用户
        $user = Auth::user();
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
            $teacher->is_pay = UserTeacherOrder::where(['user_id' => $user->id,'teacher_id' => $teacher->id,'status' => 1])->exists();
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
        $result = User::with(['teacher_experience','teacher_info','teacher_cert','teacher_education'])->where(['id' => $id])->first();
        if (!$result) {
            return $this->error('教师不存在');
        }
        // 当前用户
        $user = Auth::user();
        // $user = User::find(7);
        // 判断当前用户是否能查看
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
        // 是否收藏
        $result->has_collect = $user->has_collect_teacher($id);
        // 教师风采
        $result->teacher_demeanor = $result->teacher_demeanor($id);
        if ($result->teacher_demeanor) {
            $result->teacher_demeanor = json_decode($result->teacher_demeanor->url,true);
        }
        /*foreach ($result->teacher_demeanor as $v) {
            $v->url = json_decode($v->url,true);
        }*/
        // 教师证书
        if (isset($result->teacher_cert)) {
            $result->teacher_cert->teacher_cert = json_decode($result->teacher_cert->teacher_cert,true);
            $result->teacher_cert->other_cert = json_decode($result->teacher_cert->other_cert,true);
            $result->teacher_cert->honor_cert = json_decode($result->teacher_cert->honor_cert,true);
        }
        // $result->teacher_cert = isset($result->teacher_info) ? json_decode($result->teacher_info->teacher_cert,true) : '';
        // 判断当前机构是否购买
        if (in_array($user->role,[2,4])) {
            $is_buy = DeliverLog::where(['user_id' => $id,'pay_status' => 1,'course_id' => $data['course_id']])->exists();
            if (!$is_buy) {
                $is_buy = UserTeacherOrder::where(['user_id' => $user->id,'teacher_id' => $id,'status' => 1])->exists();
            }
        } else {
            $is_buy = UserTeacherOrder::where(['user_id' => $user->id,'teacher_id' => $id,'status' => 1])->exists();
        }
        if (!$is_buy) {
            $result->mobile = null;
        }
        // 投递详情
        if (isset($data['course_id'])) {
            $result->deliver_detail = $result->deliver_log->where('course_id',$data['course_id'])->values();
        }
        // $result->images = json_decode($result->teacher_demeanor[0],true);
        $result->tags = $result->teacher_tags->filter(function ($item) {
            return $item->is_show == 1;
        })->values();
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
        Log::info('course_data: ',$data);

        $where = [];
        $or_where = [];
        if (isset($data['is_default'])) {
            // 当前城市
            $location_info = get_location($longitude,$latitude);
            $city = $location_info['city'];
            $city_id = Region::where('region_name',$city)->value('id');
        } else {
            $city_id = Region::where('region_name',$data['city'])->value('id');
        }
        Log::info('city_id: '.$city_id);
        $where[] = ['city','=',$city_id];

        if (isset($data['name'])) {
            $where[] = $or_where[] = ['name','like','%'.$data['name'].'%'];
        }
        if (isset($data['subject'])) {
            $where[] = ['subject','=',$data['subject']];
        }
        if (isset($data['type'])) {
            $where[] = ['type','=',$data['type']];
        }
        if (isset($data['method'])) {
            $where[] = $or_where[] = ['method','=',$data['method']];
        }
        if (isset($data['district'])) {
            $id = Region::where('region_name',$data['district'])->value('id');
            /*$district = get_long_lat('','',$data['district'],'');
            $longitude = $district[0];
            $latitude = $district[1];*/
            /*Log::info('longitude: '.$longitude);
            Log::info('latitude: '.$latitude);*/
            $where[] = ['district','=',$id];
        }
        /*if (isset($data['district_id'])) {
            $district_name = Region::where('id',$data['district_id'])->value('region_name');
            $region_info = get_long_lat('','',$district_name,'');
            $longitude = $region_info[0];
            $latitude = $region_info[1];
        }*/
        // 当前用户
        $user = Auth::user();
        if (in_array($user->role,[1,2])) {
            $role = 1;
        } else {
            $role = 3;
        }
        $result = Course::with('organization')->where($where)->where(['role' => $role,'is_recommend' => 1])->whereNotIn('is_invalid',[1])->where('status','!=',0)->where('is_on',1)->whereNotIn('adder_role',[0])->where('end_time','>',Carbon::now())->orWhere(function ($query) use ($or_where,$role) {
            $query->where(['role' => $role,'is_recommend' => 1,'is_on' => 1,'method' => '线上'])->where('status','!=',0)->whereNotIn('adder_role',[0])->whereNotIn('is_invalid',[1])->where('end_time','>',Carbon::now())->where($or_where);
        })->orderByDesc('recommend_time')->paginate($page_size);
        foreach ($result as $v) {
            /*if ($v->adder_role == 4) {
                $v->distance = calculate_distance($latitude,$longitude,$v->organization->latitude,$v->organization->longitude);
            } else {
                $v->distance = calculate_distance($latitude,$longitude,$v->latitude,$v->longitude);
            }*/

            // 是否已报名
            $v->is_entry = UserCourse::where(['user_id' => $user->id,'course_id' => $v->id])->exists();
            // 是否已投递
            $v->is_deliver = DeliverLog::where(['user_id' => $user->id,'course_id' => $v->id])->exists();
            $v->class_date = json_decode($v->class_date,true);
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
        $result = Course::with(['organization','adder'])->find($course_id);
        Log::info('latitude: '.$result->latitude);
        Log::info('longitude: '.$result->longitude);
        // 当前用户
        $user = Auth::user();
        if ($result->add_role == 0) {
            $result->visit_count++;
            $result->update();
        } else {
            if ($user->id !== $result->adder_id) {
                $result->visit_count++;
                $result->update();
            }
        }
        // 距离
        /*if ($result->adder_role == 4) {
            $result->distance = calculate_distance($latitude,$longitude,$result->organization->latitude,$result->organization->longitude);
            // 地址
            // $result->address = $result->organization->address;
        }*/
        // $result->distance = calculate_distance($latitude,$longitude,floatval($result->latitude),floatval($result->longitude));
        /*if ($result->adder_role == 2) {
            $result->distance = calculate_distance($latitude,$longitude,floatval($result->latitude),floatval($result->longitude));
            // 地址
            // $result->address = $result->adder->address;
            $result->nickname = $result->adder->nickname;
        }*/

        // 是否收藏
        $result->has_collect = $user->has_collect_course($course_id);
        // 总费用
        $result->total_price = $result->class_price;
        if (in_array($user->role,[1,2])) {
            // 是否报名
            $result->is_entry = $user->has_entry_course($course_id);
        }
        if ($user->role == 3) {
            // 是否投递
            $result->is_deliver = DeliverLog::where(['user_id' => $user->id,'course_id' => $course_id])->exists();
            // 是否支付
            $result->is_pay = DeliverLog::where(['user_id' => $user->id,'course_id' => $course_id,'pay_status' => 1])->exists();
            // 课程信息
            $course_info = Course::find($course_id);
            if ($course_info->adder_role == 0) {
                /*$course_info->visit_count += 1;
                $course_info->update();*/
                $result->is_show = DeliverLog::where(['user_id' => $user->id,'course_id' => $course_id,'pay_status' => 1])->exists();
            }
        }
        if ($result->adder_role == 0) {
            $result->class_date = $result->platform_class_date;
        }

        if ($result->is_entry) {
            $entry_info = UserCourse::where(['user_id' => $user->id, 'course_id' => $course_id])->first();
            $entry_time = Carbon::parse($entry_info->created_at)->format('Y-m-d H:i:s');
            $result->entry_time = $entry_time;
        }
        $result->class_date = json_decode($result->class_date,true);
        if ($result->method !== '线上') {
            $result->province = $result->province_info->region_name;
            $result->city = $result->city_info->region_name;
            $result->district = $result->district_info ? $result->district_info->region_name : null;
        }
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
        $user->commission = number_format(Bill::where(['user_id' => $user->id,['amount','>',0]])->sum('amount'),2);
        // 我的收藏
        $user->collection = $user->collects()->count();
        // 我的报名
        $user->entry = $user->user_courses()->count();
        if ($user->role == 3) {
            $info = DeliverLog::with(['course'])->where('user_id',$user->id)->get();
            $info = $info->filter(function ($item) {
                return $item->course !== null;
            })->values();
            foreach ($info as $v) {
                if ($v->course) {
                    $v->course->pay_time = Carbon::parse($v->updated_at)->format('Y-m-d H:i:s');
                }
            }
            $course = $info->map(function ($item) {
                return $item->course;
            });
            $course = $course->filter(function ($item) {
                return $item->adder_role !== 0;
            })->values();
            // 查询后台配置
            $system_setting = CourseSetting::orderByDesc('created_at')->first();
            $looked_course_valid_time = $system_setting->looked_course_valid_time;
            $user_course = $course->filter(function ($item) use ($looked_course_valid_time) {
                // 当前时间
                $day = Carbon::now();
                $diff_day = $day->diffInDays($item->pay_time);
                return $diff_day < $looked_course_valid_time;
            })->values();
            $user->entry = count($user_course->toArray());
        }

        $user->team = $user->child()->where('users.is_perfect',1)->count() + $user->grandson()->where('users.is_perfect',1)->count();
        // 未读消息
        $user->message = $user->messages()->where('status',0)->count();
        // 联系人数量
        $user->contact_count = $user->contacts->count();
        // 标签
        if ($user->role == 3) {
            $user->tags = $user->teacher_tags->pluck('tag','id');
            $user->real_auth_status = $user->teacher_real_auth ? $user->teacher_real_auth->status : 3;
            $user->cert_status = $user->teacher_cert ? $user->teacher_cert->status : 3;
            $user->education_status = $user->teacher_education ? $user->teacher_education->status : 3;
            $user->image_status = $user->teacher_image ? $user->teacher_image->status : 3;
            // 教学经历
            $career = $user->teacher_career;
            if (!$career->isEmpty()) {
                $is_all_passed = $career->every(function ($career) {
                    return $career->status == 1;
                });
                $is_all_pending = $career->every(function ($career) {
                    return $career->status == 0;
                });
                $is_all_refuse = $career->every(function ($career) {
                    return $career->status == 2;
                });
                $is_some_passed = $career->pluck('status')->contains(0) && $career->pluck('status')->contains(1);
                $is_some_refused = $career->pluck('status')->contains(0) && $career->pluck('status')->contains(2);
                if ($is_all_passed) {
                    $user->career_status = 1;
                } elseif ($is_all_pending) {
                    $user->career_status = 0;
                } else if ($is_all_refuse) {
                    $user->career_status = 5;
                } else if ($is_some_passed) {
                    $user->career_status = 2;
                } else if ($is_some_refused) {
                    $user->career_status = 4;
                } else {
                    $user->career_status = 2;
                }
            } else {
                $user->career_status = 3;
            }
        }
        if ($user->role !== 4) {
            $user->province_name = $user->province->region_name;
            $user->city_name = $user->city->region_name;
            $user->district_name = $user->district->region_name;
        }
        return $this->success('个人主页',$user);
    }

    /**
     * 获取省份
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_province()
    {
        $province = Region::where('parent_id',0)->get();
        $result = $province->sortBy('initial')->groupBy('initial');
        return $this->success('省份',$result);
    }

    public function get_loc_province()
    {
        $province = Region::where(['region_type' => 1,'is_checked' => 1])->select('id','initial','region_name')->get();
        $result = $province->groupBy('initial') // 按照 initial 字段进行分组
        ->map(function ($items) {
            $data = $items->pluck('region_name')->toArray(); // 提取 region_name，转换为普通数组
            $id = $items->pluck('id')->toArray(); // 提取 id，转换为普通数组
            $letter = $items->first()['initial']; // 获取 initial
            return ['data' => $data, 'id' => $id, 'letter' => $letter];
        })->sortBy('letter')
            ->values(); // 重新索引结果数组的键值
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
        $city = Region::where(['parent_id' => $province_id])->get();
        $result = $city->sortBy('initial')->groupBy('initial');
        return $this->success('城市',$result);
    }

    public function get_loc_city()
    {
        $data = \request()->all();
        $province_id = $data['province_id'] ?? 0;
        // 查询省份
        $city = Region::where(['region_type' => 2,'parent_id' => $province_id,'is_checked' => 1])->get();
        $result = $city->groupBy('initial') // 按照 initial 字段进行分组
        ->map(function ($items) {
            $data = $items->pluck('region_name')->toArray(); // 提取 region_name，转换为普通数组
            $id = $items->pluck('id')->toArray(); // 提取 id，转换为普通数组
            $letter = $items->first()['initial']; // 获取 initial
            return ['data' => $data, 'id' => $id, 'letter' => $letter];
        })->sortBy('letter')
            ->values(); // 重新索引结果数组的键值
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
        $district = Region::where(['parent_id' => $city_id])->get();
        $result = $district->sortBy('initial')->groupBy('initial');
        return $this->success('区县',$result);
    }

    public function get_loc_district()
    {
        $data = \request()->all();
        $city_id = $data['city_id'] ?? 0;
        // 查询省份
        $district = Region::where(['region_type' => 3,'parent_id' => $city_id,'is_checked' => 1])->get();
        $result = $district->groupBy('initial') // 按照 initial 字段进行分组
        ->map(function ($items) {
            $data = $items->pluck('region_name')->toArray(); // 提取 region_name，转换为普通数组
            $id = $items->pluck('id')->toArray(); // 提取 id，转换为普通数组
            $letter = $items->first()['initial']; // 获取 initial
            return ['data' => $data, 'id' => $id, 'letter' => $letter];
        })->sortBy('letter')
            ->values(); // 重新索引结果数组的键值
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
        $grade = Constant::where('type',8)->get();
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
        $subject = Constant::where('type',5)->get();
        return $this->success('科目',$subject);
    }

    /**
     * 获取机构类型
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_organ_type()
    {
        $type = Constant::where('type',2)->get();
        return $this->success('机构类型',$type);
    }

    /**
     * 机构性质
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_organ_nature()
    {
        $nature = Constant::where('type',3)->get();
        return $this->success('机构类型',$nature);
    }

    /**
     * 获取培训类型
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_training_type()
    {
        $type = Constant::where('type',13)->get();
        return $this->success('培训类型',$type);
    }

    /**
     * 授课方式
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_teaching_method()
    {
        $methods = Constant::where('type',6)->get();
        return $this->success('授课方式',$methods);
    }

    /**
     * 辅导类型
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_teaching_type()
    {
        $types = Constant::where('type',4)->get();
        return $this->success('辅导类型',$types);
    }

    /**
     * 获取公告
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_notice()
    {
        // 当前用户
        $user = Auth::user();
        $role = $user->role;
        // 查询公告
        $notice = Notice::whereRaw("FIND_IN_SET('$role',object)")->where('status',1)->orderByDesc('created_at')->limit(1)->get();
        return $this->success('公告',$notice);
    }

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
}
