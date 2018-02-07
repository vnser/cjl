<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 16-6-26
 * Time: 上午4:49
 */

include_once('../../comm.php');
set_time_limit(0);
stream_context_set_default(array(
    'http' => array(
        'method' => 'GET',
        'header'=> "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1\r\n".
        "cookie: splash=1; AVS=61d0o83d45agnsb10jb3e89r11",
    )
));

define('SERVER_NAME','52888');
define('SERVER_ID',7);
$coll_key = '52888_collect';
$default_type = array(
    'page'=>1,
    'type'=>'fc2'
);
$redis = \Lib\Redis::Instance();
//读取上次采集信息
if(!($collect = $redis->get($coll_key))){
    $collect = $default_type;
}
$server_url = db()->query('select concat(scheme,site_host) from vj_server where id=\''.SERVER_ID.'\' limit 1')->fetchColumn(0);
$url = "{$server_url}/videos/{$collect['type']}?page={$collect['page']}";
//获取cjl内容
$result = file_get_contents($url);

$match_res = preg_match_all('/<div class="well well-sm">[\s\S]*?<a href="(\/video\/(\d*?)\/[\s\S]*?)"[\s\S]*?<div class="thumb-overlay">[\s\S]*?<img src="(.*)" title="(.*?)"[\s\S]*?<div class="duration">\s*?([\S]*?)\s*?<\/div>/',$result,$resArr);
if(!$match_res){
    exit("~~~^_^~~~ 捕获内容获取失败 \n");
}
//计算总页数
if(preg_match('/class=\"pagination\">[\s\S]*?<\/ul>/',$result,$page_count)){
    preg_match_all('/<li[\s\S]*?><[\S\s]+?>(\d*?)<\/\w+?><\/li>/',$page_count[0],$page_mat);
    $total_num = max($page_mat[1]);
}
$collect['page_count'] = ($total_num >= 10) ? $total_num+1 : $total_num;
if($collect['page_count'] >= $collect['page'] ){
    $y = 0;
    $n = 0;
    $r = 0;
    foreach ($resArr[1] as $k=>$v){
        $video_id = "52888_{$resArr[2][$k]}";
        $video = db()->query("select * from vj_video where `video_id`='{$video_id}' limit 1")->fetch(2);
        if($video){
            $n++;
            $r++;
            continue;
        }
        $site_con = file_get_contents($server_url.$v);

        preg_match('/<source src="(.*?)"/',$site_con,$vurl_mat);
        $video_url = $vurl_mat[1];
        if(empty($video_url)){
           /* var_dump($site_con);
            exit();*/
            $n++;
            $error = $video_id.' video url empty！';
            continue;
        }
        try{
            $sql = "insert into `vj_video`(`type_id`,`video_id`,`title`,`video_url`,`video_img`,`video_time`) VALUE ((SELECT id FROM `vj_type` where `server_id`='".SERVER_ID."' and `item`='{$collect['type']}' limit 1),'{$video_id}','{$resArr[4][$k]}','{$video_url}','{$resArr[3][$k]}','{$resArr[5][$k]}');";
            $res = db()->exec($sql);
            $y++;
        }catch (Exception $exception){
            $n++;
            $error=$exception->getMessage();
        }
    }
    //翻页
    $collect['page']++;
    echo "<(￣ˇ￣)/ 捕获完成(52888)
抓取Url: {$url}
抓取信息：分类({$collect['type']}),页码：".($collect['page']-1).",总页: {$collect['page_count']}
抓取计数：成功捕获{$y}条视频,失败捕获{$n}条(其中含存在数据({$r}条))~\n";
    if($error){
        echo '错误信息: '.$error."\n";
    }


}else{
    //最后一页
    echo SERVER_NAME.",已经捕获到最后一页了\n";
    $typeInfo = db()->query("select * from `vj_type` where `server_id`='".SERVER_ID."' and id > (select id from `vj_type` where `server_id`='".SERVER_ID."' and `item`='{$collect['type']}') limit 1")->fetch(PDO::FETCH_ASSOC);
    if(!$typeInfo){
        //没有下一个分类
        $collect = $default_type;
    }else{
        $collect = array(
            'page'=>1,
            'type'=>$typeInfo['item']
        );
    }
}
echo "\n";
$redis->set($coll_key,$collect,0);
$ob_content = ob_get_contents();
file_put_contents(ROOT.'/logs/monitoring_'.date("Y_m_d").'.log',"DATE ".date('Y-m-d H:i:s')."\n".$ob_content,FILE_APPEND);

