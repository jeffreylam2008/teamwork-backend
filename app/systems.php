<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/systems/backup/', function () {
    /**
     * Systems GET Request
     * systems get
     * 
     * To get all items record
     */
    $this->get('products/', function (Request $request, Response $response, array $args) {
        $err=[];
        $pdo = new Database();
        $db = $pdo->connect_db();
        $q = $db->prepare("
            SELECT 
                ti.item_code, 
                ti.eng_name,
                ti.chi_name,
                ti.desc,
                ti.price,
                ti.price_special,
                tic.desc as category,
                ti.unit
            FROM `t_items` as ti
            LEFT JOIN `t_items_category` as tic ON ti.cate_code = tic.cate_code;
        ");
        $q->execute();
        $err = $q->errorinfo();
        //disconnection DB
        $pdo->disconnect_db();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($res))
        {
            $callback = [
                "query" => $res,
                "error" => ["code" => $err, "message" => $err]
            ];
            return $response->withJson($callback, 200);
        }
        else
        {
            $callback = [
                "error" => ["code" => "99999", "message" => "Not Found!"]
            ];
            return $response->withJson($callback, 404);
        }
    });
    $this->get('categories/', function (Request $request, Response $response, array $args) {
        $err=[];
		$pdo = new Database();
		$db = $pdo->connect_db();
		$q = $db->prepare("
            SELECT 
                tic.cate_code,
                tic.desc
            FROM `t_items_category` as tic
            ORDER BY tic.cate_code;
        ");
		$q->execute();
		$err = $q->errorinfo();
		// disconnect DB
		$pdo->disconnect_db();
		$res = $q->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($res))
        {
            $callback = [
                "query" => $res,
                "error" => ["code" => $err, "message" => $err]
            ];
            return $response->withJson($callback, 200);
        }
		else
        {
            $callback = [
                "error" => ["code" => "99999", "message" => "Not Found!"]
            ];
            return $response->withJson($callback, 404);
        }
		
    });
    $this->get('customers/', function (Request $request, Response $response, array $args) {
        $err=[];
		$pdo = new Database();
		$db = $pdo->connect_db();
		$q = $db->prepare("
            SELECT 
                tc.cust_code,
                tc.mail_addr,
                tc.shop_addr,
                tc.attn_1,
                tc.phone_1,
                tc.fax_1,
                tc.email_1,
                tc.attn_2,
                tc.phone_2,
                tc.fax_2,
                tc.email_2,
                tc.statement_remark,
                tc.name,
                (SELECT payment_method FROM `t_payment_method` WHERE pm_code = tc.pm_code) as pm_code,
                (SELECT terms FROM `t_payment_term` WHERE pt_code = tc.pt_code) as pt_code,
                tc.remark,
                tc.district_code,
                tc.delivery_addr,
                tc.from_time,
                tc.to_time,
                tc.delivery_remark,
                tc.status,
                tai.company_BR,
                tai.company_sign,
                tai.group_name,
                tai.attn,
                tai.tel,
                tai.fax,
                tai.email
            FROM `t_customers` as tc 
            LEFT JOIN `t_accounts_info` as tai 
            ON tc.cust_code = tai.cust_code;
        ");
		$q->execute();
		$err = $q->errorinfo();
		// disconnect DB
		$pdo->disconnect_db();
		$res = $q->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($res))
        {
            $callback = [
                "query" => $res,
                "error" => ["code" => $err, "message" => $err]
            ];
            return $response->withJson($callback, 200);
        }
		else
        {
            $callback = [
                "error" => ["code" => "99999", "message" => "Not Found!"]
            ];
            return $response->withJson($callback, 404);
        }
		
    });
    $this->get('suppliers/', function (Request $request, Response $response, array $args) {
        $err=[];
		$pdo = new Database();
		$db = $pdo->connect_db();
		$q = $db->prepare("
            SELECT 
                ts.supp_code,
                ts.mail_addr,
                ts.attn_1,
                ts.phone_1,
                ts.fax_1,
                ts.email_1,
                ts.name,
                (SELECT payment_method FROM `t_payment_method` WHERE pm_code = ts.pm_code) as pm_code,
                (SELECT terms FROM `t_payment_term` WHERE pt_code = ts.pt_code) as pt_code,
                ts.remark,
                ts.status
            FROM `t_suppliers` as ts;
        ");
		$q->execute();
		$err = $q->errorinfo();
		// disconnect DB
		$pdo->disconnect_db();
		$res = $q->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($res))
        {
            $callback = [
                "query" => $res,
                "error" => ["code" => $err, "message" => $err]
            ];
            return $response->withJson($callback, 200);
        }
		else
        {
            $callback = [
                "error" => ["code" => "99999", "message" => "Not Found!"]
            ];
            return $response->withJson($callback, 404);
        }
    });
    $this->get('paymentmethods/', function (Request $request, Response $response, array $args) {
        $err=[];
		$pdo = new Database();
		$db = $pdo->connect_db();
		$q = $db->prepare("
            SELECT 
                tpm.pm_code,
                tpm.payment_method
            FROM `t_payment_method` as tpm;
        ");
		$q->execute();
		$err = $q->errorinfo();
		// disconnect DB
		$pdo->disconnect_db();
		$res = $q->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($res))
        {
            $callback = [
                "query" => $res,
                "error" => ["code" => $err, "message" => $err]
            ];
            return $response->withJson($callback, 200);
        }
		else
        {
            $callback = [
                "error" => ["code" => "99999", "message" => "Not Found!"]
            ];
            return $response->withJson($callback, 404);
        }
    });
    $this->get('paymentterms/', function (Request $request, Response $response, array $args) {
        $err=[];
		$pdo = new Database();
		$db = $pdo->connect_db();
		$q = $db->prepare("
            SELECT 
                tpt.pt_code,
                tpt.terms
            FROM `t_payment_term` as tpt;
        ");
		$q->execute();
		$err = $q->errorinfo();
		// disconnect DB
		$pdo->disconnect_db();
		$res = $q->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($res))
        {
            $callback = [
                "query" => $res,
                "error" => ["code" => $err, "message" => $err]
            ];
            return $response->withJson($callback, 200);
        }
		else
        {
            $callback = [
                "error" => ["code" => "99999", "message" => "Not Found!"]
            ];
            return $response->withJson($callback, 404);
        }
    });
});

$app->group('/api/v1/systems/restore', function () {
    $this->post('products/', function (Request $request, Response $response, array $args) {
        $body = json_decode($request->getBody(), true);
        return $response->withJson($body, 200);
    });
});