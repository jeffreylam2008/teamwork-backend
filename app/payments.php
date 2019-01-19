<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/systems/payments',function(){
    /**
     * Payment GET Request
     * payment-get
     * 
     * To get all payment record 
     */
    $this->get('/', function (Request $request, Response $response, array $args) {
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
    $this->post('/', function (Request $request, Response $response, array $args) {

    });
});