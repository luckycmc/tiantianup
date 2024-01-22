<?php

namespace App\Console\Commands;

use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateCourseStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:course_status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // 获取当天日期
        $current_date = Carbon::now();
        // 更新所有过期订单
        $result = Course::where('end_time','<',$current_date)->update(['course_status' => 2]);
        if (!$result) {
            return false;
        }
        return true;
    }
}
