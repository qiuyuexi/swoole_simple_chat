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
    public function addOnLine ($fd, $name)
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

        $name = $userlist[$fd]['name'];//取得下线人的电话

        unset($userlist[$fd]);//移除

        $redis->addString('userlist', jsonEncode($userlist));

        return $name;
    }


    /**
     * @descripe 获取在线的用户列表
     * @return array|int|mixed
     */
    public function getOnLine ($server)
    {
        $redis = $this->base->getRedisHandle();//链接redis

        $userlist = $redis->getString('userlist');//取出存储用户列表的数组

        $userlist = isset($userlist) ? jsonDecode($userlist) : array();//判断是否是null.

        if(count($userlist) != count($server->connections)){

            $online = array();

            writeLog($userlist);
            foreach($server->connections as $fd)
            {
                if(isset($userlist[$fd]) && !empty($userlist[$fd])){

                    $online[$fd] = $userlist[$fd];
                }
            }

            $userlist = $online;

            $redis->addStringT('userlist', 86400, jsonEncode($userlist));
        }

        return $userlist;
    }


    /**
     * @descripe 给在线的客户端推送消息
     * @param swoole_websocket_server $server
     * @param array $list
     * @param string $msg
     * @param int $fd 忽略推送的客户端
     */

    public function pushMsg ( $server, $msg = '暂无数据', $ignore_fd = 0,$method = '暂无数据')
    {
        foreach($server->connections as $fd)
        {
            if($fd != $ignore_fd){
                $server->push($fd, $msg);
            }
        }
    }


    /**
     * @descripe 告诉所有用户，在线人数多少人
     * @param swoole_websocket_server $server
     * @param string $msg
     */
    public function notifyOnline ( $server)
    {

        $list = $this->getOnline($server);
        foreach($list as $key => $value)
        {
            $msg = jsonEncode(array('type' => '2', 'msg' => '在线人数详情', 'list' =>$list));

            $server->push($key, $msg);
        }
    }
}