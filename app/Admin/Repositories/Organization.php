<?php

namespace App\Admin\Repositories;

use App\Models\Organization as Model;
use Carbon\Carbon;
use Dcat\Admin\Form;
use Dcat\Admin\Repositories\EloquentRepository;
use Illuminate\Support\Facades\DB;

class Organization extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;

    public function store(Form $form)
    {
        // 获取待新增的数据
        $attributes = $form->updates();
        $images = $attributes['images'];
        $insert_data = array_diff_key($attributes,array_flip(['images']));
        // 机构用户数据
        $user_data = [
            'name' => $attributes['contact'],
            'role' => 4,
            'mobile' => $attributes['mobile'],
            'province_id' => $attributes['province_id'],
            'city_id' => $attributes['city_id'],
            'district_id' => $attributes['district_id'],
            'created_at' => Carbon::now()
        ];
        // 保存用户
        $user_id = DB::table('users')->insertGetId($user_data);
        // 更新编号
        $user_number = create_user_number($attributes['city_id'],$user_id);
        DB::table('users')->where('id',$user_id)->update(['number' => $user_number]);
        // 保存机构信息
        $insert_data['user_id'] = $user_id;
        $insert_data['door_image'] = substr($attributes['door_image'], 0, strpos($attributes['door_image'], '?'));
        $insert_data['business_license'] = substr($attributes['business_license'], 0, strpos($attributes['business_license'], '?'));
        $organ_id = DB::table('organizations')->insertGetId($insert_data);
        $image_data = [];
        foreach ($images as $v) {
            $pos = strpos($v, '?');
            $image_data[] = [
                'organ_id' => $organ_id,
                'url' => substr($v, 0, $pos),
                'created_at' => Carbon::now()
            ];
        }
        // 保存图片
        DB::table('organ_images')->insert($image_data);
        return true;
    }
}
