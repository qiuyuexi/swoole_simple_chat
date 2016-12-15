<?php

    require_once('Lib/Common/start.php');

    $server = new swoole_websocket_server("0.0.0.0", 9502);//新建一个server服务器

    $chat = new ChatController();//聊天室控制器，包含新增用户，删除用户等


    //基本的设置
    $server->set(array(
        'daemonize'=>0,//默认不守护进程
    ));


    //监听打开事件
    $server->on('open', function ($server, $request)  use($chat) {

        $chat->addOnLine($request->fd,'游客',$server);//新增上线
    });



    //监听消息
    $server->on('message', function ($server, $frame) use ($chat) {

        $data = jsonDecode($frame->data);//客户端发送过来的json格式数据

        //判断消息类别
        switch ($data['type']) {

            //当有游客调用登陆接口时
            case "login":

                $msg = "{$data['name']}加入了聊天室";

                $chat->pushMsg($server, jsonEncode(array('type' => '1', 'msg' => $msg)) ,$frame->fd ,'login');//通知所有在线的

                $chat->addOnLine($frame->fd,$data['name']);//写入在线的用户的列表中

                break;


            //发送消息的接口
            case "msg":

                $msg = "{$data['name']}说:{$data['msg']}";

                $chat->pushMsg($server, jsonEncode(array('type' => '3', 'msg' => $msg)) ,$frame->fd,'msg' );//通知所有在线的

                break;

            //在线的人数
            case "online":
                $chat->notifyOnline($server);
                break;

            //未知操作
            default:
                $server->push($frame->fd, jsonEncode(array('type' => '0', 'msg' => '未知的操作')));
                break;
        }

    });


    //监听关闭事件
    $server->on('close', function ($server, $fd)  use ($chat){

        $name = $chat->offOnline($fd);//将该fd 从redis中删除


        $chat->pushMsg($server, jsonEncode(array('type' => '1', 'msg' => "{$name}退出了聊天室" )),$fd,'close');//消息通知

    });

    $server->start();

