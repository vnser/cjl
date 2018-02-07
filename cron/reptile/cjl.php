<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 16-6-26
 * Time: 上午4:49
 */

include_once('../../comm.php');

exit('exit cjl~');
set_time_limit(0);
//读取上次采集信息
$collect = \Lib\Redis::Instance()->get('cjl_collect');
if(!$collect){
    $collect = array(
        'page'=>1,
        'type'=>'c=1'
    );
}
$server_url = db()->query('select concat(scheme,video_host) from vj_server where id=1 limit 1')->fetchColumn(0);
$url = "{$server_url}/videos?page={$collect['page']}&{$collect['type']}";
//获取cjl内容
$result = file_get_contents($url);
$match_res = preg_match_all('/<div class="well well-sm">[\s\S]*?<a href="\/video\/(\d*?)\/[\s\S]*?"[\s\S]*?<div class="thumb-overlay">[\s\S]*?<img src="(.*)" title="(.*?)"[\s\S]*?<div class="duration">\s*?([\S]*?)\s*?<\/div>/',$result,$resArr);

if(!$match_res){
    exit("~~~^_^~~~ 捕获内容获取失败 \n");
}
if($collect['page'] <= 1){
    //获取最后总页
    preg_match('/">(\d*?)<\/a><\/li><li><a\s*?href=\"\S*?\"\s*?class=\"prevnext\"/',$result,$page_count);
    //计算总页数
    $collect['page_count'] = ($page_count[1] >= 10) ? $page_count[1]+1 : $page_count[1];
}
//翻页
$collect['page']++;
\Lib\Redis::Instance()->set('cjl_collect',$collect,0);

$y = 0;
$n = 0;
$r = 0;
foreach ($resArr[1] as $k=>$v){
    $video = db()->query("select * from vj_video where `video_id`='cjl_{$v}' limit 1")->fetch(2);
    if($video){
        $n++;
        $r++;
        continue;
    }
    $img_url = parse_url($resArr[2][$k]);
    try{
        $sql = "insert into `vj_video`(`type_id`,`video_id`,`title`,`video_url`,`video_img`,`video_time`) VALUE ((SELECT id FROM `vj_type` where `server_id`='1' and `item`='{$collect['type']}' limit 1),'cjl_{$v}','{$resArr[3][$k]}','/media/player/config_m.php?vkey={$v}','{$img_url['path']}','{$resArr[4][$k]}');";
        $res = db()->exec($sql);
        $y++;
    }catch (Exception $exception){
        $n++;
    }
}
echo "<(￣ˇ￣)/ 捕获完成(cjl)
抓取Url: {$url}
抓取信息：分类({$collect['type']}),页码：".($collect['page']-1).",总页: {$collect['page_count']}
抓取计数：成功捕获{$y}条视频,失败捕获{$n}条(其中含存在数据({$r}条))~\n";
if($collect['page'] >= $collect['page_count']){
    //最后一页
    $typeInfo = db()->query("select * from `vj_type` where `server_id`='1' and id=(select id+1 from `vj_type` where `server_id`='1' and `item`='{$collect['type']}') limit 1")->fetch(PDO::FETCH_ASSOC);
    if(!$typeInfo){
        //没有下一个分类
        $collect = array(
            'page'=>1,
            'type'=>'c=1'
        );
    }else{
        $collect = array(
            'page'=>1,
            'type'=>$typeInfo['item']
        );
    }
    \Lib\Redis::Instance()->set('cjl_collect',$collect,0);
    echo "已经捕获到最后一页了\n";
}
echo "\n";

$ob_content = ob_get_contents();
file_put_contents(ROOT.'/logs/monitoring_'.date("Y_m_d").'.log',"DATE ".date('Y-m-d H:i:s')."\n".$ob_content,FILE_APPEND);

