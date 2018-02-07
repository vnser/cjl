<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 16-7-23
 * Time: 上午3:21
 */
define('VERSION','1.1');
define('ROOT',__DIR__);
include_once(ROOT.'/config/config.php');
include_once(ROOT.'/comm/functions.php');
include_once(ROOT.'/Lib/Redis.class.php');
include_once(ROOT.'/Lib/Page.class.php');
include_once(ROOT.'/Lib/Curl.class.php');

//初始化redis
\Lib\Redis::Instance($config['REDIS']);