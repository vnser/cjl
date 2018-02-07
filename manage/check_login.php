<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2017/12/1 0001
 * Time: 下午 17:34
 */
require_once '../comm.php';
$admin_sid = $_COOKIE['admin_sid'];
$sid  = md5(join($config['ADMIN_USER']));
if($sid != $admin_sid){
    setcookie('admin_sid','',-1,'/');
    header('location: ./login.php');
    exit;
}