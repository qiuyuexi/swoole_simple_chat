<?php

    require_once('Lib/Common/start.php');

    $server = new swoole_websocket_server("0.0.0.0", 9502);//新建一个server服务器

    $chat = new ChatController();//聊天室控制器，包含新增用户，删除用户等

    //基本的设置
    $server->set(array(
        'daemonize'=>0,
    ));


    //监听打开事件
    $server->on('open', function ($server, $request)  use($chat) {

        $chat->addOnLine($request->fd,'游客',$server);
    });



    //监听消息
    $server->on('message', function ($server, $frame) use ($chat) {

        $data = jsonDecode($frame->data);//客户端发送过来的json格式数据

        //判断消息类别
        switch ($data['type']) {

            case "login":

                $msg = "{$data['name']}加入了聊天室";

                $chat->pushMsg($server, jsonEncode(array('type' => '1', 'msg' => $msg)) ,$frame->fd );//通知所有在线的

                $chat->addOnLine($frame->fd,$data['name']);//写入在线的用户的列表中

                break;
            case "msg":

                $msg = "{$data['name']}说:{$data['msg']}";

                $chat->pushMsg($server, jsonEncode(array('type' => '3', 'msg' => $msg)) ,$frame->fd );//通知所有在线的

                break;
            case "onlie":
                $chat->notifyOnline($server);
                break;
            default:
                $server->push($frame->fd, jsonEncode(array('type' => '0', 'msg' => '未知的操作')));
                break;
        }

    });



    //监听关闭事件
    $server->on('close', function ($server, $fd)  use ($chat){

        $chat->offOnline($fd);//将该fd 从redis中删除

        $chat->pushMsg($server, jsonEncode(array('type' => '1', 'msg' => "{$fd}退出了聊天室"),$fd));

    });

    $server->start();

