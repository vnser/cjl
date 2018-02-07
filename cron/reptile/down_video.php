<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2017/12/7 0007
 * Time: 下午 15:21
 */
require_once(__DIR__ . '/../../comm.php');
set_time_limit(0);
ignore_user_abort(true);
$down_video = db()->query("select v.video_id,v.id,if(use_image,concat(scheme,img_host,video_img),video_img) video_img,if(use_video,concat(scheme,video_host,video_url),video_url) video_url from vj_video v join vj_type t on t.id=v.type_id join vj_server s on s.id=t.server_id JOIN vj_video_coll vc USING(video_id) where vc.down_status=0 and vc.is_local_del=0 and v.local=0 and s.is_down=1 order by vc.create_task_time asc limit 1")->fetch(2);
if(!$down_video){
    exit('No download tasks!');
}
$req_h =   array(
   'http' => array(
       'method' => 'GET',
       'header'=> "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1\r\nReferer: {$down_video['video_url']}",
//           'request_fulluri'=>true
   )
);
stream_context_set_default($req_h);
db()->beginTransaction();
//锁行
db()->query("select * from vj_video where id='{$down_video['id']}' for update")->fetch(2);
$header = @get_headers($down_video['video_url'],1);
if(!$header){
    db()->commit();
    elog("{$down_video['video_id']} request header validation fail!");
}
$codes = [];
$flag = false;
foreach ($header as $k=>$v){
    if(is_int($k)){
        $codes[] = $v;
        $code = explode(' ',$v,3);
        if(in_array($code[1],[200])){
            if( $header['Location']){
                $down_video['video_url'] = $header['Location'];
            }
            db()->commit();
            modifyDownSta($down_video['video_id'],'',3);
            db()->beginTransaction();
            $flag = true;

            $vurl = parse_url($down_video['video_url']);
            $ext = pathinfo($vurl['path'],PATHINFO_EXTENSION);
            $fileName = md5($down_video['video_id']).".{$ext}";
            $savePath = "/var/www/cjl/file/video/{$fileName}";
            $shell = "wget '{$down_video['video_url']}' -O '{$savePath}' &> /dev/null";
            nexec($shell);
            $rootPath = "/file/video/{$fileName}";
            if(filesize($savePath) >= ($header['Content-Length']-10) ){
                $image = \Lib\Curl::main()->url($down_video['video_img'])->header("User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.89 Safari/537.36")->get();
                if($image === false){
                    db()->commit();
                    elog("{$down_video['video_id']} image download fail!\r\nimg_url: {$down_video['video_img']}");
                }
                $img_ext = pathinfo($down_video['video_img'],PATHINFO_EXTENSION);
                $imgName = md5($down_video['video_id']).".{$img_ext}";
                $savePathImg = "/var/www/cjl/file/image/{$imgName}";
                $rootImgPath = "/file/image/{$imgName}";
                $res = @file_put_contents($savePathImg,$image);
                if(!$res){
                    modifyDownSta($down_video['video_id']);
                    db()->commit();
                    elog("{$down_video['video_id']} save image fail!\r\nfilename: $savePathImg");
                }
                db()->exec("update vj_video set local=1,video_url='{$rootPath}',video_img='{$rootImgPath}' where id='{$down_video['id']}'");
                modifyDownSta($down_video['video_id'],'',1);
                db()->commit();
                elog("{$down_video['video_id']} download success!");
//                echo $savePath.' download ok!';
            }
            @unlink($savePath);
            db()->commit();
            elog("{$down_video['video_id']} File download failed! Header_size: ".$header['Content-Length']);
        }
    }
}
if(!$flag){
    //状态吗不正确
    db()->commit();
    elog("{$down_video['video_id']} request header not 200 !\r\nData: ".json_encode($codes,256));
}
function elog($msg,$die = true){
    global $down_video;
    file_put_contents(ROOT.'/logs/down_video_'.date('Ymd').'.log',"[DATE] ".date('Y-m-d H:i:s')."\r\nMessage: $msg\r\n\r\n",FILE_APPEND);
    if(strpos($msg,'success') === false){
        modifyDownSta($down_video['video_id'],$msg);
    }
    $die and exit();
}
function modifyDownSta($video_id,$msg = '',$status=2){
    $modify = "down_status='$status'";
    if($status==2){
        $modify .=",down_msg='{$msg}'";
    }
    db()->exec("update vj_video_coll set $modify where video_id='{$video_id}' limit 1");
}