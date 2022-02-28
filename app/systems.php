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
$app->group('/api/v1/systems/master', function () {
    $this->get('/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_data['items'] = [];
        $_data['shops'] = [];
        $_data['customers'] = [];
        $_data['paymentmethod'] = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";

        $pdo = new Database();
        $db = $pdo->connect_db();
        
        /**
         * Items
         */
        $sql = "SELECT";
        $sql .= " ti.uid,";
        $sql .= " ti.item_code,";
        $sql .= " ti.eng_name,";
        $sql .= " ti.chi_name,";
        $sql .= " ti.desc,";
        $sql .= " ti.price,";
        $sql .= " ti.price_special,";
        $sql .= " ti.cate_code,";
        $sql .= " ti.unit,";
        $sql .= " tw.qty as 'stockonhand',";
        $sql .= " tw.type";
        $sql .= " FROM `t_items` as  ti";
        $sql .= " LEFT JOIN `t_warehouse` as tw ON ti.item_code = tw.item_code";
        $sql .= " ORDER BY ti.item_code";
        // echo $sql1."\n";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $_data['items'] = $q->fetchAll(PDO::FETCH_ASSOC);

        /**
         * Shops
         */
        $sql = "select * from `t_shop`;";
        // echo $sql1."\n";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $_data['shops'] = $q->fetchAll(PDO::FETCH_ASSOC);

        /**
         * Customers
         */ 
        $sql = "SELECT ";
        $sql .= " tc.cust_code,";
        $sql .= " tc.name,";
        $sql .= " tc.pm_code";
        $sql .= " FROM `t_customers` as tc";
        // echo $sql1."\n";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $_data['customers'] = $q->fetchAll(PDO::FETCH_ASSOC);

        /**
         * Payment Method
         */
        $sql = "SELECT * FROM `t_payment_method`;";
        // echo $sql1."\n";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $_data['paymentmethod'] = $q->fetchAll(PDO::FETCH_ASSOC);

        /**
         * Suppliers 
         */
        $sql = "SELECT * FROM `t_suppliers`;";
        // echo $sql1."\n";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $_data['suppliers'] = $q->fetchAll(PDO::FETCH_ASSOC);

        //disconnection DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");

        foreach($_err as $k => $v)
        {
            if($v[0] != "00000"){
                $_result = false;
                $_msg .= $v[1]."-".$v[2]."|";
            }
            else
            {
                $_msg .= "SQL #".$k.": SQL execute OK! | ";
            }
        }
    
        if($_result)
        {
            $_callback["query"] = $_data;
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Master data fetch OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withJson($_callback, 200);
        }
        else
        {
            $_callback = ["query" => ""];
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Master data fetch fail!";
            $this->logger->addInfo("SQL execute ".$_msg);  
            return $response->withJson($_callback, 404);
        }
    });
});


$app->group('/api/v1/network/status', function (){
    $this->get('/', function (Request $request, Response $response, array $args){
        $_callback = ["Error" => "network health","Code"=> 0 ];
        return $response->withJson( $_callback , 200);
    });
});

