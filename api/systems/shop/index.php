<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once './../../../vendor/autoload.php';
require_once './../../../lib/db.php';
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
    $err = "";
    $db = connect_db();
    $q = $db->prepare("select * from `t_shop`;");
    $q->execute();
    $err = $q->errorinfo();
    //$result = $db->query( "select * from `t_shop`;");
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $key => $val) {
		$dbData[] = $val;
    }
    $callback = [
        "query" => $dbData,
        "error" => ["code" => $err[0], "message" => $err[2]]
    ];
    return $response->withJson($callback, 200);
});
$app->post('/', function (Request $request, Response $response, array $args) {
    // POST Data here
});
$app->run();
