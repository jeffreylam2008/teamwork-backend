<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/systems/payments',function(){
    /**
     * Payment GET Request
     * paymentmethod-get
     * 
     * To get all payment record 
     */
    $this->get('/methods/', function (Request $request, Response $response, array $args) {
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_err = [];
        $_data = [];
        $_msg = "";
        $_result = true;
        
        $this->logger->addInfo("Entry: paymentmethod: get all paymentmethod");
        $pdo = new Database();
		$db = $pdo->connect_db();
        
        $this->logger->addInfo("Msg: DB connected");
        $sql = "SELECT * FROM `t_payment_method`;";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $_data = $q->fetchAll(PDO::FETCH_ASSOC);
        //var_dump($result);
        // disconnect DB
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
            $_callback['error']['message'] = "Data fetch OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {
            $_callback['query'] = "";
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Data fetch Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * Payment GET Request
     * paymentmethod-get
     * 
     * To get all payment record 
     */
    $this->get('/methods/{code}', function (Request $request, Response $response, array $args) {
        $_pmcode = $args['code'];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_err = [];
        $_data = [];
        $_msg = "";
        $_result = true;

        $this->logger->addInfo("Entry: paymentmethod: get paymentmethod by pmcode");        
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = "SELECT * FROM `t_payment_method` WHERE `pm_code` = '".$_pmcode."';";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() != 0)
        {
            $_data = $q->fetch();
        }
        else
        {
            $_result = false;
        }
        // disconnect DB
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
            $_callback['error']['message'] = "Data fetch OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {
            $_callback['query'] = "";
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Data fetch Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * Payment method POST Request
     * paymentmethod-post
     * 
     * To insert new record
     */
     $this->post('/methods/', function(Request $request, Response $response, array $args){
        $err = [];
        $pdo = new Database();
        $db = $pdo->connect_db();
        // POST Data here
        $body = json_decode($request->getBody(), true);
        $_now = date('Y-m-d H:i:s');
        $db->beginTransaction();
        // $q = $db->prepare("insert into t_items_category (`cate_code`, `desc`, `create_date`) values ('".$body['i-catecode']."', '".$body['i-desc']."', '".$_now."');");
        $q = $db->prepare("insert into t_payment_method (`pm_code`, `payment_method`, `create_date`) values ('".$body['i-pm-code']."', '".$body['i-pm']."', '".$_now."');");
        $q->execute();
        // no fatch on insert
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
	 * Payment Method PATCH Request
	 * Paymentmethod-patch
	 * 
	 * To update current record on DB
	 */
	$this->patch('/methods/{code}', function(Request $request, Response $response, array $args){
		$err = [];
		$_pm_code = $args['code'];
		$pdo = new Database();
		$db = $pdo->connect_db();
		// POST Data here
		$body = json_decode($request->getBody(), true);
        $_now = date('Y-m-d H:i:s');
        // transaction began
		$db->beginTransaction();
		$q = $db->prepare("UPDATE `t_payment_method` SET `payment_method` = '".$body["i-payment-method"]."', `modify_date` = '".$_now."' WHERE `pm_code` = '".$_pm_code."';");
		$q->execute();
		// no fatch on update 
        $err = $q->errorinfo();
        // commit transaction
		$db->commit();
		// disconnect DB
		$pdo->disconnect_db();
		
		$callback = [
			"query" => "",
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
     $this->get('/terms/', function (Request $request, Response $response, array $args) {
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_err = [];
        $_data = [];
        $_msg = "";
        $_result = true;
        $this->logger->addInfo("Entry: paymentterms: get all paymentterms");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        $sql = "SELECT * FROM `t_payment_term`;";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $_data = $q->fetchAll(PDO::FETCH_ASSOC);
        // disconnect DB
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
            $_callback['error']['message'] = "Data fetch OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {
            $_callback['query'] = "";
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Data fetch Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * Payment term GET Request
     * Payment term-get
     * 
     * To get all payment record 
     */
    $this->get('/terms/{code}', function (Request $request, Response $response, array $args) {
        $_ptcode = $args['code'];
        $pdo = new Database();
		$db = $pdo->connect_db();
        $sql = "SELECT * FROM `t_payment_term` WHERE `pt_code` = '".$_ptcode."';";
        $q = $db->prepare($sql);
        $q->execute();
        $err = $q->errorinfo();
        $result = $q->fetch();
        //var_dump($result);

        // disconnect DB
        $pdo->disconnect_db();
        
        $callback = [
            "query" => $result,
            "error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
        ];
        return $response->withJson($callback,200);
    });
    
    /**
     * Payment terms POST Request
     * paymentterms-post
     * 
     * To insert new record
     */
     $this->post('/terms/', function(Request $request, Response $response, array $args){
        $err = [];
        $pdo = new Database();
        $db = $pdo->connect_db();
        // POST Data here
        $body = json_decode($request->getBody(), true);
        $_now = date('Y-m-d H:i:s');
        $db->beginTransaction();
        // $q = $db->prepare("insert into t_items_category (`cate_code`, `desc`, `create_date`) values ('".$body['i-catecode']."', '".$body['i-desc']."', '".$_now."');");
        $q = $db->prepare("insert into t_payment_term (`pt_code`, `terms`, `create_date`) values ('".$body['i-pt-code']."', '".$body['i-pt']."', '".$_now."');");
        $q->execute();
        // no fatch on insert
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
	 * Payment terms PATCH Request
	 * Paymentterms-patch
	 * 
	 * To update current record on DB
	 */
	$this->patch('/terms/{code}', function(Request $request, Response $response, array $args){
		$err = [];
		$_pt_code = $args['code'];
		$pdo = new Database();
		$db = $pdo->connect_db();
		// POST Data here
		$body = json_decode($request->getBody(), true);
        $_now = date('Y-m-d H:i:s');
        // transaction began
		$db->beginTransaction();
		$q = $db->prepare("UPDATE `t_payment_term` SET `terms` = '".$body["i-payment-term"]."', `modify_date` = '".$_now."' WHERE `pt_code` = '".$_pt_code."';");
		$q->execute();
		// no fatch on update
        $err = $q->errorinfo();
        // commit transaction
		$db->commit();
		// disconnect DB
		$pdo->disconnect_db();
		
		$callback = [
			"query" => "",
			"error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
		];
		return $response->withJson($callback,200);
    });

    /**
     * View of Items
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

            $this->logger->addInfo("Entry: paymentmethod: get header");
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