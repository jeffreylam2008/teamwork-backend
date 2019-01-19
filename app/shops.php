<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/systems/shops', function () {
    /**
     * Shop GET request
     * shop-get
     * 
     * To get shop record 
     */
    $this->get('/', function (Request $request, Response $response, array $args) {
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
});