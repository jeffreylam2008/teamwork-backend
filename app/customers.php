<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/customers', function () {
    /**
     * Customer GET Request
     * customer-get
     * 
     * To get all customer record
     */
    $this->get('/', function(Request $request, Response $response, array $args) {
        $err = "";
        $db = connect_db();
        $q = $db->prepare("select * from `t_customers`;");
        $q->execute();
        $err = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($res))
        {
            foreach ($res as $key => $val) {
                $dbData[] = $val;
            }
            $callback = [
                "query" => $dbData,
                "error" => ["code" => $err[0], "message" => $err[2]]
            ];
            return $response->withJson($callback, 200);
        }
    });
    /**
     * Customer GET Request
     * customer-get-by-code
     * 
     * To get single record based on the customer code
     */
    $this->get('/{cust_code}', function(Request $request, Response $response, array $args){
        $_cust_code = $args['cust_code'];
        $err = "";
        $db = connect_db();
        $q = $db->prepare("select * from `t_customers` where cust_code = '".$_cust_code."';");
        $q->execute();
        $err = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        //var_dump($res);
        if(!empty($res))
        {
            foreach ($res as $key => $val) {
                $dbData[] = $val;
            }
            $callback = [
                "query" => $dbData,
                "error" => ["code" => $err[0], "message" => $err[2]]
            ];
            return $response->withJson($callback, 200);
        }
    });
    /**
     * Customer POST Request
     * customer-post
     * 
     * To create new record on customer table 
     */
    $this->post("/", function(Request $request, Response $response, array $args){
       
    });
});