<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/systems/export/', function () {
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
                tic.cate_code as category,
                ti.type,
                ti.image_name,
                ti.image_body,
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
    $this->get('districts/', function (Request $request, Response $response, array $args){
        $err=[];
		$pdo = new Database();
		$db = $pdo->connect_db();
		$q = $db->prepare("
            SELECT 
                td.district_code,
                td.district_chi,
                td.district_eng,
                td.region
            FROM `t_district` as td;
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

$app->group('/api/v1/systems/import', function () {
    $this->post('/products/', function (Request $request, Response $response, array $args) {
        // check code exist, then insert new
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_now = date('Y-m-d H:i:s');
        $counter = 0;

        $this->logger->addInfo("Entry: POST: BackupNRestore - Products");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $body = json_decode($request->getBody(), true);
        $counter = count($body);
        
        // echo json_encode($counter);
     
        // var_dump($body);

        // // Start transaction 
        $db->beginTransaction();
        for($i=1; $i < $counter; $i++)
        {
            if( $i != 0 )
            {
                $sql = "INSERT IGNORE INTO `t_items` (`item_code`, `eng_name`, `chi_name`, `desc`, `price`, `price_special`,";
                $sql .= "`cate_code`, `type`, `unit`, `image_name`, `image_body`, `create_date`) ";
                $sql .= "VALUES ( ";
                $sql .= "'".$body[$i][0]."',";
                $sql .= "'".$body[$i][1]."',";
                $sql .= "'".$body[$i][2]."',";
                $sql .= "'".$body[$i][3]."',";
                $sql .= "'".$body[$i][4]."',";
                $sql .= "'".$body[$i][5]."',";
                $sql .= "'".$body[$i][6]."',";
                $sql .= "'".$body[$i][7]."',";
                $sql .= "'".$body[$i][8]."',";
                $sql .= "'".$body[$i][9]."',";
                $sql .= "'".$body[$i][10]."',";
                $sql .= "'".$_now."'";
                $sql .= ");";

                if($i == 30){
                    $this->logger->addInfo("SQL String: ".$sql);
                }
                $q = $db->prepare($sql);
                $q->execute();
                $_err[] = $q->errorinfo();

                $sql = "INSERT IGNORE INTO `t_warehouse` (`item_code`, `qty` ,`type`, `create_date`)";
                $sql .= " VALUES (";
                $sql .= " '".$body[$i][0]."',";
                $sql .= " '0',";
                $sql .= " 'in',";
                $sql .= " '".$_now."');";

                if($i == 30){
                    $this->logger->addInfo("SQL String: ".$sql);
                }
                $q = $db->prepare($sql);
                $q->execute();
                $_err[] = $q->errorinfo();
            }

        }
        $db->commit();
        $this->logger->addInfo("Msg: DB commit");
        //disconnection DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");

        foreach($_err as $k => $v)
        {
            if($v[0] != "00000")
            {
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
            $_callback['query'] = "Successful!";
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Total $counter Products Records Imported!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Import Products Fail: Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });
    $this->post('/categories/', function (Request $request, Response $response, array $args) {
        // check code exist, then insert new
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_now = date('Y-m-d H:i:s');
        $counter = 0;

        $this->logger->addInfo("Entry: POST: BackupNRestore - categories");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $body = json_decode($request->getBody(), true);
        $counter = count($body);
        
        // echo json_encode($counter);
        //var_dump($body);

        // // Start transaction 
        $db->beginTransaction();
        for($i=1; $i < $counter; $i++)
        {
            if( $i != 0 )
            {
                $sql = "INSERT IGNORE INTO `t_items_category` (`cate_code`, `desc`, `create_date`) ";
                $sql .= "VALUES ( ";
                $sql .= "'".$body[$i][0]."',";
                $sql .= "'".$body[$i][1]."',";
                $sql .= "'".$_now."'";
                $sql .= ");";

                $q = $db->prepare($sql);
                $q->execute();
                $_err[] = $q->errorinfo();

                if($i == 2){
                    $this->logger->addInfo("SQL String: ".$sql);
                    //$this->actionLogger->addInfo("SQL String: ".$sql);
                }
            }
        }
        $db->commit();
        $this->logger->addInfo("Msg: DB commit");
        //disconnection DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");

        foreach($_err as $k => $v)
        {
            if($v[0] != "00000")
            {
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
            $_callback['query'] = "Successful!";
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Total $counter Categories Records Imported!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Import Categories Fail: Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });
    $this->post('/customers/', function (Request $request, Response $response, array $args) {
        // check code exist, then insert new
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_now = date('Y-m-d H:i:s');
        $counter = 0;

        $this->logger->addInfo("Entry: POST: BackupNRestore - categories");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $body = json_decode($request->getBody(), true);
        $counter = count($body);
        
        // echo json_encode($counter);
     
        //var_dump($body);

        // // Start transaction 
        $db->beginTransaction();
        for($i=1; $i < $counter; $i++)
        {
            if( $i != 0 )
            {
                $sql = "INSERT IGNORE INTO `t_customers` (`cate_code`, `desc`, `create_date`) ";
                $sql .= "VALUES ( ";
                $sql .= "'".$body[$i][0]."',";
                $sql .= "'".$body[$i][1]."',";
                $sql .= "'".$_now."'";
                $sql .= ");";

                $q = $db->prepare($sql);
                $q->execute();
                $_err[] = $q->errorinfo();

                if($i == 2){
                    $this->logger->addInfo("SQL String: ".$sql);
                    //$this->actionLogger->addInfo("SQL String: ".$sql);
                }
            }
        }
        $db->commit();
        $this->logger->addInfo("Msg: DB commit");
        //disconnection DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");

        foreach($_err as $k => $v)
        {
            if($v[0] != "00000")
            {
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
            $_callback['query'] = "Successful!";
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Total $counter Categories Records Imported!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Import Categories Fail: Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });
});

$app->group('/api/v1/network/status', function (){
    $this->get('/', function (Request $request, Response $response, array $args){
        $_callback = ["Error" => "network health","Code"=> 0 ];
        return $response->withJson( $_callback , 200);
    });
});

