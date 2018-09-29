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
    $err=[];
    $db = connect_db();
    $q = $db->prepare("SELECT * FROM `t_items` ORDER BY 'item_code';");
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

$app->get('/{item_code}', function(Request $request, Response $response, array $args){
    $err=[];
    $item_code = $args['item_code'];
    $db = connect_db();
    $q = $db->prepare("select * from `t_items` WHERE item_code = '".$item_code."';");
    $q->execute();
    $dbData = $q->fetch();
    $err = $q->errorinfo();

    $callback = [
        "query" => $dbData,
        "error" => ["code" => $err[0], "message" => $err[2]]
    ];

    return $response->withJson($callback, 200);
});

$app->post('/',function(Request $request, Response $response, array $args){
    $err=[];
    $db = connect_db();
    

    // POST Data here
    $body = json_decode($request->getBody(), true);
    //extract($body);
    $db->beginTransaction();
    
    $_now = date('Y-m-d H:i:s');
    $q = $db->prepare("insert into t_items (`item_code`, `eng_name` ,`chi_name`, `desc`, `price`, `price_special`, `cate_code`,`unit`, `create_date`) 
        values (
            '".$body['i-itemcode']."',
            '".$body['i-chiname']."',
            '".$body['i-engname']."',
            '".$body['i-desc']."',
            '".$body['i-price']."',
            '".$body['i-specialprice']."',
            '".$body['i-category']."',
            '".$body['i-unit']."',
            '".$_now."'
        );
    ");
    $q->execute();
    $err = $q->errorinfo();
    $db->commit();
    $callback = [
        "code" => $err[0], 
        "message" => $err[2]
    ];
    return $response->withJson($callback,200);
});

/**
 * Patch request
 * 
 * edit item record to DB
 */
$app->patch('/{item_code}', function(Request $request, Response $response, array $args){
    
    $item_code = $args['item_code'];
    $db = connect_db();

    // // POST Data here
    $body = json_decode($request->getBody(), true);
    
    $db->beginTransaction();
    $q = $db->prepare("UPDATE `t_items` SET (
        `eng_name` = ".$body['i-engname'].",
        `chi_name` = ".$body['i-chiname'].",
        `desc` = ".$body['i-desc'].",
        `price` = ".$body['i-price'].",
        `price_special` = ".$body['i-specialprice'].",
        `cate_code` = ".$body['i-category'].",
        `unit` = ".$body['i-unit'].",
    ) WHERE item_code = '".$item_code."';");
    $q->execute();
    $dbData = $q->fetch();
    $err = $q->errorinfo();
    $db->commit();
    
    $callback = [
        "query" => $body,
        //"error" => ["code" => $err[0], "message" => $err[2]]
    ];

    return $response->withJson($body, 200);
});

/**
 * Delete request
 * 
 * delete record to DB
 */
$app->delete('/{item_code}', function (Request $request, Response $response, array $args) {
    $item_code = $args['item_code'];
    $db = connect_db();
    $q = $db->prepare("DELETE FROM `t_items` WHERE item_code = '".$item_code."';");
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
