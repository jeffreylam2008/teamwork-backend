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
        $err = [];
        $pdo = new Database();
		$db = $pdo->connect_db();
        $q = $db->prepare("
        SELECT tc.*, 
        td.district_chi,
        td.district_eng,
        td.region,
        te.username,
        te.default_shopcode,
        tpm.payment_method,
        tpt.terms FROM `t_customers` as tc 
        LEFT JOIN `t_district` as td ON tc.district_code = td.district_code 
        LEFT JOIN `t_employee` as te ON tc.employee_code = te.employee_code 
        LEFT JOIN `t_payment_method` as tpm ON tc.pm_code = tpm.pm_code
        LEFT JOIN `t_payment_term` as tpt ON tc.pt_code = tpt.pt_code
        ");
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
     * customer-get
     * 
     * To get next customer code
     */
    $app->get('/next', function(Request $request, Response $response, array $args) {
        $err = [];
        $pdo = new Database();
		$db = $pdo->connect_db();
        $q = $db->prepare("SELECT `cust_code` FROM `t_customers` ORDER BY `cust_code` DESC LIMIT 1;");
        $q->execute();
        $err = $q->errorinfo();
        $res = $q->fetch();
        $str = substr($res['cust_code'],1);
        $str = (int) $str + 1;
        $str = "C".$str;
        $res['cust_code'] = $str;
        if(!empty($res))
        {
            $callback = [
                "query" => $res,
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
        $err = [];
        $pdo = new Database();
		$db = $pdo->connect_db();
        $q = $db->prepare("
            SELECT * FROM `t_customers` as tc LEFT JOIN `t_district` as td ON (tc.district_code = td.district_code) WHERE `cust_code` = '".$_cust_code."';
        ");
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
        $err = [];
		$pdo = new Database();
		$db = $pdo->connect_db();
		// POST Data here
		$body = json_decode($request->getBody(), true);
        $_now = date('Y-m-d H:i:s');
        //var_dump($body);
		$db->beginTransaction();
		$q = $db->prepare("
            INSERT INTO `t_customers` (
                `cust_code`, `mail_addr`, `shop_addr`,
                `delivery_addr`, `attn_1`, `phone_1`,
                `fax_1`, `email_1`, `attn_2`, 
                `phone_2`, `fax_2`, `email_2`, 
                `statement_remark`, `name`, `group_name`,
                `pm_code`, `pt_code`, `remark`, `create_date`
            ) VALUES (
                '".$body["i-cust_code"]."',
                '".$body["i-mail_addr"]."',
                '".$body["i-shop_addr"]."',
                '".$body["i-delivery_addr"]."',
                '".$body["i-attn_1"]."',
                '".$body["i-phone_1"]."',
                '".$body["i-fax_1"]."',
                '".$body["i-email_1"]."',
                '".$body["i-attn_2"]."',
                '".$body["i-phone_2"]."',
                '".$body["i-fax_2"]."',
                '".$body["i-email_2"]."',
                '".$body["i-statement_remark"]."',
                '".$body["i-name"]."',
                '".$body["i-group_name"]."',
                '".$body["i-pm_code"]."',
                '".$body["i-pt_code"]."',
                '".$body["i-remark"]."',
                '".$_now."'
            );
        ");
		$q->execute();
		// no fatch on update 
		$err = $q->errorinfo();
        $db->commit();

        // disconnect DB
        $pdo->disconnect_db();
        
		$callback = [
			"query" => "", 
			"error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
		];
		return $response->withJson($callback, 200);
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
		$pdo = new Database();
		$db = $pdo->connect_db();
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
            `remark` = '".$body["i-remark"]."',
            `modify_date` = '".$_now."'
            WHERE `cust_code` = '".$_cust_code."';
        ");
		$q->execute();
		// no fatch on update 
		$err = $q->errorinfo();
        $db->commit();
        // disconnect DB
        $pdo->disconnect_db();
        
		$callback = [
			"query" => "", 
			"error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
		];
		return $response->withJson($callback, 200);
		
	});
});