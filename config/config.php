<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 16-6-26
 * Time: 上午4:50
 */

$config = [
    //redis配置
    'REDIS'   => [
        'host' => '127.0.0.1',
        'auth' => 'Vnser19980808'
        //'auth' => 'CQswkj123456'
    ],
    //数据库配置
    'DB'=>array(
        'host'=>'localhost',
        'user'=>'root',
        //'pass'=>'',
        'pass'=>'Vnser19980808',
        'name'=>'cjl',
        'char'=>'utf8mb4'
    ),
    //后台设置
    'ADMIN_USER'=>['vnser','0101001..'],
    //本地视频资源会员加载域名
    'VIDEO_DOMAIN'=>['23333333vjl.llcat.cc'/*,'video_src.nafou.top'*/],
    //前台管理员设置ID
    'MANAGE_USER'=>[1000,1002],
    //限制每天登陆次数
    'LIMIT_LOGIN'=> 8,
    'HTTP_PROXY'=>'120.27.189.52:31280'
];
//资源域名随机
$config['VIDEO_DOMAIN'] = $config['VIDEO_DOMAIN'][array_rand($config['VIDEO_DOMAIN'])];