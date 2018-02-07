<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2018/2/7 0007
 * Time: 10:32
 */
require_once '../comm.php';
require_once 'check_login.php';

stream_context_set_default(array(
    'http' => array(
        'method' => 'GET',
        'header'=> "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1",
    )
));

$act = $_GET['do'];
if($act == 'get_data'){
    $server_id = $_POST['server_id'];
    $server = db()->query("select concat(s.scheme,s.site_host) server_url,count(v.id) video_count from vj_server s join vj_type t on t.server_id=s.id left join vj_video v on v.type_id=t.id where s.id = '{$server_id}' ")->fetch(2);
    $rhea = @get_headers($server['server_url'],0);
    $sres = '';
    if($rhea){
        $code = explode(' ',$rhea[0],2)[1];
        $sres = "[请求成功]\r\n".join("\r\n",$rhea);
    }else{
        $code = 'access failure';
        $sres = "[请求失败]";
    }
    ajaxReturn([
        'video_count'=>$server['video_count'],
        'req_res'=>$sres,
        'req_code'=>$code
    ]);
}
if('modify' == $act){
    unset($_POST['id']);
    $sid = $_GET['server_id'];
    $data = joins($_POST,"='","',")."'";
//    var_dump($data);
    $res = db()->exec("update vj_server set $data where id = '{$sid}' limit 1");
    if($res){
        ajaxReturn(['code'=>'修改成功']);
    }
    ajaxReturn(['code'=>'修改失败,或未作出修改']);
}


$title = '取源服务器';
$server = db()->query("select * from vj_server")->fetchAll(2);
//print_r($server);
include_once 'view/server.html';