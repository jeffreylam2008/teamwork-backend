<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/test', function () {
    /**
     * Shop GET request
     * shop-get
     * 
     * To get shop record 
     */
    $this->get('/', function (Request $request, Response $response, array $args) {

        $_param = array();
        $_param = $request->getQueryParams();
        if(empty($_param))
        {
            $_param['page'] = 50;
            $_param['nshow'] = 3;
        }
        //var_dump($_param);
        // $err = [];
        // $pdo = new Database();
		// $db = $pdo->connect_db();
        // $q = $db->prepare("select * from `t_shop`;");
        // $q->execute();
        // $err = $q->errorinfo();
        // // disconnect DB
        // $pdo->disconnect_db();
        
        // //$result = $db->query( "select * from `t_shop`;");
        // foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $key => $val) {
        //     $dbData[] = $val;
        // }
        // $callback = [
        //     "query" => $dbData,
        //     "error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
        // ];
        // return $response->withJson($callback, 200);
        return $response->withJson($_param, 200);
    });
});