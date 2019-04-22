<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/customers', function () use($app) {
    /**
     * Customer GET Request
     * customer-get
     * 
     * To get all customer record
     */
    $app->get('/', function(Request $request, Response $response, array $args) {
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
                "error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
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
    $app->get('/{cust_code}', function(Request $request, Response $response, array $args){
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
                $dbData[$key] = $val;
            }
            $callback = [
                "query" => $dbData,
                "error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
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
    $app->post("/", function(Request $request, Response $response, array $args){
       
    });

    /**
	 * Customers PATCH Request
	 * Customers-patch
	 * 
	 * To update current record on DB
	 */
	$app->patch('/{cust_code}', function(Request $request, Response $response, array $args){
		$err = [];
		$_cust_code = $args['cust_code'];
		$db = connect_db();
		// POST Data here
		$body = json_decode($request->getBody(), true);
		$_now = date('Y-m-d H:i:s');
		$db->beginTransaction();
		$q = $db->prepare("
            UPDATE `t_customers` SET 
            `mail_addr` = '".$body["i-mail_addr"]."',
            `shop_addr` = '".$body["i-shop_addr"]."',
            `delivery_addr` = '".$body["i-delivery_addr"]."',
            `attn_1` = '".$body["i-attn_1"]."',
            `phone_1` = '".$body["i-phone_1"]."',
            `email_1` = '".$body["i-email_1"]."',
            `attn_2` = '".$body["i-attn_2"]."',
            `phone_2` = '".$body["i-phone_2"]."',
            `fax_1` = '".$body["i-fax_1"]."',
            `fax_2` = '".$body["i-fax_2"]."',
            `email_2` = '".$body["i-email_2"]."',
            `statement_remark` = '".$body["i-statement_remark"]."',
            `name` = '".$body["i-name"]."',
            `group_name` = '".$body["i-group_name"]."',
            `pm_code` = '".$body["i-pm_code"]."',
            `pt_code` = '".$body["i-pt_code"]."',
            `modify_date` = '".$_now."'
            WHERE `cust_code` = '".$_cust_code."';
        ");
		$q->execute();
		// no fatch on update 
		$err = $q->errorinfo();
		$db->commit();
		$callback = [
			"query" => "", 
			"error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
		];
		return $response->withJson($callback, 200);
		
	});
});