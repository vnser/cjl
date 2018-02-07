<?php

namespace Lib;
/**
 * Redis缓存驱动
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 */
class Redis
{

    private $options = array(
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => false,
        'persistent' => false,  //是否开启长连接
        'auth' => false,
        'db' => 0,
        'prefix' => ''
    );

    static private $_instance = [];

    /**
     * 取得缓存类实例
     * @static
     * @access public
     * @param array $options
     * @param bool
     * @return self
     */
    static public function Instance($options = array(), $one = true)
    {
        $guid = $one ? $one : md5(join(',', $options));
        if (!isset(static::$_instance[$guid]))
            static::$_instance[$guid] = new static($options);
        return static::$_instance[$guid];
    }

    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     * @throws
     */
    public function __construct($options = array())
    {
        if (!extension_loaded('redis')) {
            throw new \Exception('CALL TO REDIS');
        }
        $options = array_merge($this->options, $options);
        $this->options = $options;
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler = new \Redis;
        $options['timeout'] === false ?
            $this->handler->$func($options['host'], $options['port']) :
            $this->handler->$func($options['host'], $options['port'], $options['timeout']);
        //增加代码，设置redis安全性，增加认证密码
        if (isset($options['auth']) && $options['auth']) {
            $this->handler->auth($options['auth']);
        }
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name)
    {
        //N('cache_read',1);
        $value = $this->handler->get($this->options['prefix'] . $name);
        $jsonData = json_decode($value, true);
        return ($jsonData === NULL) ? $value : $jsonData;    //检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value 存储数据
     * @param integer $expire 有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null)
    {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        $name = $this->options['prefix'] . $name;
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if (is_int($expire) && $expire) {
            $result = $this->handler->setex($name, $expire, $value);
        } else {
            $result = $this->handler->set($name, $value);
        }
        /*   if($result && $this->options['length']>0) {
               // 记录缓存队列
               $this->queue($name);
           }*/
        return $result;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name)
    {
        return $this->handler->delete($this->options['prefix'] . $name);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clear()
    {
        return $this->handler->flushDB();
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        return call_user_func_array([$this->handler, $name], $arguments);
    }
}
