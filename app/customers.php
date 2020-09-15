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
        $err1 = [];
        $_data = "";
        $err[0] = "";
        $err[1] = "";
        $dbData = [];
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
        tpt.terms,
        taci.company_BR,
        taci.company_sign,
        taci.group_name,
        taci.attn,
        taci.tel,
        taci.fax,
        taci.email
        FROM `t_customers` as tc 
        LEFT JOIN `t_district` as td ON tc.district_code = td.district_code 
        LEFT JOIN `t_employee` as te ON tc.employee_code = te.employee_code 
        LEFT JOIN `t_payment_method` as tpm ON tc.pm_code = tpm.pm_code
        LEFT JOIN `t_payment_term` as tpt ON tc.pt_code = tpt.pt_code
        LEFT JOIN `t_accounts_info` as taci ON tc.cust_code = taci.cust_code
        ");
        $q->execute();
        $err1 = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($res))
        {
            foreach ($res as $key => $val) {
                $dbData[] = $val;
            }
        }
        if($err1[0] == "00000")
        {
            $err[0] = $err1[0];
            $err[1] = "Success!<br>DB: " .$err1[1]. " ".$err1[2];
            $_data = $dbData;
        } 
        else
        {
            $err[0] = $err1[0];
            $err[1] = "Error<br>DB: " .$err1[1]. " ".$err1[2];
        }

        $callback = [
            "query" => $_data, 
            "error" => ["code" => $err[0], "message" => $err[1]]
        ];

        return $response->withJson($callback, 200);
    
    });

    /**
     * Customer GET Request
     * customer-get-by-code
     * 
     * To get single record based on the customer code
     */
    $app->get('/{cust_code}', function(Request $request, Response $response, array $args){
        $_cust_code = $args['cust_code'];
        $err1 = [];
        $_data = "";
        $err[0] = "";
        $err[1] = "";
        $pdo = new Database();
		$db = $pdo->connect_db();
        $q = $db->prepare("
        SELECT tc.*, 
        (SELECT `cust_code` FROM `t_customers` WHERE `cust_code` < '".$_cust_code."' ORDER BY `cust_code` DESC LIMIT 1) as `previous`,
        (SELECT `cust_code` FROM `t_customers` WHERE `cust_code` > '".$_cust_code."' ORDER BY `cust_code` LIMIT 1) as `next`,
        td.district_chi,
        td.district_eng,
        td.region,
        te.username,
        te.default_shopcode,
        tpm.payment_method,
        tpt.terms,
        taci.company_BR,
        taci.company_sign,
        taci.group_name,
        taci.attn,
        taci.tel,
        taci.fax,
        taci.email
        FROM `t_customers` as tc 
        LEFT JOIN `t_district` as td ON tc.district_code = td.district_code 
        LEFT JOIN `t_employee` as te ON tc.employee_code = te.employee_code 
        LEFT JOIN `t_payment_method` as tpm ON tc.pm_code = tpm.pm_code
        LEFT JOIN `t_payment_term` as tpt ON tc.pt_code = tpt.pt_code
        LEFT JOIN `t_accounts_info` as taci ON tc.cust_code = taci.cust_code
        WHERE tc.cust_code = '".$_cust_code."';
        ");
        $q->execute();
        $err1 = $q->errorinfo();
        $res = $q->fetch();
        //var_dump($res);
        if(!empty($res))
        {
            if($err1[0] == "00000")
            {
                $err[0] = $err1[0];
                $err[1] = "Success! DB: " .$err1[1]. " ".$err1[2];
                $_data = $res;
            } 
            else
            {
                $err[0] = $err1[0];
                $err[1] = "Error! DB: " .$err1[1]. " ".$err1[2];
            }

            $callback = [
                "query" => $_data, 
                "error" => ["code" => $err[0], "message" => $err[1]]
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
        $err1 = [];
        $err2 = [];
        $err3 = [];
        $_data = "";
        $err[0] = "";
        $err[1] = "";
		$pdo = new Database();
		$db = $pdo->connect_db();
		// POST Data here
        $body = json_decode($request->getBody(), true);
        if(empty($body["i-from_time"])) $body["i-from_time"] = "00:00";
        if(empty($body["i-to_time"])) $body["i-to_time"] = "00:00";
        $_now = date('Y-m-d H:i:s');
        
        //var_dump($body);

        $db->beginTransaction();
        $q1 = $db->prepare("SELECT `cust_code` FROM `t_customers` ORDER BY `cust_code` DESC LIMIT 1;");
        $q1->execute();
        $err1 = $q1->errorinfo();
        if($err1[0] == "00000")
        {
            $res = $q1->fetch();
            if(empty($res['cust_code']))
            {
                $res['cust_code'] = "00";
            }
            $cust_code = substr($res['cust_code'],1);
            $cust_code = (int) $cust_code + 1;
            $cust_code = str_pad($cust_code, 5, "0", STR_PAD_LEFT);
            $cust_code = "C".$cust_code;
            $q2 = $db->prepare("
                INSERT INTO `t_customers` (
                    `cust_code`,
                    `status`, `name`, `attn_1`, `attn_2`,
                    `mail_addr`, `shop_addr`, `email_1`, `email_2`,
                    `phone_1`, `fax_1`, `statement_remark`, `remark`,
                    `pm_code`, `pt_code`, `district_code`, `delivery_addr`,
                    `from_time`, `to_time`, `phone_2`, `fax_2`, 
                    `delivery_remark`, `create_date`
                ) VALUES (
                    '".$cust_code."',
                    '".$body["i-status"]."',
                    '".$body["i-name"]."',
                    '".$body["i-attn_1"]."',
                    '".$body["i-attn_2"]."',
                    '".$body["i-mail_addr"]."',
                    '".$body["i-shop_addr"]."',
                    '".$body["i-email_1"]."',
                    '".$body["i-email_2"]."',
                    '".$body["i-phone_1"]."',
                    '".$body["i-fax_1"]."',
                    '".$body["i-statement_remark"]."',
                    '".$body["i-remark"]."',
                    '".$body["i-pm_code"]."',
                    '".$body["i-pt_code"]."',
                    '".$body["i-district"]."',
                    '".$body["i-delivery_addr"]."',
                    '".$body["i-from_time"]."',
                    '".$body["i-to_time"]."',
                    '".$body["i-delivery_phone"]."',
                    '".$body["i-delivery_fax"]."',
                    '".$body["i-delivery_remark"]."',	
                    '".$_now."'
                );
            ");
            $q2->execute();
            
            $q3= $db->prepare("
                INSERT INTO `t_accounts_info` (
                    `cust_code`,`company_br`,`company_sign`,`group_name`, `email`,
                    `attn`, `tel`, `fax`, `create_date`
                ) VALUES (
                    '".$cust_code."',
                    '".$body["i-acc_company_br"]."',
                    '".$body["i-acc_company_sign"]."',
                    '".$body["i-acc_group_name"]."',
                    '".$body["i-acc_email"]."',
                    '".$body["i-acc_attn"]."',
                    '".$body["i-acc_phone"]."',
                    '".$body["i-acc_fax"]."',
                    '".$_now."'
                );
                
            ");
            $q3->execute();
            
            // catch error here 
            $err2 = $q2->errorinfo();
            $err3 = $q3->errorinfo();
        }


        $db->commit();
        // disconnect DB
        $pdo->disconnect_db();
        if($err2[0] == "00000" && $err3[0] == "00000")
        {
            $err[0] = $err2[0];
            $err[1] = "Customer Code: ".$cust_code. " created!";
            $_data = ["cust_code" => $cust_code];
        } 
        else
        {
            $err[0] = $err2[0];
            $err[1] = "Error! DB: ".$err2[1]." ".$err2[2] ." " .$err3[1]." ".$err3[2] ;
            $_data = "";
        }
		$callback = [
			"query" => $_data, 
			"error" => ["code" => $err[0], "message" => $err[1]]
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
        $err1 = [];
        $err2 = [];
        $err[0] = "";
        $err[1] = "";
        $_data = "";
		$_cust_code = $args['cust_code'];
		$pdo = new Database();
		$db = $pdo->connect_db();
		// POST Data here
        $body = json_decode($request->getBody(), true);
        if(empty($body["i-from_time"])) $body["i-from_time"] = "00:00";
        if(empty($body["i-to_time"])) $body["i-to_time"] = "00:00";
        //var_dump($body);
        $_now = date('Y-m-d H:i:s');
    
		$db->beginTransaction();
		$q1 = $db->prepare("
            UPDATE `t_customers` SET 
            `status` = '".$body["i-status"]."',
            `name` = '".$body["i-name"]."',
            `attn_1` = '".$body["i-attn_1"]."',
            `attn_2` = '".$body["i-attn_2"]."',
            `mail_addr` = '".$body["i-mail_addr"]."',
            `shop_addr` = '".$body["i-shop_addr"]."',
            `email_1` = '".$body["i-email_1"]."',
            `email_2` = '".$body["i-email_2"]."',
            `phone_1` = '".$body["i-phone_1"]."',
            `fax_1` = '".$body["i-fax_1"]."',
            `statement_remark` = '".$body["i-statement_remark"]."',
            `remark` = '".$body["i-remark"]."',
            `pm_code` = '".$body["i-pm_code"]."',
            `pt_code` = '".$body["i-pt_code"]."',
            `district_code` = '".$body["i-district"]."',
            `delivery_addr` = '".$body["i-delivery_addr"]."',
            `from_time` = '".$body["i-from_time"]."',
            `to_time` = '".$body["i-to_time"]."',
            `phone_2` = '".$body["i-delivery_phone"]."',
            `fax_2` = '".$body["i-delivery_fax"]."',
            `delivery_remark` = '".$body["i-delivery_remark"]."',
            `modify_date` = '".$_now."'
            WHERE `cust_code` = '".$_cust_code."';
        ");
        $q1->execute();
        
        $q2 = $db->prepare("
            UPDATE `t_accounts_info` SET 
            `company_br` = '".$body["i-acc_company_br"]."',
            `company_sign` = '".$body["i-acc_company_sign"]."',
            `group_name` = '".$body["i-acc_group_name"]."',
            `attn` = '".$body["i-acc_attn"]."',
            `tel` = '".$body["i-acc_phone"]."',
            `fax` = '".$body["i-acc_fax"]."',
            `email` = '".$body["i-acc_email"]."',
            `modify_date` = '".$_now."'
            WHERE `cust_code` = '".$_cust_code."';
        ");
        $q2->execute();

		// no fatch on update 
        $err1 = $q1->errorinfo();
        $err2 = $q2->errorinfo();
        $db->commit();
    
        // disconnect DB
        $pdo->disconnect_db();

        if($err1[0] == "00000" && $err2[0] == "00000")
        {
            $err[0] = $err1[0];
            $err[1] = "Update Completed! DB: " .$err1[1]. " ".$err1[2] ." ".$err2[1]. " ".$err2[2] ;
        } 
        else
        {
            $err[0] = $err1[0]. " " .$err2[0];
            $err[1] = "Error! DB: " .$err1[1]. " ".$err1[2] ." ".$err2[1]. " ".$err2[2] ;
        }

        $callback = [
			"query" => $_data, 
			"error" => ["code" => $err[0], "message" => $err[1]]
        ];
        
		return $response->withJson($callback, 200);
    });
    
    /**
     * Customers DELETE Request
	 * Customers-delete
	 * 
     * To delete customer record by customer code
     */
    $app->delete('/{cust_code}', function(Request $request, Response $response, array $args){
        $err1 = [];
        $err2 = [];
        $err[0] = "";
        $err[1] = "";
        $_has = 0;        
        $_data = "";
    
        $_cust_code = $args['cust_code'];
		$pdo = new Database();
        $db = $pdo->connect_db();
       
        $q1 = $db->prepare("
            DELETE FROM `t_customers` WHERE `cust_code` = '".$_cust_code."';
        ");
        $q1->execute();
        $err1 = $q1->errorinfo();
        
        $q2 = $db->prepare("
            DELETE FROM `t_accounts_info` WHERE `cust_code` = '".$_cust_code."';
        ");
        $q2->execute();
        $err2 = $q2->errorinfo();

        // SQL Query success
        if($err1[0] == "00000")
        {
            $err[0] = "00000";
            $err[1] = "Customer code (".$_cust_code.") delete successful!";
        }
        else
        {
            $err[0] = "90002";
            $err[1] = "Wrong Customer code input";
        }
        // disconnect DB
        $pdo->disconnect_db();

        // gethering the data to send it back to client
        $callback = [
			"query" => $_data, 
			"error" => ["code" => $err[0], "message" => $err[1]]
        ];
        return $response->withJson($callback, 200);
    });

    /**
     * Customer GET Request
     * Customer-get-by-code
     * 
     * To verify current code record is exist
     */
    $this->get('/has/customer/{cust_code}', function(Request $request, Response $response, array $args){
        $err1 = [];
        $err2 = [];
        $err[0] = "";
        $err[1] = "";
        $_has = 0;        
        $_data = "";
    
        $_cust_code = $args['cust_code'];
		$pdo = new Database();
        $db = $pdo->connect_db();
        
        $db->beginTransaction();
        $q1 = $db->prepare("
            SELECT Count(*) as `has`, `trans_code` FROM `t_transaction_h`
            WHERE `cust_code` = '".$_cust_code."' AND `prefix` = 'INV';
        ");
        $q1->execute();
        $_has = $q1->fetch();
        $err1 = $q1->errorinfo();

        // SQL Query success
        if($err1[0] == "00000")
        {
            if($_has === 1)
            {
                $err[0] = "90000";
                $err[1] = "Customer code already exist on Transaction (".$_has['trans_code'].") cannot be delete!";
            }
        }
        
        $db->commit();
        // disconnect DB
        $pdo->disconnect_db();

        // gethering the data to send it back to client
        $callback = [
			"query" => $_data, 
			"error" => ["code" => $err[0], "message" => $err[1]]
        ];
        return $response->withJson($callback, 200);
    });
});