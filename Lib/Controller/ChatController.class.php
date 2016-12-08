<?php

class ChatController
{

    private $base;

    public function __construct ()
    {

        $this->base = new BaseController();
    }

    /**
     * @descripe 新增用户,写入在线用户列表中
     * @param $fd 文件描述符
     * @param $name 用户的姓名
     */
    public function addOnLine ($fd, $name, $server)
    {

        $redis = $this->base->getRedisHandle();//链接redis

        $userlist = $redis->getString('userlist');//取出存储用户列表的数组

        $userlist = isset($userlist) ? jsonDecode($userlist) : array();//判断是否是null.

        $userlist[$fd] = array('fd' => serialize($fd), 'name' => $name);//写入数据

        $redis->addStringT('userlist', 86400, jsonEncode($userlist));
    }


    /**
     * @descripe 将用户从在线列表中移除
     * @param $fd文件描述符
     */
    public function offOnline ($fd)
    {

        $redis = $this->base->getRedisHandle();//链接redis

        $userlist = $redis->getString('userlist');//取出存储用户列表的数组

        $userlist = isset($userlist) ? jsonDecode($userlist) : array();//判断是否是null.

        unset($userlist[$fd]);

        $redis->addString('userlist', jsonEncode($userlist));
    }


    /**
     * @descripe 获取在线的用户列表
     * @return array|int|mixed
     */
    public function getOnLine ()
    {

        $redis = $this->base->getRedisHandle();//链接redis

        $userlist = $redis->getString('userlist');//取出存储用户列表的数组

        $userlist = isset($userlist) ? jsonDecode($userlist) : array();//判断是否是null.

        return $userlist;
    }

    /**
     * @descripe 发送消息
     * @param swoole_websocket_server $server
     * @param array $list
     * @param string $msg
     * @param int $fd 忽略推送的客户端
     */

    public function pushMsg (swoole_websocket_server $server, $msg = '', $fd = 0)
    {

        $list = $this->getOnline();

        foreach ($list as $key => $value) {


            if (unserialize($value['fd']) == $fd) continue;

            $server->push(unserialize($value['fd']), $msg);
        }
    }


    /**
     * @descripe 告诉所有用户，在线人数多少人
     * @param swoole_websocket_server $server
     * @param string $msg
     */
    public function notifyOnline (swoole_websocket_server $server)
    {

        $list = $this->getOnline();

        foreach ($list as $key => $value) {

            $msg = jsonEncode(array('type' => '2', 'msg' => '在线人数详情', 'list' => $list));

            echo $msg;

            $server->push(unserialize($value['fd']), $msg);
        }
    }
}