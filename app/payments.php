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
        $pdo = new Database();
		$db = $pdo->connect_db();
        $sql = "SELECT * FROM `t_payment_method`; ";
        $q = $db->prepare($sql);
        $q->execute();
        $err = $q->errorinfo();
        $result = $q->fetchAll();
        //var_dump($result);
        $new = [];
        foreach($result as $k => $v)
        {
            extract($v);
            $new[] = $v;
        }
        $err = $q->errorinfo();
        // disconnect DB
        $pdo->disconnect_db();
        
        $callback = [
            "query" => $new,
            "error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
        ];
        return $response->withJson($callback,200);
    });

    /**
     * Payment GET Request
     * paymentmethod-get
     * 
     * To get all payment record 
     */
    $this->get('/methods/{code}', function (Request $request, Response $response, array $args) {
        $_pmcode = $args['code'];
        $pdo = new Database();
		$db = $pdo->connect_db();
        $sql = "SELECT * FROM `t_payment_method` WHERE `pm_code` = '".$_pmcode."';";
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
        $pdo = new Database();
		$db = $pdo->connect_db();
        $sql = "SELECT * FROM `t_payment_term`;";
        $q = $db->prepare($sql);
        $q->execute();
        $err = $q->errorinfo();
        $result = $q->fetchAll();
        // disconnect DB
        $pdo->disconnect_db();

        //var_dump($result);
        $new = [];
        foreach($result as $k => $v)
        {
            extract($v);
            $new[] = $v;
        }
        $err = $q->errorinfo();
        $callback = [
            "query" => $new,
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


});