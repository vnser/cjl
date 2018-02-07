<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2017/7/18
 * Time: 22:24
 */
require_once('../../comm.php');
set_time_limit(0);
$server_id = 4;
$cache_name = '56pao_collect';

//读取上次采集信息
$collect = \Lib\Redis::Instance()->get($cache_name);
if(!$collect){
    $collect = array(
        'page'=>1,
        'type'=>'27'
    );
}

$server = db()->query('select site_host from vj_server where id='.$server_id.' limit 1')->fetch(2);
$type = "{$collect['type']}";
if($collect['page'] > 1){
    $type .= "_{$collect['page']}";
}
$page_url = "http://{$server['site_host']}/diao/se{$type}.html";
$site_con = file_get_contents($page_url);

if(!$site_con){
    //最后一页
    $collect['page'] = 1;
    $typeInfo = db()->query("select * from `vj_type` where id=(select id+1 from `vj_type` where `server_id`='{$server_id}' and `item`='{$collect['type']}') limit 1")->fetch(PDO::FETCH_ASSOC);
    if($typeInfo){
        $collect['type'] = $typeInfo['item'];
    }else{
        $collect['type'] = '27';
    }
    \Lib\Redis::Instance()->set($cache_name,$collect,0);
    exit('到底了,跳转到下一分类('.$collect['type'].')！');
}
$site_con = mb_convert_encoding($site_con,'utf-8','gbk');

//print_r($site_con);
//preg_match_all('/class=\"video_box\"[\s\S]+?href=\"(.*?)\"[\S\s]+?title=\"(.*?)\"[\s\S]+?<img src="(.*?)"/',$site_con,$mat_arr);
preg_match_all('/class="video_box"[\S\s]+?href=\"(.*?)\"[\s\S]+?<img[\s\S]+?src=\"(.*?)\"[\s\S]+?title=\"([\s\S]+?)\"/',$site_con,$mat_arr);
//unset($mat_arr[1][count($mat_arr[1])-1]);
/*print_r($mat_arr);
exit;*/


$y = 0;
$n = 0;
$r = 0;
$msg = '';
foreach ($mat_arr[1] as $k => $v){
    //取得视频ID
    preg_match('/.*\/(\d+)/',$v,$mat_vid);
    $video_id = "56pao_{$mat_vid[1]}";
    $db_video = db()->query("select * from vj_video where video_id='{$video_id}'")->fetch(2);
    if($db_video){
        //已存在
        $r++;
        $n++;
        continue;
    }

    $video_con = @file_get_contents("http://{$server['site_host']}{$v}");
    if(!$video_con){
        $error = $php_errormsg;
        continue;
    }
//    preg_match('/<source class="src" src="(.*?)"/',$video_con,$mat_video);
    preg_match('/flashvars={\s*f:\'(.*?)\'/',$video_con,$mat_video);

   /* $video = parse_url($mat_video[1]);
    $video_url = $video['path'];*/
    $video_url = $mat_video[1];
    if(!$video_url){
        $msg = '视频地址为空!';
        $n++;
        continue;
    }
    $image = parse_url($mat_arr[2][$k]);
    $image_url = $image['path'];

    $sql = "insert into vj_video(video_id,video_url,video_time,type_id,video_img,title) value('{$video_id}','{$video_url}','00:00',(select id from vj_type where item='{$collect['type']}' limit 1),'{$image_url}','{$mat_arr[3][$k]}')";
    try{
        db()->exec($sql);
        $y++;
    }catch (Exception $exception){
        $msg = $exception->getMessage();
        $n++;
    }
}
echo "<(￣ˇ￣)/ 捕获完成(56pao)
抓取Url: {$page_url}
抓取信息：分类({$collect['type']}),页码：".($collect['page'])."
抓取计数：成功捕获{$y}条视频,失败捕获{$n}条(其中含存在数据({$r}条))~\n" ;
if($msg){
    echo "error：{$msg}\n";
}

$collect['page']++;
\Lib\Redis::Instance()->set($cache_name,$collect,0);
//print_r($mat_arr);
//print_r($site_con);
/*if($collect['page'] <= 1){
    //采集页数
}*/

//print_r($server);
echo "\n";
$ob_content = ob_get_contents();
file_put_contents(ROOT.'/logs/monitoring_'.date("Y_m_d").'.log',"DATE ".date('Y-m-d H:i:s')."\n".$ob_content,FILE_APPEND);