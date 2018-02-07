<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2016/12/25
 * Time: 4:51
 */
//exit;

require_once 'comm.php';
include_once(ROOT . '/checklogin.php');
try {
  /*  if(!isMobile()){
        thrown([
            'title'=>'温馨提示',
            'content'=>'请在移动端打开操作(建议您使用UC、QQ浏览器操作可以获得更好的体验)!',
            'btn'=>'好的,我知道了'
        ]);
    }*/
    if ($login_flag) {
        //登录状态

        //查询服务器
        $server = db()->query('select * from (select s.name,s.id,s.sort from vj_server s LEFT join vj_type t on t.server_id=s.id LEFT join vj_video v on v.type_id=t.id  where s.hide<>1 or v.local=1 GROUP BY s.id) a ORDER by sort desc');

        //查询服务器子分类
        $type_where = "";
        $server_id = addslashes($_GET['server_id']);
        if (!empty($server_id)) {
            $type_where[] = "server_id='{$server_id}'";
        }
        if (!empty($type_where)) {
            $type_where = ' where ' . join(' and ', $type_where);
            //查询分类
            $type_list = db()->query("SELECT t.id,concat(t. NAME, '(', if (v.count IS NULL,0,v.`count`), ')') name FROM `vj_type` t LEFT JOIN (SELECT id,count(*) `count`,type_id FROM `vj_video` GROUP BY type_id) v ON v.type_id = t.id {$type_where}")->fetchAll(2);
        }

        //视频条件
        $order = "rand()";
        if (isset($_GET['all'])) {
            $order = 'v.id';
        }
        $wheres = [];
        if (!empty($_GET['type_id'])) {
            $type_id = addslashes($_GET['type_id']);
            $wheres[] = "v.type_id='{$type_id}'";
        }
        if (!empty($_GET['so'])) {
            $so = addslashes($_GET['so']);
            $wheres[] = "(v.title like '%{$so}%' or v.video_id='{$so}' or v.id='{$so}')";
        }
        if (!empty($_GET['server_id'])) {
            $server_id = addslashes($_GET['server_id']);
            $wheres[] = "t.server_id like '%{$server_id}%'";
        }

        if (!empty($wheres)) {
            $order = 'v.id desc';
        }
        if (isset($_GET['local'])) {
            //本地视频
            $order = 'rand()';
            $wheres[] = "v.local=1";
        }

        if(isset($_GET['digest'])){
            //精选
            $order = 'rand()';
            $wheres[] = "vc.digest=1";
        }

        if($_GET['_order']){
            $order = addslashes($_GET['_order']);
        }
        if($_GET['t_end']){
            $wheres[] = "v.video_time>='{$_GET['t_end']}'";
        }
        $wheres[] = '(s.hide <> 1  or v.local = 1 )';
        $where = ' where ' . join(' and ', $wheres);


        $count = db()->query('select count(*) `count` from vj_video v join vj_type t on t.id=v.type_id join vj_server s on s.id=t.server_id  join vj_video_coll vc USING (video_id)' . $where . ' order by ' . $order . ' ')->fetch(PDO::FETCH_ASSOC);

        $total_page = ceil($count['count'] / 20);
        $now_page = (($_GET['page'] and $_GET['page'] > 0) ? ($_GET['page'] > $total_page ? $total_page : $_GET['page']) : 1);
        $first_limit = (($now_page - 1) * 20);
        $first_limit = $first_limit < 0? 0 :$first_limit;

        $result = db()->query($sql = 'select vc.digest,v.local,s.name s_name,s.use_video,s.use_image,v.id,title,video_url,video_img,video_time,img_host,video_host,scheme,server_id from vj_video v join vj_type t on t.id=v.type_id join vj_server s on s.id=t.server_id join vj_video_coll vc USING (video_id)' . $where . ' order by ' . $order . ' limit ' . $first_limit . ',20 ')->fetchAll(PDO::FETCH_ASSOC);
        //var_dump($sql);
        if (!$result) {
            thrown([
                'title' => '提示',
                'content' => '没有查询到相关的视频!'
            ]);
        }

        /*
        $server_ids = array_column($result,'server_id');
        if(in_array(3,$server_ids)){
            //访问服务器
            $qyule_server = db()->query('select concat(scheme,video_host,"/") url from vj_server where id=3 limit 1')->fetch(2);
            $iframe = '<iframe src="'.$qyule_server['url'].'" style="display: none" ></iframe>';
        }*/
    }
    /* $avtb_server = db()->query('select concat(scheme,video_host,"/") url from vj_server where id=2 limit 1')->fetch(2);*/

    include ROOT . '/view/index.html';
} catch (Exception $exception) {
    thrown([
        'title' => '发生错误',
        'content' => $exception->getMessage()
    ]);
}
