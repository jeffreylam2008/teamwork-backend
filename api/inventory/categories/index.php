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
    $err="";
    $db = connect_db();
    $q = $db->prepare("SELECT * FROM `t_items_category` ORDER BY 'cate_code';");
    $q->execute();
    $err = $q->errorinfo();
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $key => $val) {
		$dbData[] = $val;
    }
    $callback = [
        "query" => $dbData,
        "error" => ["code" => $err[0], "message" => $err[2]]
    ];

    if($err[0] == "00000")
        return $response->withJson($callback, 200);
    
});

$app->get('/{cate_code}', function(Request $request, Response $response, array $args){
    $err="";
    $cate_code = $args['cate_code'];
    $db = connect_db();

    $q = $db->prepare("select * from `t_items_category` WHERE cate_code = '".$cate_code."';");
    $q->execute();
    $dbData = $q->fetch();
    $err = $q->errorinfo();

    $callback = [
        "query" => $dbData,
        "error" => ["code" => $err[0], "message" => $err[2]]
    ];

    return $response->withJson($callback, 200);
});
$app->run();
