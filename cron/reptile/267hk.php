<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2017/7/19
 * Time: 16:00
 */
require_once('../../comm.php');

set_time_limit(0);
$server_id = 5;
$cache_name = '267hk_collect';

//读取上次采集信息
$collect = \Lib\Redis::Instance()->get($cache_name);
if(!$collect){
    $collect = array(
        'page'=>1,
        'type'=>'60'
    );
}
$server = db()->query('select site_host from vj_server where id='.$server_id.' limit 1')->fetch(2);
$type = "{$collect['type']}/";
if($collect['page'] > 1){
    $type .= 'index-'.$collect['page'].'.html';
}
$url = "https://{$server['site_host']}/Html/{$type}";
//echo $url;
$site_con = file_get_contents($url);
//echo $site_con;
preg_match_all('/<li><a href="(.*?)"[\s\S]+?src="(.*?)"[\s\S]+?<h3>([\s\S]*?)<\/h3>/',$site_con,$mat_video);
//print_r($mat_video);
if(empty($collect['page_count'])){
    //捕获页码
//    echo $site_con;
    preg_match('/pagination[\S\s]*?<strong>.*\&nbsp\;(\d+)\/(\d+)/',$site_con,$mat_page_num);
    $collect['page_count'] = $mat_page_num[2];
//    var_dump($mat_page_num);
}

$r = 0;
$n = 0;
$y = 0;
foreach ($mat_video[1] as $k=>$v){
    preg_match('/.*\/(\d+)/',$v,$mat_vid);
    //var_dump($mat_vid);
    $video_id = '267hk_'.$mat_vid[1];
    $db_video = db()->query("select * from vj_video where video_id='{$video_id}'")->fetch(2);
    if($db_video){
        //已存在
        $r++;
        $n++;
        continue;
    }
    $o_video_url = "https://{$server['site_host']}{$v}";
//    echo $o_video_url;
    $video_con = file_get_contents($o_video_url);
    if(!$video_con){
        $n++;
        continue;
    }
    //var_dump($video_con);
    if(!preg_match('/downurl[\s\S]*?href="([\s\S]*?)"/',$video_con,$mat_vde_url)){
        $n++;
        continue;
    }
//    var_dump($mat_vde_url);
    $video_url  = $mat_vde_url[1];
    $image_url = $mat_video[2][$k];
    $video_title = $mat_video[3][$k];
    $sql = "insert into vj_video(video_id,video_url,video_time,type_id,video_img,title) value('{$video_id}','{$video_url}','00:00',(select id from vj_type where item='{$collect['type']}' limit 1),'{$image_url}','{$video_title}')";
    //print_r($sql);
    try{
        db()->exec($sql);
        $y++;
    }catch (Exception $exception){
//        echo $exception->getMessage();
        $n++;
    }
//    break;
}

echo "<(￣ˇ￣)/ 捕获完成(267hk)
抓取Url: {$url}
抓取信息：分类({$collect['type']}),页码：".($collect['page']).",总页: {$collect['page_count']}
抓取计数：成功捕获{$y}条视频,失败捕获{$n}条(其中含存在数据({$r}条))~\n";

//翻页
$collect['page']++;
if($collect['page'] >= $collect['page_count']){
    //最后一页
    $next_sql  = "select item from `vj_type` where id=(select id+1 `id` from `vj_type` where `server_id`='{$server_id}' and `item`='{$collect['type']}' limit 1) limit 1";
    $typeInfo = db()->query($next_sql)->fetch(PDO::FETCH_ASSOC);

    $collect['type'] = $typeInfo ? $typeInfo['item'] : '60';
    $collect['page'] = 1;
    unset($collect['page_count']);
}
echo "\n";
\Lib\Redis::Instance()->set($cache_name,$collect,0);
$ob_content = ob_get_contents();
file_put_contents(ROOT.'/logs/monitoring_'.date("Y_m_d").'.log',"DATE ".date('Y-m-d H:i:s')."\n".$ob_content,FILE_APPEND);
