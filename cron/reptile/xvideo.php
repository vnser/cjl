<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2016/12/21
 * Time: 19:33
 */
require_once('../../comm.php');
set_time_limit(0);
stream_context_set_default(array(
    'http' => array(
        'method' => 'GET',
        'header'=> "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1\r\nReferer: {$down_video['video_url']}",
//           'request_fulluri'=>true
    )
));
//清理视频,防止视频失效
db()->exec("delete from vj_video where unix_timestamp(add_time)+10700<=unix_timestamp() and video_id like 'xvideo\_%' and local=0");
//读取上次采集信息
$server_id = 6;
$coll_key = 'xvideo_collect';
$first_type = 'month|0';
$collect = \Lib\Redis::Instance()->get($coll_key);
if(!$collect){
    $collect = array(
        'page'=>1,
        'type'=>$first_type,
//        'page_count'=>99
    );
}
$collect['page_count'] = 99;

//计算url
$server = db()->query('select site_host,scheme from vj_server where id=\''.$server_id.'\' limit 1')->fetch(2);
$exp_type = explode('|',$collect['type']);
$month = reckonMonthYear($exp_type[1]);
$url = "{$server['scheme']}{$server['site_host']}/best/$month".($collect['page'] == 1?'':"/{$collect['page']}");

//采集页面
$result = file_get_contents($url);

if(!$result){
    echo('请求失败!');
    //ob_end_clean()
}
$matchRes = preg_match_all('/class="thumb-block[\s\S]*?href="(.*?)"[\s\S]*?data-videoid="(.*?)"[\s\S]*?title=\"([\s\S]*?)\"/',$result,$arr);
//print_r($arr);
/*
if($collect['page'] <= 1){
    //采集总页数
    preg_match('/>(\d*?)<\/a><\/li><li><a href=\"[\S]*?\" class="prevnext"/',$result,$pageC);
    //计算总页数
    $collect['page_count'] = ($pageC[1] >= 10) ? $pageC[1]+1 : $pageC[1];
}*/

//翻页
$collect['page']++;
$org_collect = $collect;

if($collect['page'] >= $collect['page_count']){
    //最后一页
    $typeInfo = db()->query("select * from `vj_type` where `server_id`='{$server_id}' and id>(select id from `vj_type` where `server_id`='{$server_id}' and `item`='{$collect['type']}') limit 1")->fetch(PDO::FETCH_ASSOC);
    if(!$typeInfo){
        //没有下一个分类
        $collect =  $collect = array(
            'page'=>1,
            'type'=>$first_type,
            'page_count'=>99
        );
    }else{
        $collect = array(
            'page'=>1,
            'type'=>$typeInfo['item']
        );
    }
//    \Lib\Redis::Instance()->set($coll_key,$collect,0);
    echo "已经捕获到最后一页了\n";
}
\Lib\Redis::Instance()->set($coll_key,$collect,0);

$y = 0;
$n = 0;
$r = 0;//重复
foreach ($arr[1] as $k =>$v){
    //切割ID
//    $parse_url = explode('/',$v,3);
    $video_id = "xvideo_{$arr[2][$k]}";
    //查询ID
    $video = db()->query("select * from vj_video where `video_id`='{$video_id}' limit 1")->fetch(2);
    if($video){
        $n++;
        $r++;
        continue;
    }
    $chi_content = @file_get_contents("{$server['scheme']}{$server['site_host']}{$v}");
    if(!preg_match("/setVideoUrlHigh\(\'(.*?)'\)[\s\S]*?setThumbUrl169\('(.*?)'\)/",$chi_content,$video_res)){
        $n++;
        $error = '子页面,捕获失败!';
        continue;
    }

    if(empty($video_res[1])){
        $n++;
        $error = '视频地址未捕获!';
        continue;
    }
    $title = explode('-',$arr[3][$k]);
    $video_title = $arr[3][$k];
    if(count($title)==2){
        $video_title = $title[0];
    }
    try{
        $sql = "insert into `vj_video`(`type_id`,`video_id`,`title`,`video_url`,`video_img`,`video_time`) VALUE ((SELECT id FROM `vj_type` where `server_id`='{$server_id}' and `item`='{$org_collect['type']}' limit 1),'{$video_id}','{$video_title}','{$video_res[1]}','{$video_res[2]}','00:00');";
        $res = db()->exec($sql);
        $y++;
    }catch (Exception $exception) {
        $n++;
        $error = $exception->getMessage().'[SQL]: '.$sql;
        continue;
    }
}
echo "<(￣ˇ￣)/ 捕获完成(xvideo)
抓取Url: {$url}
抓取信息：分类({$org_collect['type']}),页码：".($org_collect['page']-1).",总页: {$org_collect['page_count']}
抓取计数：成功捕获{$y}条视频,失败捕获{$n}条(其中含存在数据({$r}条))~\n";
if($error)
    echo "其中报错：{$error}\n";


echo "\n";
$ob_content = ob_get_contents();
file_put_contents(ROOT.'/logs/monitoring_'.date("Y_m_d").'.log',"DATE ".date('Y-m-d H:i:s')."\n".$ob_content,FILE_APPEND);