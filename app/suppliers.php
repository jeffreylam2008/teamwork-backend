<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/suppliers', function () use($app) {
    /**
     * Supplier GET Request
     * Supplier-get
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
        $q = $db->prepare("SELECT * FROM `t_suppliers`");
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
     * Supplier GET Request
     * Supplier-get-by-code
     * 
     * To get single record based on the Supplier code
     */
    $app->get('/{supp_code}', function(Request $request, Response $response, array $args){
        $_supp_code = $args['supp_code'];
        $err1 = [];
        $_data = "";
        $err[0] = "";
        $err[1] = "";
        $pdo = new Database();
		$db = $pdo->connect_db();
        $q = $db->prepare("
        SELECT ts.*,
        (SELECT `supp_code` FROM `t_suppliers` WHERE `supp_code` < '".$_supp_code."' ORDER BY `supp_code` DESC LIMIT 1) as `previous`,
        (SELECT `supp_code` FROM `t_suppliers` WHERE `supp_code` > '".$_supp_code."' ORDER BY `supp_code` LIMIT 1) as `next`,
        tpm.payment_method,
        tpt.terms
        FROM `t_suppliers` as ts 
        LEFT JOIN `t_payment_method` as tpm ON ts.pm_code = tpm.pm_code
        LEFT JOIN `t_payment_term` as tpt ON ts.pt_code = tpt.pt_code
        WHERE ts.supp_code = '".$_supp_code."';
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
     * Supplier POST Request
     * Supplier-post
     * 
     * To create new record on Supplier table 
     */
    $app->post("/", function(Request $request, Response $response, array $args){
        $err1 = [];
        $err2 = [];
        $_data = "";
        $err[0] = "";
        $err[1] = "";
		$pdo = new Database();
		$db = $pdo->connect_db();
		// POST Data here
		$body = json_decode($request->getBody(), true);
        $_now = date('Y-m-d H:i:s');
        
        //var_dump($body);

        $db->beginTransaction();
        $q1 = $db->prepare("
            SELECT `supp_code` 
            FROM `t_suppliers` 
            ORDER BY `supp_code` 
            DESC LIMIT 1;
        ");
        $q1->execute();
        $err1 = $q1->errorinfo();
        if($err1[0] == "00000")
        {
            $res = $q1->fetch();
            if(empty($res['supp_code']))
            {
                $res['supp_code'] = "00";
            }
            $supp_code = substr($res['supp_code'],1);
            $supp_code = (int) $supp_code + 1;
            $supp_code = str_pad($supp_code, 5, "0", STR_PAD_LEFT);
            $supp_code = "S".$supp_code;
            $q2 = $db->prepare("
                INSERT INTO `t_suppliers` (
                    `supp_code`,
                    `mail_addr`, `attn_1`, `phone_1`, `fax_1`, `email_1`,
                    `name`, `pm_code`, `pt_code`, `remark`,
                    `status`, `create_date`
                ) VALUES (
                    '".$supp_code."',
                    '".$body["i-mail_addr"]."',
                    '".$body["i-attn_1"]."',
                    '".$body["i-phone_1"]."',
                    '".$body["i-fax_1"]."',
                    '".$body["i-email_1"]."',
                    '".$body["i-name"]."',
                    '".$body["i-pm_code"]."',
                    '".$body["i-pt_code"]."',
                    '".$body["i-remark"]."',
                    '".$body["i-status"]."',
                    '".$_now."'
                );
            ");
            $q2->execute();
            // catch error here 
            $err2 = $q2->errorinfo();
        }
        $db->commit();
        // disconnect DB
        $pdo->disconnect_db();
        if($err2[0] == "00000")
        {
            $err[0] = $err2[0];
            $err[1] = "Customer Code: ".$supp_code. " created!";
            $_data = ["cust_code" => $supp_code];
        } 
        else
        {
            $err[0] = $err2[0];
            $err[1] = "Error! DB: ".$err2[1] . " " . $err2[2];
            $_data = "";
        }
		$callback = [
			"query" => $_data, 
			"error" => ["code" => $err[0], "message" => $err[1]]
		];
		return $response->withJson($callback, 200);
    });

    /**
	 * Supplier PATCH Request
	 * Supplier-patch
	 * 
	 * To update current record on DB
	 */
	$app->patch('/{supp_code}', function(Request $request, Response $response, array $args){
        $err1 = [];
        $err[0] = "";
        $err[1] = "";
        $_data = "";
		$_supp_code = $args['supp_code'];
		$pdo = new Database();
		$db = $pdo->connect_db();
		// POST Data here
        $body = json_decode($request->getBody(), true);
        //var_dump($body);
        $_now = date('Y-m-d H:i:s');
    
		$db->beginTransaction();
		$q1 = $db->prepare("
            UPDATE `t_suppliers` SET 
            `mail_addr` = '".$body["i-mail_addr"]."',
            `attn_1` = '".$body["i-attn_1"]."',
            `phone_1` = '".$body["i-phone_1"]."',
            `fax_1` = '".$body["i-fax_1"]."',
            `email_1` = '".$body["i-email_1"]."',
            `name` = '".$body["i-name"]."',
            `pm_code` = '".$body["i-pm_code"]."',
            `pt_code` = '".$body["i-pt_code"]."',
            `remark` = '".$body["i-remark"]."',
            `status` = '".$body["i-status"]."',
            `modify_date` = '".$_now."'
            WHERE `supp_code` = '".$_supp_code."';
        ");
        $q1->execute();

		// no fatch on update 
        $err1 = $q1->errorinfo();
        $db->commit();
    
        // disconnect DB
        $pdo->disconnect_db();

        if($err1[0] == "00000")
        {
            $err[0] = $err1[0];
            $err[1] = "Update Completed! DB: " .$err1[1]. " ".$err1[2];
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
    });
    
    /**
     * Supplier DELETE Request
	 * Supplier-delete
	 * 
     * To delete customer record by customer code
     */
    $app->delete('/{supp_code}', function(Request $request, Response $response, array $args){
        $err1 = [];
        $err[0] = "";
        $err[1] = "";
        $_has = 0;        
        $_data = "";
    
        $_supp_code = $args['supp_code'];
		$pdo = new Database();
        $db = $pdo->connect_db();
       
        $q1 = $db->prepare("
            DELETE FROM `t_suppliers` WHERE `supp_code` = '".$_supp_code."';
        ");
        $q1->execute();
        $err1 = $q1->errorinfo();

        // SQL Query success
        if($err1[0] == "00000")
        {
            $err[0] = "00000";
            $err[1] = "Suppliers: (".$_supp_code.") delete successful!";
        }
        else
        {
            $err[0] = "90002";
            $err[1] = "Wrong suppliers code input";
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
     * View of Suppliers
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