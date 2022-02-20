<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/inventory/quotations', function () {
    /**
     * Quotations GET Request
     * quotations-get
     * 
     * To get all quotations record
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

        $this->logger->addInfo("Entry: quotations: get all quotations");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        // only if transaction field param exist
        if(!empty($_param['i-quotation-num']))
        {
            $_where_trans = " AND ( th.trans_code LIKE ('%".$_param['i-quotation-num']."%') ) ";
        }
        // otherwise follow date range as default
        else
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
        $sql .= " WHERE th.is_void = 0 AND th.prefix = (SELECT prefix FROM t_prefix WHERE uid = 3)";
        $sql .= $_where_date.$_where_trans.";";
        $this->logger->addInfo("SQL: ".$sql);
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $_data = $q->fetchAll(PDO::FETCH_ASSOC);
    
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");

        // export data
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
     * Quotation GET Request
     * quotations-get-by-code
     * 
     * To get single quotations record
     */
    $this->get('/{trans_code}', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_trans_code= $args['trans_code'];

        $this->logger->addInfo("Entry: quotations: get quotations by trans_code");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = "SELECT th.trans_code as 'quotation',";
        $sql .= " th.create_date as 'date',";
        $sql .= " th.employee_code as 'employee_code',";
        $sql .= " th.modify_date as 'modifydate',";
        $sql .= " tt.pm_code as 'paymentmethod',";
        $sql .= " tpm.payment_method as 'paymentmethodname',";
        $sql .= " th.prefix as 'prefix',";
        $sql .= " th.remark as 'remark',";
        $sql .= " th.shop_code as 'shopcode',";
        $sql .= " ts.name as 'shopname',";
        $sql .= " th.cust_code as 'cust_code',";
        $sql .= " tc.name as 'cust_name', ";
        $sql .= " th.is_convert as 'is_convert', ";
        $sql .= " th.total as 'total'";
        $sql .= " FROM `t_transaction_h` as th";
        $sql .= " LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code";
        $sql .= " LEFT JOIN `t_customers` as tc ON th.cust_code = tc.cust_code";
        $sql .= " LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code";
        $sql .= " LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code";
        $sql .= " WHERE th.trans_code = '".$_trans_code."';";
        
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
            $sql .= " WHERE td.trans_code = '".$_trans_code."';";

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
     * Quotation GET Request
     * quotations-find-latest-record
     * 
     * To get single quotations record
     * @param cust_code commandline arguments from user input
     */
    $this->get('/getlast/cust/{cust_code}', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_cust_code = $args['cust_code'];

        $this->logger->addInfo("Entry: quotations: get quotations information for search");
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
        $sql .= " WHERE th.cust_code = '".$_cust_code."' AND th.prefix = 'QTA' ORDER BY `create_date` DESC;";

        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() != 0)
        {
            $_data = $q->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            $_data = [];
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
            $_callback['error']['message'] = "OK!";
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
     * Quotation GET Request
     * quotations-find-item-record
     * 
     * @param cust_code commandline arguments from user input
     */
    $this->get('/getinfo/cust/{cust_code}', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_cust_code = $args['cust_code'];

        $this->logger->addInfo("Entry: quotations: get customer information for search");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        $sql = " SELECT";
        $sql .= " th.prefix,";
        $sql .= " td.trans_code,";
        $sql .= " th.create_date,";
        $sql .= " td.item_code,";
        $sql .= " td.eng_name,";
        $sql .= " td.chi_name,";
        $sql .= " td.unit,";
        $sql .= " td.price,";
        $sql .= " td.qty";
        $sql .= " FROM `t_transaction_h` as th";
        $sql .= " LEFT JOIN `t_transaction_d` as td";
        $sql .= " ON th.trans_code = td.trans_code";
        $sql .= " WHERE th.cust_code = '".$_cust_code."'";
        $sql .= " AND (";
        $sql .= " th.prefix = ( SELECT prefix FROM t_prefix WHERE uid = 1 )";
        $sql .= " OR th.prefix = ( SELECT prefix FROM t_prefix WHERE uid = 3)";
        $sql .= " )";
        $sql .= " GROUP BY td.item_code DESC";
        $sql .= " ORDER BY th.create_date DESC;";

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
     * Next Quotations number
     * Quotations number generator
     * 
     * To gen next Quotations number
     */
    $this->get('/getnextnum/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_max = "00";

        $this->logger->addInfo("Entry: quotations: getnextnum");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = "SELECT prefix FROM t_prefix WHERE uid = 3 LIMIT 1;";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() != 0)
        {
            $_prefix = $q->fetch();
        }
        $sql = "SELECT MAX(trans_code) as max FROM `t_transaction_h` WHERE prefix = '".$_prefix['prefix']."' ORDER BY `create_date` DESC;";
        $q = $db->prepare($sql);
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
            $_data = $_prefix['prefix'].date("ym").str_pad($_max, 2, 0, STR_PAD_LEFT);
        }
        else
        {
            $_result = false;
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

        $this->logger->addInfo("Entry: quotations: getprefix");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = "SELECT prefix FROM `t_prefix` WHERE `uid` = '3' LIMIT 1;";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() != 0)
        {
            $_data = $q->fetch();
        }
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
     * Quotation PATCH request
     * quotations-patch
     * @param body
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

        $this->logger->addInfo("Entry: PATCH: quotations");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        // POST Data here
        $body = json_decode($request->getBody(), true);
        extract($body);
        
        // To convert money format to decimal
        $total = filter_var($total,FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        
        $sql = "SELECT * FROM `t_transaction_d` WHERE trans_code = '".$_trans_code."';";
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

        if(!empty($_new_res))
        {
            $db->beginTransaction();
            // transaction header
            
            $sql = "UPDATE `t_transaction_h` SET";
            $sql .= " cust_code = '".$cust_code."',";
            $sql .= " total = '".$total."', ";
            $sql .= " employee_code = '".$employee_code."', ";
            $sql .= " shop_code = '".$shopcode."', ";
            $sql .= " remark = '".$remark."', ";
            $sql .= " modify_date =  '".$_now."'";
            $sql .= " WHERE trans_code = '".$_trans_code."';";
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

                        $q = $db->prepare($sql);
                        $q->execute();
                        $_err[] = $q->errorinfo();
                    }
                    // New add items
                    else
                    {
                        $sql = "insert into t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, unit, price, discount, create_date)";
                        $sql .= " values (";
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
                        //echo $sql_d."\n";
                        $q = $db->prepare($sql);
                        $q->execute();
                        $_err[] = $q->errorinfo();
                    }   
                }
                // tender information input here
                $sql = "UPDATE `t_transaction_t` SET";
                $sql .= " pm_code = '".$paymentmethod."',";
                $sql .= " total = '".$total."',";
                $sql .= " modify_date = '".$_now."'";
                $sql .= " WHERE trans_code = '".$_trans_code."';";
                $q = $db->prepare($sql);
                $q->execute();
                $_err[] = $q->errorinfo();
            }
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
     * Quotations POST request
     * quotations-post
     * @param body
     * 
     * Add new record to DB
     */
    $this->post('/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";

        $this->logger->addInfo("Entry: POST: quotations");
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
        $sql = "insert into t_transaction_h (trans_code, cust_code, prefix, total, employee_code, shop_code, remark, is_void, is_convert, create_date) ";
        $sql .= " values (";
        $sql .= " '".$quotation."',";
        $sql .= " '".$cust_code."',";
        $sql .= " '".$prefix."',";
        $sql .= " '".$total."',";
        $sql .= " '".$employee_code."',";
        $sql .= " '".$shopcode."',";
        $sql .= " '".$remark."',";
        $sql .= " '0',";
        $sql .= " '0',";
        $sql .= " '".$date."'";
        $sql .= " );";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
    
        if(!empty($db->lastInsertId()))
        {
            foreach($items as $k => $v)
            {
                $sql = "insert into t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, unit, price, discount, create_date)";
                $sql .= " values (";
                $sql .= " '".$quotation."',";
                $sql .= " '".$v['item_code']."',";
                $sql .= " '".$v['eng_name']."' ,";
                $sql .= " '".$v['chi_name']."' ,";
                $sql .= " '".$v['qty']."',";
                $sql .= " '".$v['unit']."',";
                $sql .= " '".$v['price']."',";
                $sql .= "     '".$v['price_special']."',";
                $sql .= " '".$date."'";
                $sql .= " );";
                $q = $db->prepare($sql);
                $q->execute();
                $_err[] = $q->errorinfo();
            }
            // tender information input here
            $sql = "insert into t_transaction_t (trans_code, pm_code, total, create_date)";
            $sql .= " values (";
            $sql .= " '".$quotation."',";
            $sql .= " '".$paymentmethod."',";
            $sql .= " '".$total."',";
            $sql .= " '".$date."'";
            $sql .= " );";
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
            $_callback['error']['message'] = "Transaction: ".$quotation." - Insert OK!";
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
     * Quotations Delete Request
     * quotations-delete
     * 
     * To remove quotation record based on quotation code
     */
    $this->delete('/{trans_code}', function(Request $request, Response $response, array $args){
        $_trans_code = $args['trans_code'];
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $this->logger->addInfo("Entry: DELETE: quotations");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        // transaction start
        $db->beginTransaction();
        // sql statement
        $sql = "UPDATE `t_transaction_h` SET is_void = '1' WHERE trans_code = '".$_trans_code."';";
        // prepare sql statement
        $q = $db->prepare($sql);
        // execute statement
        $q->execute();
        $_err[0] = $q->errorinfo();

        // transaction end
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

            $this->logger->addInfo("Entry: quotations: get header");
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