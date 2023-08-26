<?php 
return [
    'labels' => [
        'UserTeacherOrder' => 'UserTeacherOrder',
        'user-teacher-order' => 'UserTeacherOrder',
    ],
    'fields' => [
        'user_id' => '用户id',
        'role' => '角色',
        'teacher_id' => '教师id',
        'out_trade_no' => '订单号',
        'amount' => '订单金额',
        'discount' => '余额抵扣金额',
        'status' => '状态0为未支付1为已支付',
        'pay_type' => '支付方式：0为余额1为微信2为组合',
    ],
    'options' => [
    ],
];
