<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/inventory/invoices', function () {
    /**
     * Invoices GET request
     * invoices-get
     * 
     * To get all invoice record
     */
    $this->get('/', function (Request $request, Response $response, array $args) {

        $_param = $request->getQueryParams();


        $_callback = [];
        $_err = [];
        $_query = [];
        $_where_trans = "";
        $_where_date = "";
        $pdo = new Database();
        $db = $pdo->connect_db();

        // only if transaction field param exist
        if(!empty($_param['i-invoice-num']))
        {
            $_where_trans = "AND ( th.trans_code LIKE ('%".$_param['i-invoice-num']."%') ) ";
            
        }
        // otherwise follow date range as default
        else
        {
            $_where_date = "AND (date(th.create_date) BETWEEN '".$_param['i-start-date']."' AND '".$_param['i-end-date']."') ";
        }
        $sql = "
            SELECT 
                th.*,
                tpm.payment_method, 
                tc.name as `cust_name`, 
                ts.name as `shop_name`,
                ts.shop_code
            FROM `t_transaction_h` as th 
            LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code 
            LEFT JOIN `t_customers` as tc ON th.cust_code = tc.cust_code 
            LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code
            LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code
            WHERE th.is_void = 0 AND th.prefix = (SELECT prefix FROM t_prefix WHERE uid = 1) 
            ".$_where_date.$_where_trans.";
        ";
        // t_transaction_h SQL
        $q = $db->prepare($sql);

        $q->execute();
        $_err = $q->errorinfo();
        $_res = $q->fetchAll(PDO::FETCH_ASSOC);
    
        //export data

        foreach ($_res as $key => $val) {
            $_query[] = $val;
        }
        $_callback = [
            "query" => $_query,
            "error" => ["code" => $_err[0], "message" => $_err[1]." ".$_err[2]]
        ];

        // $_callback = "";
        return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
       
    });
    
    /**
     * Invoices GET request 
     * invoices-get-by-code
     * 
     * Get invoices by ID
     */
    $this->get('/{trans_code}', function (Request $request, Response $response, array $args) {
        // inital variable
        $_callback = [];
        $_query = [];
        $_callback['has'] = false;
        $_trans_code= $args['trans_code'];
        $_err = [];
        $_err2 = [];
        $_err3 = [];
        $_customers = [];
    
        $pdo = new Database();
		$db = $pdo->connect_db();
        $sql = "
            SELECT 
                th.trans_code as 'invoicenum',
                th.create_date as 'date',
                th.employee_code as 'employee_code',
                th.refer_code as 'refer_num',
                th.modify_date as 'modifydate',
                tt.pm_code as 'paymentmethod',
                tpm.payment_method as 'paymentmethodname',
                th.prefix as 'prefix',
                th.quotation_code as 'quotation',
                th.remark as 'remark',
                th.shop_code as 'shopcode',
                ts.name as 'shopname',
                th.cust_code as 'cust_code',
                tc.name as 'cust_name', 
                th.total as 'total',
                th.is_void as 'is_void'
            FROM `t_transaction_h` as th
            LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code
            LEFT JOIN `t_customers` as tc ON th.cust_code = tc.cust_code
            LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code
            LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code
            WHERE th.trans_code = '".$_trans_code."';
        ";
        $sql2 = "
            SELECT 
                td.item_code,
                td.eng_name,
                td.chi_name,
                td.qty,
                td.unit,
                td.price,
                td.discount as 'price_special',
                tw.qty as 'stockonhand'
            FROM `t_transaction_d` as td 
            LEFT JOIN `t_warehouse` as tw ON td.item_code = tw.item_code
            WHERE trans_code = '".$_trans_code."';
        ";
        // execute SQL Statement 1
        $q = $db->prepare($sql);
        $q->execute();
        $_err = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        // execute SQL statement 2
        $q = $db->prepare($sql2);
        $q->execute();
        $_err2 = $q->errorinfo();
        $res2 = $q->fetchAll(PDO::FETCH_ASSOC);
        
        //disconnection DB
        $pdo->disconnect_db();
    
        // export data
        if(!empty($res))
        {
            $_query = $res[0];        
            foreach ($res2 as $key => $val) {
                 $_query["items"] = $res2;
            }
            // calcuate subtotal
            if(!empty($_query["items"]))
            {
                foreach($_query["items"] as $k => $v)
                {
                    extract($v);
                    $_query["items"][$k]["subtotal"] = ($qty * $price);
                }
            }
            //var_dump($_query);
            $_callback['query'] = $_query;
            $_callback['has'] = true;
        }
        else
        {
            $_callback['query'] = $_query;
            $_callback['has'] = false;
        }
        $_callback["error"]["code"] = $_err[0];
        $_callback["error"]["message"] = $_err[2];
        return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
    });

    /**
     * Next invoice number
     * Invoice number generator
     * 
     * To gen next invoices number
     */
    $this->get('/getnextnum/', function (Request $request, Response $response, array $args) {
        $err[0] = "";
        $err[1] = "";
        $_data = "";
        $_max = "00";
        $pdo = new Database();
        $db = $pdo->connect_db();



        $q1 = $db->prepare("SELECT prefix FROM t_prefix WHERE uid = 1 LIMIT 1;");
        $q1->execute();
        $_err[] = $q1->errorinfo();
        if($q1->rowCount() != "0")
        {
            $_prefix = $q1->fetch();
        }
        
        $q = $db->prepare("
            SELECT MAX(trans_code) as max FROM `t_transaction_h` WHERE prefix = '".$_prefix['prefix']."' ORDER BY `create_date` DESC;
        ");
        $q->execute();
        $_err[] = $q->errorinfo();
        $_data = $q->fetch();
        
        if(!empty($_data['max']))
        {
            $_max = substr($_data['max'],-2);
            $_max++;
            if($_max >= 100)
            {
                $_max = 00;
            }
        }

        $pdo->disconnect_db();
        $_data = $_prefix['prefix'].date("ym").str_pad($_max, 2, 0, STR_PAD_LEFT);

        $callback = [
            "query" => $_data,
            "error" => [
                "code" => $err[0], 
                "message" => $err[1]
            ]
        ];

        return $response->withHeader('Connection', 'close')->withJson($callback, 200);
    });

    /**
     * Get Prefix
     * 
     * To get prefix
     */
    $this->get('/getprefix/', function (Request $request, Response $response, array $args) {
        $err[0] = "";
        $err[1] = "";
        $err[1] = "done!";
        $pdo = new Database();
        $db = $pdo->connect_db();
        $sql = "
            SELECT prefix FROM `t_prefix` WHERE `uid` = '1' LIMIT 1;
        ";
        
        $q = $db->prepare($sql);
        $q->execute();
        $_err = $q->errorinfo();
        if($q->rowCount() != "0")
        {
            $_res = $q->fetch();
        }
        $pdo->disconnect_db();
        $callback = [
            "query" => $_res['prefix'],
            "error" => [
                "code" => $err[0], 
                "message" => $err[1]
            ]
        ];
    
        return $response->withHeader('Connection', 'close')->withJson($callback, 200);
    });

    /**
     * GET Invoice information by items and customer code
     * To show information based on customer code and item code, show previous 5 records of invoices
     */
     $this->get('/getinfo/cust/{cust_code}/item/{item_code}', function (Request $request, Response $response, array $args) {
        $_callback = [];
        $_err = [];
        $_res = [];

        $cust_code = $args['cust_code'];
        $item_code = $args['item_code'];
        $pdo = new Database();
        $db = $pdo->connect_db();
        
        $sql = "
            SELECT 
                th.*,
                td.trans_code, 
                td.item_code, 
                td.eng_name, 
                td.chi_name, 
                td.unit, 
                td.price, 
                td.qty 
            FROM `t_transaction_h` as th 
            LEFT JOIN `t_transaction_d` as td
            ON th.trans_code = td.trans_code
            WHERE th.cust_code = '".$cust_code."' AND th.prefix = (SELECT prefix FROM t_prefix WHERE uid = 1) AND td.item_code = '".$item_code."' LIMIT 10;
        ";

        $q = $db->prepare($sql);
        $q->execute();
        $_err = $q->errorinfo();
        if($q->rowCount() != "0")
        {
            $_res = $q->fetchAll(PDO::FETCH_ASSOC);
        }
        $pdo->disconnect_db();

        $_callback['query'] = $_res;
        $_callback["error"]["code"] = $_err[0];
        $_callback["error"]["message"] = $_err[2];

        //disconnection DB
        
        return $response->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Connection', 'close')
        ->withJson($_callback, 200);
    });

    /**
     * Invoice GET Request
     * Invoice-find-latest-record
     * 
     * To get single invoice record by customer code while filter search
     */
    $this->get('/getlast/cust/{cust_code}', function (Request $request, Response $response, array $args) {
        $_callback = [];
        $_err = [];
        $_res = [];

        $cust_code = $args['cust_code'];
        $pdo = new Database();
        $db = $pdo->connect_db();
        
        $sql = "
            SELECT th.*, 
            tpm.payment_method, 
            tc.name as `cust_name`, 
            ts.name as `shop_name`, 
            ts.shop_code 
            FROM `t_transaction_h` as th 
            LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code 
            LEFT JOIN `t_customers` as tc ON th.cust_code = tc.cust_code 
            LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code 
            LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code
            WHERE th.cust_code = '".$cust_code."' AND th.prefix = (SELECT prefix FROM t_prefix WHERE uid = 1) ORDER BY `create_date` DESC;
        ";

        $q = $db->prepare($sql);
        $q->execute();
        $_err = $q->errorinfo();
        if($q->rowCount() != "0")
        {
            $_res = $q->fetchAll(PDO::FETCH_ASSOC);
        }
        
        //disconnection DB
        $pdo->disconnect_db();

        $_callback['query'] = $_res;
        $_callback["error"]["code"] = $_err[0];
        $_callback["error"]["message"] = $_err[2];
        return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
    });

    /** 
     * PATCH request
     * invoices-patch-by-code
     * @param body
     * 
     * to update input to database
     */
    
    $this->patch('/{trans_code}', function(Request $request, Response $response, array $args){
        $_err = [];
		//$_done = false;
        $_result = true;
        $_msg = "";
		$_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_now = date('Y-m-d H:i:s');
        $_new_res = [];
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        //$sql_d = "";
        // POST Data here
        $body = json_decode($request->getBody(), true);
        extract($body);
        $this->logger->addInfo("PATCH: invoices");
        // To convert money format to decimal
        $total = filter_var($total,FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        
        $_trans_code = $args['trans_code'];
        $sql = "SELECT * FROM `t_transaction_d` WHERE trans_code = '".$_trans_code."';";
        //$this->logger->addInfo("SQL: ".$sql);
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        
        if($_err[0][0] == "00000")
        {
            foreach($res as $k => $v)
            {
                $_new_res[$v['item_code']] = $v["item_code"];
            }
        }
        // after the new resource fill go next
        if(!empty($_new_res))
        {
            $db->beginTransaction();
            // transaction header
            
            $sql = "UPDATE `t_transaction_h` SET ";
            $sql .= " cust_code = '".$cust_code."',";
            $sql .= " quotation_code = '".$quotation."',";
            $sql .= " total = '".$total."',";
            $sql .= " employee_code = '".$employee_code."',";
            $sql .= " shop_code = '".$shopcode."',";
            $sql .= " remark = '".$remark."',";
            $sql .= " modify_date =  '".$_now."'";
            $sql .= " WHERE trans_code = '".$_trans_code."';";
            //$this->logger->addInfo("SQL: ".$sql);
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            
            if($q->rowCount() != "0")
            {
                foreach($_new_res as $k_itcode => $v)
                {
                    // delete items from this transaction
                    if(!array_key_exists($k_itcode,$items))
                    {

                        $sql = "DELETE FROM `t_transaction_d` WHERE trans_code = '".$_trans_code."' AND item_code = '".$k_itcode."';";
                        //$this->logger->addInfo("SQL: ".$sql);
                        $q = $db->prepare($sql);
                        $q->execute();
                        $_err[] = $q->errorinfo();
                    }
                }
                foreach($items as $k_itcode => $v)
                {
                    // update item already in transaction
                    if(array_key_exists($k_itcode,$_new_res))
                    {
                        $sql = "UPDATE `t_transaction_d` SET";
                        $sql .= " qty = '".$v['qty']."',";
                        $sql .= " unit = '".$v['unit']."',";
                        $sql .= " price = '".$v['price']."',";
                        $sql .= " modify_date = '".$_now."'";
                        $sql .= " WHERE trans_code = '".$_trans_code."' AND item_code = '".$k_itcode."';";
                        //$this->logger->addInfo("SQL: ".$sql);
                        $q = $db->prepare($sql);
                        $q->execute();
                        $_err[] = $q->errorinfo();
                    }
                    // New add items
                    else
                    {
                        $sql = "INSERT INTO t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, unit, price, discount, create_date)";
                        $sql .= " VALUES (";
                        $sql .= " '".$_trans_code."',";
                        $sql .= " '".$v['item_code']."',";
                        $sql .= " '".$v['eng_name']."' ,";
                        $sql .= " '".$v['chi_name']."' ,";
                        $sql .= " '".$v['qty']."',";
                        $sql .= " '".$v['unit']."',";
                        $sql .= " '".$v['price']."',";
                        $sql .= " '".$v['price_special']."',";
                        $sql .= " '".$date."'";
                        $sql .= " );";
                        //$this->logger->addInfo("SQL: ".$sql);
                        $q = $db->prepare($sql);
                        $q->execute();
                        $_err[] = $q->errorinfo();
                    }   
                }
                
                // tender information input here
                $sql ="UPDATE `t_transaction_t` SET";
                $sql .= " pm_code = '".$paymentmethod."',";
                $sql .= " total = '".$total."',";
                $sql .= " modify_date = '".$_now."'";
                $sql .= " WHERE trans_code = '".$_trans_code."';";
                //$this->logger->addInfo("SQL: ".$sql);
                $q = $db->prepare($sql);
                $q->execute();
                $_err[] = $q->errorinfo();
            }
            // $_done = true;
            $db->commit();
            $this->logger->addInfo("Msg: DB commit");
            //disconnection DB
            $pdo->disconnect_db();
            $this->logger->addInfo("Msg: DB connection closed");
        }

        // var_dump($_err);
        foreach($_err as $k => $v)
        {
            if($v[0] != "00000"){
                $_result = false;
                $_msg .= $v[1]."-".$v[2]."|";
            }
        }
        
        if($_result)
        {
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Transaction: ".$trans_code." - Insert OK!";
            $this->logger->addInfo("SQL execute ".$_callback['error']['message']);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Insert Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_callback['error']['message']);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * POST request
     * invoices-post
     * @param body
     * 
     * Add new record to DB
     */
    $this->post('/', function (Request $request, Response $response, array $args) {
        $_err = [];
        // $_done = false;
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        // POST Data here
        $body = json_decode($request->getBody(), true);
        extract($body);
        //$this->logger->addInfo("POST: invoices");
        // Start transaction 
        $db->beginTransaction();
        // insert record to transaction_h
        $sql = "INSERT INTO t_transaction_h (trans_code, cust_code ,quotation_code, prefix, total, employee_code, shop_code, remark, is_void, is_convert, create_date) ";
        $sql .= " VALUES (";
        $sql .= " '".$trans_code."',";
        $sql .= " '".$cust_code."',";
        $sql .= " '".$quotation."',";
        $sql .= " '".$prefix."',";
        $sql .= " '".$total."',";
        $sql .= " '".$employee_code."',";
        $sql .= " '".$shopcode."',";
        $sql .= " '".$remark."',";
        $sql .= " '0',";
        $sql .= " '0',";
        $sql .= " '".$date."'";
        $sql .= ");";
        //$this->logger->addInfo("SQL: ".$sql);
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        // insert record to transaction_d
        if(!empty($db->lastInsertId()))
        {
            foreach($items as $k => $v)
            {
                $sql = "INSERT INTO t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, unit, price, discount, create_date)";
                $sql .= " VALUES (";
                $sql .= " '".$trans_code."',";
                $sql .= " '".$v['item_code']."',";
                $sql .= " '".$v['eng_name']."' ,";
                $sql .= " '".$v['chi_name']."' ,";
                $sql .= " '".$v['qty']."',";
                $sql .= " '".$v['unit']."',";
                $sql .= " '".$v['price']."',";
                $sql .= " '".$v['price_special']."',";
                $sql .= " '".$date."'";
                $sql .= " );";
                //$this->logger->addInfo("SQL: ".$sql);
                $q = $db->prepare($sql);
                $q->execute();
            }
            $_err[] = $q->errorinfo();

            // tender information input here
            $sql = "INSERT INTO t_transaction_t (trans_code, pm_code, total, create_date) ";
            $sql .= " VALUES (";
            $sql .= " '".$trans_code."',";
            $sql .= " '".$paymentmethod."',";
            $sql .= " '".$total."',";
            $sql .= " '".$date."'";
            $sql .= " );";
            //$this->logger->addInfo("SQL: ".$sql);
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
        }

        // if has quotation
        if(!empty($quotation))
        {
            $sql = "UPDATE t_transaction_h SET";
            $sql .= " is_convert = 1, ";
            $sql .= " modify_date =  '".$date."'";
            $sql .= " WHERE trans_code = '".$quotation."';";
            // $this->logger->addInfo("SQL: ".$sql);
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
        }
        $db->commit();
        $this->logger->addInfo("Msg: DB commit");
        //disconnection DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");

        // var_dump($_err);
        foreach($_err as $k => $v)
        {
            if($v[0] != "00000"){
                $_result = false;
                $_msg .= $v[1]."-".$v[2]."|";
            }
        }
        if($_result)
        {
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Transaction: ".$trans_code." - Insert OK!";
            $this->logger->addInfo("SQL execute ".$_callback['error']['message']);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Insert Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_callback['error']['message']);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * Invoices Delete Request
     * Invoices-delete
     * 
     * To remove Invoices record based on invoices code
     */
    $this->delete('/{trans_code}', function(Request $request, Response $response, array $args){
        $_trans_code = $args['trans_code'];
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        $this->logger->addInfo("DELETE: invoices");
        // transaction start
        $db->beginTransaction();
        // sql statement
        $sql = "UPDATE `t_transaction_h` SET is_void = '1' WHERE trans_code = '".$_trans_code."';";
        // $this->logger->addInfo("SQL: ".$sql);
        // prepare sql statement
        $q = $db->prepare($sql);
        // execute statement
        $q->execute();
        $_err[] = $q->errorinfo();

        $sql = "UPDATE `t_transaction_h` SET is_void = '1' WHERE refer_code = '".$_trans_code."';";
        // $this->logger->addInfo("SQL: ".$sql);
        // prepare sql statement
        $q = $db->prepare($sql);
        // execute statement
        $q->execute();
        $_err[] = $q->errorinfo();

        $db->commit();
        $this->logger->addInfo("Msg: DB commit");
        //disconnection DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");


        foreach($_err as $k => $v)
        {
            if($v[0] != "00000"){
                $_result = false;
                $_msg .= $v[1]."-".$v[2]."|";
            }
        }
        if($_result)
        {
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = $_trans_code." - Deleted!";
            $this->logger->addInfo("SQL execute ".$_callback['error']['message']);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Delete Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_callback['error']['message']);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });
        
    /**
     * Check transaction_d item exist API
     */
    $this->group('/transaction/d',function(){
        /**
         * Transaction D GET Request
         * Trans-d-get-by-code
         * 
         * To check items on transaction d table (use it on delete items)
         */
        $this->get('/{item_code}', function (Request $request, Response $response, array $args) {
            $item_code = $args['item_code'];

            $pdo = new Database();
		    $db = $pdo->connect_db();
            $sql = "SELECT * FROM `t_transaction_d` where item_code = '". $item_code ."';";
        
            $q = $db->prepare($sql);
            $q->execute();
            $dbData = $q->fetch();
            $err = $q->errorinfo();
            //disconnection DB
            $pdo->disconnect_db();

            $callback = [
                "query" => $dbData,
                "error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
            ];
        
            return $response->withHeader('Connection', 'close')->withJson($callback, 200);
        });

        /**
         * Transaction D GET Request
         * Trans-d-get-by-code
         * 
         * To check items on transaction d table (use it on delete items)
         */
         $this->get('/trans_code/{trans_code}', function (Request $request, Response $response, array $args) {
            $_trans_code = $args['trans_code'];
            $pdo = new Database();
            $db = $pdo->connect_db();
            
            $sql = "SELECT * FROM `t_transaction_d` where trans_code = '". $_trans_code ."';";
        
            $q = $db->prepare($sql);
            $q->execute();
            $dbData = $q->fetchAll(PDO::FETCH_ASSOC);
            $err = $q->errorinfo();
            //disconnection DB
            $pdo->disconnect_db();

            $callback = [
                "query" => $dbData,
                "error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
            ];
        
            return $response->withHeader('Connection', 'close')->withJson($callback, 200);
        });
    });

     /**
     * Check transaction_d item exist API
     */
    $this->group('/transaction/h',function(){
        /**
         * Transaction H GET Request
         * Customer Check
         * To check customer code on transaction h table (use it on delete customer)
         * 
         * @param cust_code customer code to look up record
         */
        $this->get('/{prefix}/customers/{cust_code}', function (Request $request, Response $response, array $args) {
            $err[0] = "";
            $err[1] = "";
            $_data = [];
            $cust_code = $args['cust_code'];
            $prefix = $args['prefix'];
            $pdo = new Database();
            $db = $pdo->connect_db();

            $sql = "SELECT * FROM `t_transaction_h` where cust_code = '". $cust_code ."' AND prefix = '".$prefix."';";
            
            $q = $db->prepare($sql);
            $q->execute();
            $_data = $q->fetchAll(PDO::FETCH_ASSOC);
            $err = $q->errorinfo();
            //disconnection DB
            $pdo->disconnect_db();
            
            if(!$_data)
            {
                $_data = ["has" => false, "data"=> ""];
            }
            else
            {
                $_data = ["has" => true, "data"=> $_data];
            }

            $callback = [
                "query" => $_data,
                "error" => [
                    "code" => $err[0], 
                    "message" => $err[1]
                ]
            ];
        
            return $response->withHeader('Connection', 'close')->withJson($callback, 200);
        });
        
        /**
         * Transaction H GET Request
         * Supplier Check 
         * To check Supplier code on transaction h table (use it on delete customer)
         * 
         * @param prefix existing prefix 
         * @param supp_code supplier code to look up record
         */
        $this->get('/{prefix}/suppliers/{supp_code}', function (Request $request, Response $response, array $args) {
            $err[0] = "";
            $err[1] = "";
            $_data = [];
            $supp_code = $args['supp_code'];
            $prefix = $args['prefix'];
            $pdo = new Database();
            $db = $pdo->connect_db();

            $sql = "SELECT * FROM `t_transaction_h` where supp_code = '". $supp_code ."' AND prefix = '".$prefix."';";
            
            $q = $db->prepare($sql);
            $q->execute();
            $_data = $q->fetchAll(PDO::FETCH_ASSOC);
            $err = $q->errorinfo();
            //disconnection DB
            $pdo->disconnect_db();
            
            if(!$_data)
            {
                $_data = ["has" => false, "data"=> ""];
            }
            else
            {
                $_data = ["has" => true, "data"=> $_data];
            }

            $callback = [
                "query" => $_data,
                "error" => [
                    "code" => $err[0], 
                    "message" => $err[1]
                ]
            ];
        
            return $response->withHeader('Connection', 'close')->withJson($callback, 200);
        });

        /**
         * Transaction H GET Request
         * Count number of mothly invoice
         * @param queryparam start date
         * @param queryparam end date
         */
        $this->get('/count/', function (Request $request, Response $response, array $args) {
            //$_prefix = $args['prefix'];
            $_param = $request->getQueryParams();
            if(empty($_param["month"])) $_param["month"] = date('m');
            if(empty($_param["year"])) $_param["year"] = date('Y');

            $_callback = [];
            $_err = [];
            $_query = [];
            $pdo = new Database();
            $db = $pdo->connect_db();
            
            $q = $db->prepare("
            SELECT 
                count(*) as count,
                sum(total) as income
            FROM 
                `t_transaction_h` as th
            WHERE th.is_void = 0 AND th.prefix = (SELECT prefix FROM `t_prefix` WHERE `uid` = '1') AND month(th.create_date) = '".$_param['month']."'
            AND year(th.create_date) = '".$_param['year']."';
            ");

            $q->execute();
            $_err = $q->errorinfo();
            $_res = $q->fetch(PDO::FETCH_ASSOC);
        
            // export data

            // foreach ($_res as $key => $val) {
            //     $_query[] = $val;
            // }
            $_callback = [
                "query" => $_res,
                "error" => ["code" => $_err[0], "message" => $_err[1]." ".$_err[2]]
            ];
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
       
        });
    });

    /**
     * View of invoice
     */
    $this->group('/view',function()
    {
        $this->get('/header/username/{username}/', function (Request $request, Response $response, array $args) 
        {
            $_err= [];
            $_data['employee'] = [];
            $_data['menu'] = [];
            $_data['prefix'] = [];
            $_data['dn'] = ["dn_num"=>"", "dn_prefix"=>""];
            $_max = "00";
            $_username = "";
            $_param = $request->getQueryParams();
            $_username = $args['username'];
            $_result = true;
            $_msg = "";
            $pdo = new Database();
            $db = $pdo->connect_db();

            $sql1 = "
                SELECT 
                te.employee_code as employee_code,
                te.username as username,
                ts.name as shop_name, 
                ts.shop_code as shop_code
                FROM `t_employee` as te
                LEFT JOIN `t_shop` as ts
                ON te.default_shopcode = ts.shop_code where te.username = '".$_username."';
            ";
            // echo $sql1."\n";
            $q = $db->prepare($sql1);
            $q->execute();
            $_err[] = $q->errorinfo();
            $_data['employee'] = $q->fetch(PDO::FETCH_ASSOC);

            // SQL2
            switch($_param['lang'])
            {
                case "en-us":
                    $sql2 = "SELECT m_order as `order`, `id`, `parent_id`, lang2 as `name`, slug, `param` FROM `t_menu`;";
                    break;
                case "zh-hk":
                    $sql2 = "SELECT m_order as `order`, `id`, `parent_id`, lang1 as `name`, slug, `param` FROM `t_menu`;";
                    break;
                default:
                    $sql2 = "SELECT m_order as `order`, `id`, `parent_id`, lang2 as `name`, slug, `param` FROM `t_menu`;";
                    break;
            }
            //echo $sql2."\n";
            $q = $db->prepare($sql2);
            $q->execute();
            $_err[] = $q->errorinfo();
            $_data['menu'] = $q->fetchAll(PDO::FETCH_ASSOC);

            //SQL 3
            $sql3 = "
                SELECT prefix FROM `t_prefix` WHERE `uid` = '1' LIMIT 1;
            ";
            //echo $sql3."\n";
            $q = $db->prepare($sql3);
            $q->execute();
            $_err[] = $q->errorinfo();
            $_data['prefix'] = $q->fetch(PDO::FETCH_ASSOC);

            //SQL 4
            $sql4 = "
                SELECT prefix FROM `t_prefix` WHERE `uid` = '4' LIMIT 1;
            ";
            $q = $db->prepare($sql4);
            $q->execute();
            $_err[] = $q->errorinfo();
            if($q->rowCount() != "0")
            {
                $_prefix = $q->fetch();
                $_data['dn']['dn_prefix'] = $_prefix['prefix'];

                $sql5 = "
                    SELECT MAX(trans_code) as max FROM `t_transaction_h` WHERE prefix = '".$_prefix['prefix']."' ORDER BY `create_date` DESC;
                ";
                $sql5 = $db->prepare($sql5);
                $q->execute();
                $_err[] = $q->errorinfo();

                if(!empty($_data['max']))
                {
                    $_max = substr($_data['max'],-2);
                    $_max++;
                    if($_max >= 100)
                    {
                        $_max = 00;
                    }
                }
                $_data['dn']['dn_num'] = $_prefix['prefix'].date("ym").str_pad($_max, 2, 0, STR_PAD_LEFT);
            }

            //disconnection DB
            $pdo->disconnect_db();

            foreach($_err as $k => $v)
            {
                if($v[0] != "00000"){
                    $_result = false;
                    $_msg .= $v[1];
                }
            }
            $callback = [
                "query" => $_data,
                "error" => [
                    "code" => "00000", 
                    "message" => $_msg 
                ]
            ];
        
            if($_result)
            {
                return $response->withHeader('Connection', 'close')->withJson($callback, 200);
            }
            else
            {
                $callback = ["query" => ""];    
                return $response->withHeader('Connection', 'close')->withJson($callback, 404);
            }
           
        });
    });
});