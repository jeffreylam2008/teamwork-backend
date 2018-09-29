<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once './../../../../vendor/autoload.php';
require_once './../../../../lib/db.php';
$c = new \Slim\Container(); //Create Your container

//Override the default Not Found Handler
$c['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $data = [
            "message" => "param not define"
        ];
        return $c['response']->withJson($data,404);
    };
};
$app = new \Slim\App($c);
$app->get('/', function (Request $request, Response $response, array $args) {
    $db = connect_db();
    $sql = "select * from `t_payment_method`; ";
    $q = $db->prepare($sql);
    $q->execute();
    $err = $q->errorinfo();
    $result = $q->fetchAll();
    //var_dump($result);
    $new = [];
    foreach($result as $k => $v)
    {
        extract($v);
        $new[$pm_code] = $v;
    }
    $err = $q->errorinfo();
    $callback = [
        "query" => $new,
        "error" => ["code" => $err[0], "message" => $err[2]]
    ];
    return $response->withJson($callback,200);
});
$app->post('/', function (Request $request, Response $response, array $args) {
    // $err="";
    
    
    // // POST Data here
    // $body = json_decode($request->getBody(), true);
    // //var_dump($data);

    // $callback = [
    //     "code" => "", "message" => ""
    // ];
    // return $response->withJson($callback,200);
});
$app->run();
