<?php

require_once('Lib/Common/start.php');

$server = new swoole_http_server("127.0.0.1", 9999);

$server->set(
    array(
        'user' => 'rouqiu',
        'group' => 'rouqiu',
        'task_worker_num' => 100,
    )
);


$server->on('request', function ($request, $response) use ($server) {

    $request = $request->server['request_uri'];//query_string

    $request = explode('/', $request);//请求的参数

    switch ($request[1]) {

        case 'tasktest':

            $server->task("taskcallback", -1, function (swoole_server $server, $task_id, $data) {
                echo "Task Callback: ";
                echo $task_id . '   ' . $data;
                echo PHP_EOL;
            });

            break;
        case 'login':

            $server->task("write", -1, function (swoole_server $server, $task_id, $data) use($request){

                $c = ucwords($request[1]) . 'Controller';
                $m = isset($request[2]) ? $request[2] : 'index';
                $handle = new $c;
                $handle->$m();

                echo $task_id . '   ' . $data;
                echo PHP_EOL;
            });
            break;
        case 'Base':

            $c = ucwords($request[1]) . 'Controller';
            $m = isset($request[2]) ? $request[2] : 'index';
            $handle = new $c;
            $handle->$m();

            break;

        default:

            break;
    }
    $response->end();
});


$server->on('task', function (swoole_server $server, $task_id, $src_worker_id, $data) {

    echo $task_id . 'start' . 'data is' . $data;

    echo PHP_EOL;

    return 0;
});


$server->on('finish', function (swoole_server $server, $task_id, $data) {

    echo $task_id . 'end';

    echo PHP_EOL;

    return 0;
});

$server->start();