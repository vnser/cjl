<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2016/12/21
 * Time: 19:33
 */
require_once('../../comm.php');
set_time_limit(0);
//清理一天前视频,防止视频失效
db()->exec("delete from vj_video where unix_timestamp(add_time)+7100<=unix_timestamp() and video_id like 'qyule\_%' and local=0");
//读取上次采集信息
$collect = \Lib\Redis::Instance()->get('qyule_collect');
if(!$collect){
    $collect = array(
        'page'=>1,
        'type'=>'cartoon'
    );
}
$req_h =   array(
    'http' => array(
        'method' => 'GET',
        'header'=> "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1\r\nReferer: {$down_video['video_url']}",
//           'request_fulluri'=>true
    )
);
stream_context_set_default($req_h);

$server = db()->query('select site_host,scheme from vj_server where id=3 limit 1')->fetch(2);
if($collect['type'] == 'recent'){
    //默认分类
    $type = $collect['type'].($collect['page'] == 1?'':'/'.$collect['page']).'/';
}else{
    $type = $collect['type'].'/'.($collect['page'] == 1?'':'recent/'.$collect['page'].'/');
}
$url = "{$server['scheme']}{$server['site_host']}/{$type}";
//采集页面
$result = file_get_contents($url,false, stream_context_create(array(
    'http'=>[
        'method'=>'GET',
        'header'=>"User-Agent: User-Agent:Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1",
        'timeout'=>20
    ]
)));
if(!$result){
    echo('请求失败!');
    //ob_end_clean()
}
$matchRes = preg_match_all('/<div class=\"video\"\>[\s\S]*?href=\"(.*?)\"[\s]?title=\"([\s\S]*?)\"[\s\S]*?<img src=\"([\s\S]*?)\"[\s\S]*?video-overlay badge transparent\">[\s]*?([\S\ ]*?)[\s]*<\/span>[\s\S]*?<\/a>[\s\S]*?<\/div>/',$result,$arr);

/*
if(!$matchRes){
    exit("~~~^_^~~~ 捕获内容获取失败 \n");
}*/
/*print_r($arr);
exit;*/
if($collect['page'] <= 1){
    //采集总页数
    preg_match('/>(\d*?)<\/a><\/li><li><a href=\"[\S]*?\" class="prevnext"/',$result,$pageC);
    //计算总页数
    $collect['page_count'] = ($pageC[1] >= 10) ? $pageC[1]+1 : $pageC[1];
}

//翻页
$collect['page']++;
$org_collect = $collect;
if($collect['page'] >= $collect['page_count']){
    //最后一页
    $typeInfo = db()->query("select * from `vj_type` where `server_id`='3' and id=(select id+1 from `vj_type` where `server_id`='3' and `item`='{$collect['type']}') limit 1")->fetch(PDO::FETCH_ASSOC);
    if(!$typeInfo){
        //没有下一个分类
        $collect = array(
            'page'=>1,
            'type'=>'cartoon'
        );
    }else{
        $collect = array(
            'page'=>1,
            'type'=>$typeInfo['item']
        );
    }
    \Lib\Redis::Instance()->set('qyule_collect',$collect,0);
    echo "已经捕获到最后一页了\n";
}
\Lib\Redis::Instance()->set('qyule_collect',$collect,0);


$y = 0;
$n = 0;
$r = 0;//重复
foreach ($arr[1] as $k =>$v){
    //切割ID
    $parse_url = explode('/',$v,3);
    //查询ID
    $video = db()->query("select * from vj_video where `video_id`='qyule_{$parse_url[1]}' limit 1")->fetch(2);
    if($video){
        $n++;
        $r++;
        continue;
    }
    $chi_content = @file_get_contents("{$server['scheme']}{$server['site_host']}{$v}");
    if(!preg_match('/<source src=\"(.*?)\"/',$chi_content,$video_res)){
        $n++;
        continue;
    }
    $video_url_hea = @get_headers($video_res[1],1);
    if(empty($video_url_hea['Location'])){
        $n++;
        $error = '视频地址未捕获!';
        continue;
    }
    $video_url = $video_url_hea['Location'];
//    $video_path = parse_url($video_res[1]);
    $image_path = parse_url($arr[3][$k]);
    try{
        $sql = "insert into `vj_video`(`type_id`,`video_id`,`title`,`video_url`,`video_img`,`video_time`) VALUE ((SELECT id FROM `vj_type` where `server_id`='3' and `item`='{$org_collect['type']}' limit 1),'qyule_{$parse_url[1]}','{$arr[2][$k]}','{$video_url}','{$image_path['path']}','{$arr[4][$k]}');";
        $res = db()->exec($sql);
        $y++;
    }catch (Exception $exception) {
        $n++;
        $error = $exception->getMessage();
    }
}
echo "<(￣ˇ￣)/ 捕获完成(qyule)
抓取Url: {$url}
抓取信息：分类({$org_collect['type']}),页码：".($org_collect['page']-1).",总页: {$org_collect['page_count']}
抓取计数：成功捕获{$y}条视频,失败捕获{$n}条(其中含存在数据({$r}条))~\n";
if($error)
echo "其中报错：{$error}\n";


echo "\n";
$ob_content = ob_get_contents();
file_put_contents(ROOT.'/logs/monitoring_'.date("Y_m_d").'.log',"DATE ".date('Y-m-d H:i:s')."\n".$ob_content,FILE_APPEND);