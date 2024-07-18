<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/customers', function () use($app) {
    /**
     * Customer GET Request
     * customer-get
     * done
     * To get all customer record
     */
    $this->get('/', function(Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = "";

        $this->logger->addInfo("Entry: GET: Customers");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = "SELECT tc.*, ";
        $sql .= "td.district_chi, "; 
        $sql .= "td.district_eng, ";
        $sql .= "td.region, ";
        $sql .= "te.username, ";
        $sql .= "te.default_shopcode, ";
        $sql .= "tpm.payment_method, ";
        $sql .= "tpt.terms, ";
        $sql .= "taci.company_BR, ";
        $sql .= "taci.company_sign, ";
        $sql .= "taci.group_name, ";
        $sql .= "taci.attn, ";
        $sql .= "taci.tel, ";
        $sql .= "taci.fax, ";
        $sql .= "taci.email ";
        $sql .= "FROM `t_customers` as tc "; 
        $sql .= "LEFT JOIN `t_district` as td ON tc.district_code = td.district_code "; 
        $sql .= "LEFT JOIN `t_employee` as te ON tc.employee_code = te.employee_code ";
        $sql .= "LEFT JOIN `t_payment_method` as tpm ON tc.pm_code = tpm.pm_code ";
        $sql .= "LEFT JOIN `t_payment_term` as tpt ON tc.pt_code = tpt.pt_code ";
        $sql .= "LEFT JOIN `t_accounts_info` as taci ON tc.cust_code = taci.cust_code ";
        

        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $_data = $q->fetchAll(PDO::FETCH_ASSOC);
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
            $_callback['query'] = $_data;
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Successful: Query done!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $this->actionLogger->addInfo("Msg: GET:Customers:run SQL query has problem");
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Update Fail: Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * Customer GET Request
     * customer-get-by-code
     * done
     * To get single record based on the customer code
     */
    $this->get('/{cust_code}', function(Request $request, Response $response, array $args){
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = "";
        $_cust_code = $args['cust_code'];

        $this->logger->addInfo("Entry: GET: Customer with cust_code");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = "SELECT tc.*, ";
        $sql .= "(SELECT `cust_code` FROM `t_customers` WHERE `cust_code` < '".$_cust_code."' ORDER BY `cust_code` DESC LIMIT 1) as `previous`, ";
        $sql .= "(SELECT `cust_code` FROM `t_customers` WHERE `cust_code` > '".$_cust_code."' ORDER BY `cust_code` LIMIT 1) as `next`, ";
        $sql .= "td.district_chi, ";
        $sql .= "td.district_eng, ";
        $sql .= "td.region, ";
        $sql .= "te.username, ";
        $sql .= "te.default_shopcode, ";
        $sql .= "tpm.payment_method, ";
        $sql .= "tpt.terms, ";
        $sql .= "taci.company_BR, ";
        $sql .= "taci.company_sign, ";
        $sql .= "taci.group_name, ";
        $sql .= "taci.attn, ";
        $sql .= "taci.tel, ";
        $sql .= "taci.fax, ";
        $sql .= "taci.email ";
        $sql .= "FROM `t_customers` as tc "; 
        $sql .= "LEFT JOIN `t_district` as td ON tc.district_code = td.district_code "; 
        $sql .= "LEFT JOIN `t_employee` as te ON tc.employee_code = te.employee_code "; 
        $sql .= "LEFT JOIN `t_payment_method` as tpm ON tc.pm_code = tpm.pm_code ";
        $sql .= "LEFT JOIN `t_payment_term` as tpt ON tc.pt_code = tpt.pt_code ";
        $sql .= "LEFT JOIN `t_accounts_info` as taci ON tc.cust_code = taci.cust_code ";
        $sql .= "WHERE tc.cust_code = '".$_cust_code."'; ";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $_data = $q->fetch();

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
            $_callback['query'] = $_data;
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Successful: (".$_cust_code.") Found!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $this->actionLogger->addInfo("Msg: GET:Customer/wcust_code:run SQL query has problem");
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Update Fail: Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    $this->get('/count/',function(Request $request, Response $response, array $args){
        $err1 = [];
        $err[0] = "";
        $err[1] = "";

        $pdo = new Database();
		$db = $pdo->connect_db();
        $q = $db->prepare("
            SELECT count(*) as count 
            FROM `t_customers` as tc WHERE STATUS = 'Active';
        ");
        $q->execute();
        $err1 = $q->errorinfo();
        $res = $q->fetch(PDO::FETCH_ASSOC);

        if($err1[0] == "00000")
        {
            $err[0] = $err1[0];
            $err[1] = "Success!<br>DB: " .$err1[1]. " ".$err1[2];
        } 
        else
        {
            $err[0] = $err1[0];
            $err[1] = "Error<br>DB: " .$err1[1]. " ".$err1[2];
        }

        $callback = [
            "query" => $res, 
            "error" => ["code" => $err[0], "message" => $err[1]]
        ];

        return $response->withJson($callback, 200);
    
    });

    /**
     * Customer POST Request
     * customer-post
	 * done
     * To create new record on customer table 
     */
    $this->post("/", function(Request $request, Response $response, array $args){
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = "";
        $_cust_code = "";

        $this->logger->addInfo("Entry: POST: Customers");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $this->actionLogger->addInfo("Msg: POST:Customers:".$request->getBody());
		// POST Data here
        $body = json_decode($request->getBody(), true);
        if(empty($body["i-from_time"])) $body["i-from_time"] = "00:00";
        if(empty($body["i-to_time"])) $body["i-to_time"] = "00:00";
        $_now = date('Y-m-d H:i:s');
        
        //var_dump($body);

        $db->beginTransaction();
        $q1 = $db->prepare("SELECT `cust_code` FROM `t_customers` ORDER BY `cust_code` DESC LIMIT 1;");
        $q1->execute();
        $_err[] = $q1->errorinfo();
        if($_err[0][0] == "00000")
        {
            $res = $q1->fetch();
            if(empty($res['cust_code']))
            {
                $res['cust_code'] = "00";
            }
            $_cust_code = substr($res['cust_code'],1);
            $_cust_code = (int) $_cust_code + 1;
            $_cust_code = str_pad($_cust_code, 5, "0", STR_PAD_LEFT);
            $_cust_code = "C".$_cust_code;
        }
        if($_cust_code != "" || !empty($_cust_code)){
            $sql = "INSERT INTO `t_customers` (";
            $sql .= "`cust_code`,";
            $sql .= "`status`, `name`, `attn_1`, `attn_2`,";
            $sql .= "`mail_addr`, `shop_addr`, `email_1`, `email_2`,";
            $sql .= "`phone_1`, `fax_1`, `statement_remark`, `remark`,";
            $sql .= "`pm_code`, `pt_code`, `district_code`, `delivery_addr`,";
            $sql .= "`from_time`, `to_time`, `phone_2`, `fax_2`, ";
            $sql .= "`delivery_remark`, `create_date`";
            $sql .= ") VALUES (";
            $sql .= "'".$_cust_code."',";
            $sql .= "'".$body["i-status"]."',";
            $sql .= "'".$body["i-name"]."',";
            $sql .= "'".$body["i-attn_1"]."',";
            $sql .= "'".$body["i-attn_2"]."',";
            $sql .= "'".htmlspecialchars($body["i-mail_addr"], ENT_QUOTES)."',";
            $sql .= "'".htmlspecialchars($body["i-shop_addr"], ENT_QUOTES)."',";
            $sql .= "'".htmlspecialchars($body["i-email_1"], ENT_QUOTES)."',";
            $sql .= "'".htmlspecialchars($body["i-email_2"], ENT_QUOTES)."',";
            $sql .= "'".$body["i-phone_1"]."',";
            $sql .= "'".$body["i-fax_1"]."',";
            $sql .= "'".htmlspecialchars($body["i-statement_remark"], ENT_QUOTES)."',";
            $sql .= "'".htmlspecialchars($body["i-remark"], ENT_QUOTES)."',";
            $sql .= "'".$body["i-pm_code"]."',";
            $sql .= "'".$body["i-pt_code"]."',";
            $sql .= "'".$body["i-district"]."',";
            $sql .= "'".htmlspecialchars($body["i-delivery_addr"], ENT_QUOTES)."',";
            $sql .= "'".$body["i-from_time"]."',";
            $sql .= "'".$body["i-to_time"]."',";
            $sql .= "'".$body["i-delivery_phone"]."',";
            $sql .= "'".$body["i-delivery_fax"]."',";
            $sql .= "'".htmlspecialchars($body["i-delivery_remark"], ENT_QUOTES)."',";
            $sql .= "'".$_now."'";
            $sql .=  ");";
        //$this->actionLogger->addInfo("SQL: ".$sql);
            $q2 = $db->prepare($sql);
            $q2->execute();
            $_err[] = $q2->errorinfo();
            
            $sql = "INSERT INTO `t_accounts_info` ( ";
            $sql .= " `cust_code`,`company_br`,`company_sign`,`group_name`, `email`, ";
            $sql .= " `attn`, `tel`, `fax`, `create_date` ";
            $sql .= " ) VALUES ( ";
            $sql .= " '".$_cust_code."', ";
            $sql .= " '".$body["i-acc_company_br"]."', ";
            $sql .= " '".$body["i-acc_company_sign"]."', ";
            $sql .= " '".$body["i-acc_group_name"]."', ";
            $sql .= " '".$body["i-acc_email"]."', ";
            $sql .= " '".$body["i-acc_attn"]."', ";
            $sql .= " '".$body["i-acc_phone"]."', ";
            $sql .= " '".$body["i-acc_fax"]."', ";
            $sql .= " '".$_now."' ";
            $sql .= " ); ";
        //$this->actionLogger->addInfo("SQL: ".$sql);
            $q3= $db->prepare($sql);
            $q3->execute();
            $_err[] = $q3->errorinfo();
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
        if($_result && !empty($_cust_code))
        {
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Customer: (".$_cust_code.") - ".$body["i-name"]." Created!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $this->actionLogger->addInfo("Msg: POST:Customers:_cust_code not found or run SQL query has problem");
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Update Fail: Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
	 * Customers PATCH Request
	 * Customers-patch
	 * done
	 * To update current record on DB
	 */
	$this->patch('/{cust_code}', function(Request $request, Response $response, array $args){
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
		$_cust_code = $args['cust_code'];

        $this->logger->addInfo("Entry: PATCH: Customers");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        $this->actionLogger->addInfo("Msg: PATCH:Customers:".$request->getBody());
		// POST Data here
        $body = json_decode($request->getBody(), true);
        if(empty($body["i-from_time"])) $body["i-from_time"] = "00:00";
        if(empty($body["i-to_time"])) $body["i-to_time"] = "00:00";
        //var_dump($body);
        $_now = date('Y-m-d H:i:s');
    
		$db->beginTransaction();
		$sql = "UPDATE `t_customers` SET ";
        $sql .= "`status` = '".$body["i-status"]."', ";
        $sql .= "`name` = '".$body["i-name"]."', ";
        $sql .= "`attn_1` = '".$body["i-attn_1"]."', ";
        $sql .= "`attn_2` = '".$body["i-attn_2"]."', ";
        $sql .= "`mail_addr` = '".htmlspecialchars($body["i-mail_addr"], ENT_QUOTES)."', ";
        $sql .= "`shop_addr` = '".htmlspecialchars($body["i-shop_addr"], ENT_QUOTES)."', ";
        $sql .= "`email_1` = '".htmlspecialchars($body["i-email_1"], ENT_QUOTES)."', ";
        $sql .= "`email_2` = '".htmlspecialchars($body["i-email_2"], ENT_QUOTES)."', ";
        $sql .= "`phone_1` = '".$body["i-phone_1"]."', ";
        $sql .= "`fax_1` = '".$body["i-fax_1"]."', ";
        $sql .= "`statement_remark` = '".htmlspecialchars($body["i-statement_remark"], ENT_QUOTES)."', ";
        $sql .= "`remark` = '".htmlspecialchars($body["i-remark"], ENT_QUOTES)."', ";
        $sql .= "`pm_code` = '".$body["i-pm_code"]."', ";
        $sql .= "`pt_code` = '".$body["i-pt_code"]."', ";
        $sql .= "`district_code` = '".$body["i-district"]."', ";
        $sql .= "`delivery_addr` = '".htmlspecialchars($body["i-delivery_addr"], ENT_QUOTES)."', ";
        $sql .= "`from_time` = '".$body["i-from_time"]."', ";
        $sql .= "`to_time` = '".$body["i-to_time"]."', ";
        $sql .= "`phone_2` = '".$body["i-delivery_phone"]."', ";
        $sql .= "`fax_2` = '".$body["i-delivery_fax"]."', ";
        $sql .= "`delivery_remark` = '".htmlspecialchars($body["i-delivery_remark"], ENT_QUOTES)."', ";
        $sql .= "`modify_date` = '".$_now."' ";
        $sql .= "WHERE `cust_code` = '".$_cust_code."';";
        $q1 = $db->prepare($sql);
        $q1->execute();
        $_err[] = $q1->errorinfo();
        // $this->logger->addInfo("SQL: ".$sql);

        
        $sql = "UPDATE `t_accounts_info` SET ";
        $sql .= "`company_br` = '".$body["i-acc_company_br"]."', ";
        $sql .= "`company_sign` = '".$body["i-acc_company_sign"]."', ";
        $sql .= "`group_name` = '".$body["i-acc_group_name"]."', ";
        $sql .= "`attn` = '".$body["i-acc_attn"]."', ";
        $sql .= "`tel` = '".$body["i-acc_phone"]."', ";
        $sql .= "`fax` = '".$body["i-acc_fax"]."', ";
        $sql .= "`email` = '".$body["i-acc_email"]."', ";
        $sql .= "`modify_date` = '".$_now."' ";
        $sql .= "WHERE `cust_code` = '".$_cust_code."';";
        
        // $this->logger->addInfo("SQL: ".$sql);
        $q2 = $db->prepare($sql);
        $q2->execute();
        $_err[] = $q2->errorinfo();

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
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Customer: (".$_cust_code.") - ".$body["i-name"]." updated!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $this->actionLogger->addInfo("Msg: PATCH:Customers:run SQL query has problem");
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Update Fail: Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });
    
    /**
     * Customers DELETE Request
	 * Customers-delete
	 * 
     * To delete customer record by customer code
     */
    $this->delete('/{cust_code}', function(Request $request, Response $response, array $args){
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_cust_code = $args['cust_code'];

        $this->logger->addInfo("Entry: PATCH: Customers");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        $this->actionLogger->addInfo("Msg: DELETE:Customer:".$_cust_code);
       
        $sql = "DELETE FROM `t_customers` WHERE `cust_code` = '".$_cust_code."';";
        $q1 = $db->prepare($sql);
        $q1->execute();
        $_err[] = $q1->errorinfo();
        
        $sql = "DELETE FROM `t_accounts_info` WHERE `cust_code` = '".$_cust_code."';";
        $q2 = $db->prepare($sql);
        $q2->execute();
        $_err[] = $q2->errorinfo();

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
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Customer: (".$_cust_code.") - Deleted!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Delete Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
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
        $sql = "SELECT Count(*) as `has`, `trans_code` FROM `t_transaction_h` ";
        $sql .="WHERE `cust_code` = '".$_cust_code."' AND `prefix` = 'INV';";
        $q1 = $db->prepare();
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

    /**
     * View of Customer
     */
    $this->group('/view',function()
    {
        $this->get('/header/username/{username}/', function (Request $request, Response $response, array $args) 
        {
            $_err = [];
            $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
            $_result = true;
            $_msg = "";
            $_data['employee'] = [];
            $_data['menu'] = [];
            $_data['prefix'] = [];
            $_data['dn'] = ["dn_num"=>"", "dn_prefix"=>""];
            $_max = "00";
            $_param = $request->getQueryParams();
            $_username = $args['username'];
            $_result = true;
            $_msg = "";

            $this->logger->addInfo("Entry: items: get header");
            $pdo = new Database();
            $db = $pdo->connect_db();
            $this->logger->addInfo("Msg: DB connected");
            $sql = "SELECT ";
            $sql .= " te.employee_code as employee_code,";
            $sql .= " te.username as username,";
            $sql .= " ts.name as shop_name,";
            $sql .= " ts.shop_code as shop_code";
            $sql .= " FROM `t_employee` as te";
            $sql .= " LEFT JOIN `t_shop` as ts";
            $sql .= " ON te.default_shopcode = ts.shop_code where te.username = '".$_username."';";
            // echo $sql."\n";
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $_data['employee'] = $q->fetch(PDO::FETCH_ASSOC);

            // SQL2
            switch($_param['lang'])
            {
                case "en-us":
                    $sql = "SELECT m_order as `order`, `id`, `parent_id`, lang2 as `name`, slug, `param` FROM `t_menu`;";
                    break;
                case "zh-hk":
                    $sql = "SELECT m_order as `order`, `id`, `parent_id`, lang1 as `name`, slug, `param` FROM `t_menu`;";
                    break;
                default:
                    $sql = "SELECT m_order as `order`, `id`, `parent_id`, lang2 as `name`, slug, `param` FROM `t_menu`;";
                    break;
            }
            //echo $sql2."\n";
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $_data['menu'] = $q->fetchAll(PDO::FETCH_ASSOC);

            //SQL 3
            $sql = "SELECT prefix FROM `t_prefix` WHERE `uid` = '3' LIMIT 1;";
            //echo $sql3."\n";
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $_data['prefix'] = $q->fetch(PDO::FETCH_ASSOC);

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
                $_callback['query'] = $_data;
                $_callback['error']['code'] = "00000";
                $_callback['error']['message'] = "Header data fetch OK!";
                $this->logger->addInfo("SQL execute ".$_msg);
                return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
            }
            else
            {  
                $_callback['query'] = "";
                $_callback['error']['code'] = "99999";
                $_callback['error']['message'] = "Header data fetch Fail - Please try again!";
                $this->logger->addInfo("SQL execute ".$_msg);
                return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
            }
            
        });
    });
});