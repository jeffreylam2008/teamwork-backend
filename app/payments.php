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
    $this->get('/method/', function (Request $request, Response $response, array $args) {
        $pdo = new Database();
		$db = $pdo->connect_db();
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
        // disconnect DB
        $pdo->disconnect_db();
        
        $callback = [
            "query" => $new,
            "error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
        ];
        return $response->withJson($callback,200);
    });
    /**
     * Payment term GET Request
     * Payment term-get
     * 
     * To get all payment record 
     */
     $this->get('/term/', function (Request $request, Response $response, array $args) {
        $pdo = new Database();
		$db = $pdo->connect_db();
        $sql = "select * from `t_payment_term`;";
        $q = $db->prepare($sql);
        $q->execute();
        $err = $q->errorinfo();
        $result = $q->fetchAll();
        // disconnect DB
        $pdo->disconnect_db();

        //var_dump($result);
        $new = [];
        foreach($result as $k => $v)
        {
            extract($v);
            $new[$pt_code] = $v;
        }
        $err = $q->errorinfo();
        $callback = [
            "query" => $new,
            "error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
        ];
        return $response->withJson($callback,200);
    });
    $this->post('/', function (Request $request, Response $response, array $args) {

    });
});