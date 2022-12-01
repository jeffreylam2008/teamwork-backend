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
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_err = [];
        $_data = [];
        $_msg = "";
        $_result = true;
        $_where_trans = "";
        $_where_date = "";
        if(!empty($_param))
        {
            $this->logger->addInfo("Entry: invoices: get all invoices");
            $pdo = new Database();
            $db = $pdo->connect_db();
            $this->logger->addInfo("Msg: DB connected");

            // only if transaction field param exist
            if(!empty($_param['i-invoice-num']))
            {
                $_where_trans = " AND ( th.trans_code LIKE ('%".$_param['i-invoice-num']."%') ) ";
            }
            // otherwise follow date range as default
            if(!empty($_param['i-start-date']) && !empty($_param['i-end-date']) )
            {
                $_where_date = " AND (date(th.create_date) BETWEEN '".$_param['i-start-date']."' AND '".$_param['i-end-date']."') ";
            }
            $sql = "SELECT";
            $sql .= " th.*,";
            $sql .= " tpm.payment_method,"; 
            $sql .= " tc.name as `cust_name`,"; 
            $sql .= " ts.name as `shop_name`,";
            $sql .= " ts.shop_code";
            $sql .= " FROM `t_transaction_h` as th";
            $sql .= " LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code";
            $sql .= " LEFT JOIN `t_customers` as tc ON th.cust_code = tc.cust_code"; 
            $sql .= " LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code";
            $sql .= " LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code";
            $sql .= " WHERE th.is_void = 0 AND th.prefix = (SELECT prefix FROM t_prefix WHERE uid = 1)";
            $sql .= $_where_date.$_where_trans.";";
            // t_transaction_h SQL
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $_data = $q->fetchAll(PDO::FETCH_ASSOC);
        
            //export data
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
        }
        else
        {
            $_callback = [
                "error" => ["code" => "99999", "message" => "Query String Not Found!"]
            ];
            return $response->withJson($_callback, 404);
        }
    });
    
    /**
     * Invoices GET request 
     * invoices-get-by-code
     * 
     * Get invoices by ID
     */
    $this->get('/{trans_code}', function (Request $request, Response $response, array $args) {
        // inital variable
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_trans_code= $args['trans_code'];
    
        $this->logger->addInfo("Entry: invoices: get invoices by trans_code");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = "SELECT";
        $sql .= " th.trans_code as 'invoice_num',";
        $sql .= " th.create_date as 'date',";
        $sql .= " th.employee_code as 'employee_code',";
        $sql .= " th.refer_code as 'refer_num',";
        $sql .= " th.modify_date as 'modifydate',";
        $sql .= " tt.pm_code as 'paymentmethod',";
        $sql .= " tpm.payment_method as 'paymentmethodname',";
        $sql .= " th.prefix as 'prefix',";
        $sql .= " th.quotation_code as 'quotation',";
        $sql .= " th.remark as 'remark',";
        $sql .= " th.shop_code as 'shopcode',";
        $sql .= " ts.name as 'shopname',";
        $sql .= " th.cust_code as 'cust_code',";
        $sql .= " tc.name as 'cust_name',"; 
        $sql .= " th.total as 'total',";
        $sql .= " th.is_void as 'is_void'";
        $sql .= " FROM `t_transaction_h` as th";
        $sql .= " LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code";
        $sql .= " LEFT JOIN `t_customers` as tc ON th.cust_code = tc.cust_code";
        $sql .= " LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code";
        $sql .= " LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code";
        $sql .= " WHERE th.trans_code = '".$_trans_code."';";
        //$this->logger->addInfo("SQL: ".$sql);
        // execute SQL Statement 1
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        if($q->rowCount() != 0)
        {
            $sql = "SELECT";
            $sql .= " td.item_code,";
            $sql .= " td.eng_name,";
            $sql .= " td.chi_name,";
            $sql .= " td.qty,";
            $sql .= " td.unit,";
            $sql .= " td.price,";
            $sql .= " td.discount as 'price_special',";
            $sql .= " tw.qty as 'stockonhand'";
            $sql .= " FROM `t_transaction_d` as td ";
            $sql .= " LEFT JOIN `t_warehouse` as tw ON td.item_code = tw.item_code";
            $sql .= " WHERE trans_code = '".$_trans_code."';";
            // execute SQL statement 2
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $res2 = $q->fetchAll(PDO::FETCH_ASSOC);
            
            //disconnection DB
            $pdo->disconnect_db();
            $this->logger->addInfo("Msg: DB connection closed");
            // export data

            $_data = $res[0];        
            foreach ($res2 as $key => $val) {
                $_data["items"] = $res2;
            }
            // calcuate subtotal
            if(!empty($_data["items"]))
            {
                foreach($_data["items"] as $k => $v)
                {
                    extract($v);
                    $_data["items"][$k]["subtotal"] = ($qty * $price);
                }
            }
        }
        else
        {
            $_result = false;
        }
        //var_dump($_data);
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
            $_callback['has'] = true;
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Data fetch OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['query'] = "";
            $_callback['has'] = false;
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Data fetch Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * Next invoice number
     * Invoice number generator
     * 
     * To gen next invoices number
     */
    $this->get('/getnextnum/{session_id}', function (Request $request, Response $response, array $args) {
        $_session_id = $args['session_id'];
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_prefix = "";
        

        $this->logger->addInfo("Entry: invoices: getnextnum");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        if(empty($_session_id))
        {
            $_session_id = "";
        }

        $sql = "SELECT prefix FROM t_prefix WHERE uid = 1 LIMIT 1";
        // $this->logger->addInfo("SQL = ".$sql);
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() > 0)
        {
            $_prefix = $q->fetch(PDO::FETCH_ASSOC);
        }

        // Get last number from transaction nummber generator
        $sql = "SELECT `last`, `prefix`, `suffix`, `session_id` FROM `t_trans_num_generator` WHERE `prefix` in (SELECT prefix FROM t_prefix WHERE uid = 1)  ORDER BY `create_date` DESC LIMIT 1";
        // $this->logger->addInfo("SQL = ".$sql);
        $q1 = $db->prepare($sql);
        $q1->execute();
        $_err[] = $q1->errorinfo();
        $_data = $q1->fetch(PDO::FETCH_ASSOC);
        
        // define variable
        $prefix = $_prefix['prefix'];
        $suffix = date("ym");

        if(empty($_data['last']))
        {
            $_last = 0;
            $last = $_last + 1;
            $insert = true;

        }
        // session_id is different then give a new one
        elseif( strcmp($_data['session_id'],$_session_id) != 0 )
        {
            // reset counter to zero
            if($_data['last'] >= 199){
                $_data['last'] = 0;
            }
            $last = $_data['last'] + 1;
            $insert = true;

        }
        // remain same id
        else
        {
            $prefix = $_data['prefix'];
            $suffix = $_data['suffix'];
            $last = $_data['last'];
            $insert = false;
        }

        $last = str_pad($last,3,0,STR_PAD_LEFT);
        if($insert){
            $sql = "INSERT INTO `t_trans_num_generator` (`prefix`, `suffix`, `last`, `session_id`, `create_date`, `expiry_date`)  VALUES(  '".$prefix."', '".$suffix."', '".$last."', '".$_session_id."', '".date('Y-m-d H:i:s')."', null);";
            // $this->logger->addInfo("SQL = ".$sql);
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
        }

        $_data = $prefix.$suffix.$last;

        // disconnect DB session
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
     * Get Prefix
     * 
     * To get prefix
     */
    $this->get('/getprefix/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];

        $this->logger->addInfo("Entry: invoices: getprefix");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = "SELECT prefix FROM `t_prefix` WHERE `uid` = '1' LIMIT 1;";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() != 0)
        {
            $_data = $q->fetch(PDO::FETCH_ASSOC);
        }
        
        // disconnect DB session
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
            $_callback['query'] = $_data['prefix'];
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
     * GET Invoice information by items and customer code
     * To show information based on customer code and item code, show previous 5 records of invoices\
     * 
     * @param cust_code commandline arguments from user input
     * @param item_code commandline arguments from user input
     */
     $this->get('/getinfo/cust/{cust_code}/item/{item_code}', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_cust_code = $args['cust_code'];
        $_item_code = $args['item_code'];

        $this->logger->addInfo("Entry: invoices: get customer information for search");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        $sql = "SELECT";
        $sql .= " th.*,";
        $sql .= " td.trans_code,"; 
        $sql .= " td.item_code,"; 
        $sql .= " td.eng_name,"; 
        $sql .= " td.chi_name,"; 
        $sql .= " td.unit,"; 
        $sql .= " td.price,"; 
        $sql .= " td.qty";
        $sql .= " FROM `t_transaction_h` as th";
        $sql .= " LEFT JOIN `t_transaction_d` as td";
        $sql .= " ON th.trans_code = td.trans_code";
        $sql .= " WHERE th.cust_code = '".$_cust_code."' AND th.prefix = (SELECT prefix FROM t_prefix WHERE uid = 1) AND td.item_code = '".$_item_code."' LIMIT 10;";

        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() != 0)
        {
            $_data = $q->fetchAll(PDO::FETCH_ASSOC);
        }
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
            $_callback['error']['message'] = "Data fetch OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Connection', 'close')
            ->withJson($_callback, 200);
        }
        else
        {  
            $_callback['query'] = "";
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Data fetch Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Connection', 'close')
            ->withJson($_callback, 404);
        }
    });

    /**
     * Invoice GET Request
     * Invoice-find-latest-record
     * 
     * To get single invoice record by customer code while filter search
     * 
     * @param cust_code command line argument required from user
     */
    $this->get('/getlast/cust/{cust_code}', function (Request $request, Response $response, array $args) {
        $_result = true;
        $_msg = "";
		$_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_err = [];
        $_data = [];
        $_cust_code = $args['cust_code'];

        $this->logger->addInfo("Entry: invoices: get last customer info for search");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        
        $sql = "SELECT th.*,";
        $sql .= " tpm.payment_method,"; 
        $sql .= " tc.name as `cust_name`,"; 
        $sql .= " ts.name as `shop_name`,"; 
        $sql .= " ts.shop_code"; 
        $sql .= " FROM `t_transaction_h` as th"; 
        $sql .= " LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code"; 
        $sql .= " LEFT JOIN `t_customers` as tc ON th.cust_code = tc.cust_code"; 
        $sql .= " LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code"; 
        $sql .= " LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code";
        $sql .= " WHERE th.cust_code = '".$_cust_code."' AND th.prefix = (SELECT prefix FROM t_prefix WHERE uid = 1) ORDER BY `create_date` DESC;";

        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() != 0)
        {
            $_data = $q->fetchAll(PDO::FETCH_ASSOC);
        }

        //disconnection DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");

        foreach($_err as $k => $v)
        {
            // has error
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
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Retrieve Data Problem!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /** 
     * PATCH request
     * invoices-patch-by-code
     * @param trans_code to identify which transaction to amand
     * @param body request body content
     * 
     * to update input to database
     */
    
    $this->patch('/{trans_code}', function(Request $request, Response $response, array $args){
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_now = date('Y-m-d H:i:s');
        $_new_res = [];
        $_trans_code = $args['trans_code'];

        $this->logger->addInfo("Entry: PATCH: invoices");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        //$sql_d = "";
        // POST Data here
        $body = json_decode($request->getBody(), true);
        extract($body);
        
        // To convert money format to decimal
        //$total = filter_var($total,FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        
        $this->logger->addInfo("Debug: total".$total);
        
        $sql = "SELECT * FROM `t_transaction_d` WHERE trans_code = '".$_trans_code."';";
        //$this->logger->addInfo("SQL: ".$sql);
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        
        if($q->rowCount() != 0)
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
            
            if($q->rowCount() != 0)
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
            $_callback['error']['message'] = "Transaction: ".$_trans_code." - Update OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Insert Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * POST request
     * invoices-post
     * @param body request body content
     * 
     * Add new record to DB
     */
    $this->post('/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";

        $this->logger->addInfo("Entry: POST: invoices");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        // POST Data here
        $body = json_decode($request->getBody(), true);
        extract($body);
        // To convert money format to decimal
        $total = filter_var($total,FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
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
        // $this->logger->addInfo("SQL: ".$sql);
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
                $_err[] = $q->errorinfo();
            }
            

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
        }

        $db->commit();
        $this->logger->addInfo("Msg: DB commit");
        //disconnection DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");

        // var_dump($_err);
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
            $_callback['error']['message'] = "Transaction: ".$trans_code." - Insert OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Insert Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * Invoices Delete Request
     * Invoices-delete
     * @param trans_code requied transaction number
     * To remove Invoices record based on invoices code
     */
    $this->delete('/{trans_code}', function(Request $request, Response $response, array $args){
        $_trans_code = $args['trans_code'];
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $this->logger->addInfo("Entry: DELETE: invoices");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

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
            $_callback['error']['message'] = "Transaction: ".$_trans_code." - Deleted!";
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
     * Check transaction_d item exist API
     */
    $this->group('/transaction/d',function(){
        /**
         * Transaction D GET Request
         * Trans-d-get-by-code
         * @param item_code required item code for idenifcation
         * 
         * To check items on transaction d table (use it on delete items)
         */
        $this->get('/{item_code}', function (Request $request, Response $response, array $args) {
            $_item_code = $args['item_code'];
            $_err = [];
            $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
            $_result = true;
            $_msg = "";
            $_data = [];

            $this->logger->addInfo("Entry: invoices: check transaction_d by item_code");
            $pdo = new Database();
		    $db = $pdo->connect_db();
            $this->logger->addInfo("Msg: DB connected");

            $sql = "SELECT * FROM `t_transaction_d` where item_code = '". $_item_code ."';";
            $q = $db->prepare($sql);
            $q->execute();
            $_data = $q->fetch();
            $_err[] = $q->errorinfo();
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
         * Transaction D GET Request
         * Trans-d-get-by-code
         * @param trans_code transaction code
         * 
         * To check items on transaction d table (use it on delete items)
         */
         $this->get('/trans_code/{trans_code}', function (Request $request, Response $response, array $args) {
            $_trans_code = $args['trans_code'];
            $_err = [];
            $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
            $_result = true;
            $_msg = "";
            $_data = [];

            $this->logger->addInfo("Entry: invoices: check transaction_d by tran_code");
            $pdo = new Database();
            $db = $pdo->connect_db();
            $this->logger->addInfo("Msg: DB connected");

            $sql = "SELECT * FROM `t_transaction_d` where trans_code = '". $_trans_code ."';";
            $q = $db->prepare($sql);
            $q->execute();
            $_data = $q->fetchAll(PDO::FETCH_ASSOC);
            $_err[] = $q->errorinfo();
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
            $_err = [];
            $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
            $_result = true;
            $_msg = "";
            $_data = [];
            $_cust_code = $args['cust_code'];
            $_prefix = $args['prefix'];

            $this->logger->addInfo("Entry: invoices: check custommer by cust_code has transaction_h exist");
            $pdo = new Database();
            $db = $pdo->connect_db();
            $this->logger->addInfo("Msg: DB connected");

            $sql = "SELECT * FROM `t_transaction_h` where cust_code = '". $_cust_code ."' AND prefix = '".$_prefix."';";
            $q = $db->prepare($sql);
            $q->execute();
            $_data = $q->fetchAll(PDO::FETCH_ASSOC);
            $_err[] = $q->errorinfo();
            //disconnection DB
            $pdo->disconnect_db();
            $this->logger->addInfo("Msg: DB connection closed");
            
            if(!$_data)
            {
                $_data = ["has" => false, "data"=> ""];
            }
            else
            {
                $_data = ["has" => true, "data"=> $_data];
            }

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
         * Transaction H GET Request
         * Count number of mothly invoice
         * @param queryparam start date
         * @param queryparam end date
         */
        $this->get('/count/', function (Request $request, Response $response, array $args) {
            //$_prefix = $args['prefix'];
            $_err = [];
            $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
            $_result = true;
            $_msg = "";
            $_data = [];
            $_param = $request->getQueryParams();
            if(empty($_param["month"])) $_param["month"] = date('m');
            if(empty($_param["year"])) $_param["year"] = date('Y');

            $this->logger->addInfo("Entry: invoices: get count");
            $pdo = new Database();
            $db = $pdo->connect_db();
            $this->logger->addInfo("Msg: DB connected");
            $sql = "SELECT";
            $sql .= " count(*) as count,";
            $sql .= " sum(total) as income";
            $sql .= " FROM `t_transaction_h` as th";
            $sql .= " WHERE th.is_void = 0 AND th.prefix = (SELECT prefix FROM `t_prefix` WHERE `uid` = '1') AND month(th.create_date) = '".$_param['month']."'";
            $sql .= " AND year(th.create_date) = '".$_param['year']."';";
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $_data = $q->fetch(PDO::FETCH_ASSOC);

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
    });

    /**
     * View of invoice
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
            
            //SQL1
            $this->logger->addInfo("Entry: invoices: get header");
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
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $_data['employee'] = $q->fetch(PDO::FETCH_ASSOC);

            //SQL2
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
            $sql = "SELECT prefix FROM `t_prefix` WHERE `uid` = '1' LIMIT 1;";
            //echo $sql3."\n";
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $_data['prefix'] = $q->fetch(PDO::FETCH_ASSOC);

            //SQL 4
            $sql = "SELECT prefix FROM `t_prefix` WHERE `uid` = '4' LIMIT 1;";
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            if($q->rowCount() != 0)
            {
                $_prefix = $q->fetch();
                $_data['dn']['dn_prefix'] = $_prefix['prefix'];

                $sql = "SELECT MAX(trans_code) as max FROM `t_transaction_h` WHERE prefix = '".$_prefix['prefix']."' ORDER BY `create_date` DESC;";
                $q = $db->prepare($sql);
                $q->execute();
                $_err[] = $q->errorinfo();
                $res = $q->fetch();

                if(!empty($res['max']))
                {
                    $_max = substr($res['max'],-2);
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