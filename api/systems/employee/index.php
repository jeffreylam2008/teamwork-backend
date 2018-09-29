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
$app->get('/{username}', function (Request $request, Response $response, array $args) {
    $username = $args['username'];
    $db = connect_db();
    if(isset($username) && !empty($username))
    {
        $sql = "select * from `t_employee` where username = '".$username."'; ";
        $q = $db->prepare($sql);
        $q->execute();
        $result = $q->fetch();
        $err = $q->errorinfo();
        $callback = [
            "query" => $result,
            "error" => ["code" => $err[0], "message" => $err[2]]
        ];
        return $response->withJson($callback,200);
    }
    

});

$app->run();
