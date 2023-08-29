<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'open_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->created_at ?: 'Y-m-d H:i:s');
    }

    // 教师经历
    public function teacher_experience()
    {
        return $this->hasMany(TeacherCareer::class);
    }
    
    // 教师信息
    public function teacher_info()
    {
        return $this->hasOne(TeacherInfo::class);
    }

    // 教师图片
    public function teacher_images()
    {
        return $this->hasMany(TeacherImage::class);
    }
    
    // 教师风采
    public function teacher_demeanor($teacher_id)
    {
        return $this->teacher_images()->where('user_id',$teacher_id)->where('type',2)->get();
    }

    // 收藏课程
    public function collects()
    {
        return $this->hasMany(Collect::class, 'user_id');
    }

    // 是否收藏课程
    public function has_collect_course($course_id)
    {
        return $this->collects()->where('course_id', $course_id)->where('type', 2)->exists();
    }

    // 是否收藏教师
    public function has_collect_teacher($teacher_id)
    {
        return $this->collects()->where('teacher_id', $teacher_id)->where('type', 1)->exists();
    }

    // 教师标签
    public function teacher_tags()
    {
        return $this->hasMany(TeacherTag::class,'user_id','id');
    }

    public function teacher_career()
    {
        return $this->hasMany(TeacherCareer::class, 'user_id', 'user_id');
    }

    // 课程
    public function courses()
    {
        return $this->belongsToMany(Course::class,'deliver_log','user_id','course_id');
    }

    public function deliver_log()
    {
        return $this->hasMany(DeliverLog::class,'user_id');
    }
}
