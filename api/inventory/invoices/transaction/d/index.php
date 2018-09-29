<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once './../../../../../vendor/autoload.php';
require_once './../../../../../lib/db.php';
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
$app->get('/{item_code}', function (Request $request, Response $response, array $args) {
    
    $item_code = $args['item_code'];
    $db = connect_db();
    $sql = "SELECT * FROM `t_transaction_d` where item_code = '". $item_code ."';";

    $q = $db->prepare($sql);
    $q->execute();
    $dbData = $q->fetch();
    $err = $q->errorinfo();

    $callback = [
        "query" => $dbData,
        "error" => ["code" => $err[0], "message" => $err[2]]
    ];

    return $response->withJson($callback, 200);
});
$app->post('/', function (Request $request, Response $response, array $args) {
   
});
$app->run();
