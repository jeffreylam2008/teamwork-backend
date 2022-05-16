<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Stocks -> Delivery Note
 */
$app->group('/api/v1/stocks/dn', function () {
    /**
     * GET Operation By transaction code
     * @param trans_code transaction code required
     */
    $this->get('/{trans_code}', function (Request $request, Response $response, array $args) {
        // inital variable
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        
        $_trans_code= $args['trans_code'];
    
        $this->logger->addInfo("Entry: dn: get dn by code");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = "SELECT";
        $sql .= " th.trans_code as 'dn_num',";
        $sql .= " th.create_date as 'date',";
        $sql .= " th.employee_code as 'employee_code',";
        $sql .= " th.refer_code as 'refer_num',";
        $sql .= " th.modify_date as 'modifydate',";
        $sql .= " (SELECT ttt.pm_code FROM `t_transaction_t` as ttt WHERE ttt.trans_code = th.refer_code) as 'paymentmethod',";
        $sql .= " (SELECT tpmm.payment_method FROM `t_transaction_t` as ttt LEFT JOIN `t_payment_method` as tpmm ON ttt.pm_code = tpmm.pm_code WHERE ttt.trans_code = th.refer_code)  as 'paymentmethodname',";
        $sql .= " th.prefix as 'prefix',";
        $sql .= " th.quotation_code as 'quotation',";
        $sql .= " th.remark as 'remark',";
        $sql .= " th.shop_code as 'shopcode',";
        $sql .= " ts.name as 'shopname',";
        $sql .= " th.cust_code as 'cust_code',";
        $sql .= " tsp.name as 'cust_name', ";
        $sql .= " tsp.delivery_addr,";
        $sql .= " tsp.district_code,";
        $sql .= " tsp.from_time,";
        $sql .= " tsp.to_time,";
        $sql .= " tsp.delivery_remark,";
        $sql .= " th.total as 'total'";
        $sql .= " FROM `t_transaction_h` as th";
        $sql .= " LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code";
        $sql .= " LEFT JOIN `t_customers` as tsp ON th.cust_code = tsp.cust_code";
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
            $sql .= " td.pstock as 'stockonhand'";
            $sql .= " FROM `t_transaction_d` as td";
            $sql .= " WHERE trans_code = '".$_trans_code."';";
            // execute SQL statement 2
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $res2 = $q->fetchAll(PDO::FETCH_ASSOC);
            
            //disconnection DB
            $pdo->disconnect_db();

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
     * GET Operation to get next order number
     */
    $this->get('/getnextnum/{session_id}', function (Request $request, Response $response, array $args) {
        $_session_id = $args['session_id'];
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_max = "00";
        
        $this->logger->addInfo("Entry: dn: getnextnum");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        
        if(empty($_session_id))
        {
            $_session_id = "";
        }

        $sql = "SELECT prefix FROM `t_prefix` WHERE `uid` = '4' LIMIT 1;";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() > 0)
        {
            $_prefix = $q->fetch(PDO::FETCH_ASSOC);
        }
       
        // Get last number from transaction nummber generator
        $sql = "SELECT `last`, `prefix`, `suffix`, `session_id` FROM `t_trans_num_generator` WHERE `prefix` in (SELECT prefix FROM t_prefix WHERE uid = 4)  ORDER BY `create_date` DESC LIMIT 1";
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
     * GET Operation to get prefix
     */
    $this->get('/getprefix/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];

        $this->logger->addInfo("Entry: dn: getprefix");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = "SELECT * FROM `t_prefix` WHERE `uid` = '4' LIMIT 1;";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() > 0)
        {
            $prefix = $q->fetch();
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
     * POST Operation to Insert new DN information
     * @param body body as required
     */
    $this->post('/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";

        $this->logger->addInfo("Entry: POST: dn");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $refer_num = "";
        $quotation = "";
        $body = json_decode($request->getBody(), true);
        extract($body);

        // Set Invoice to reference number
        if(!empty($trans_code))
        {
            $refer_num = $trans_code;
        }

        // Start transaction 
        $db->beginTransaction();
        // Insert record to transaction_h
        $sql = "INSERT INTO t_transaction_h (trans_code, cust_code, quotation_code, refer_code, prefix, employee_code, shop_code, remark, create_date) ";
        $sql .= " VALUES (";
        $sql .= " '".$dn_num."',";
        $sql .= " '".$cust_code."',";
        $sql .= " '".$quotation."',";
        $sql .= " '".$refer_num."',";
        $sql .= " '".$dn_prefix."',";
        $sql .= " '".$employee_code."',";
        $sql .= " '".$shopcode."',";
        $sql .= " '".$remark."',";
        $sql .= " '".$date."'";
        $sql .= " );";
        //$this->logger->addInfo("SQL: ".$sql);
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();

        // insert record to transaction_d
        foreach($items as $k => $v)
        {
            $sql = "SELECT `qty` FROM `t_warehouse` WHERE `item_code` = '".$v['item_code']."' LIMIT 1 INTO @_qty;";
            $sql .= " INSERT INTO t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, pstock, unit, create_date)";
            $sql .= " VALUES (";
            $sql .= " '".$dn_num."',";
            $sql .= " '".$v['item_code']."',";
            $sql .= " '".$v['eng_name']."' ,";
            $sql .= " '".$v['chi_name']."' ,";
            $sql .= " '-".$v['qty']."',";
            $sql .= " @_qty,";
            $sql .= " '".$v['unit']."',";
            $sql .= " '".$date."'";
            $sql .= " );";
            $sql .= " UPDATE";
            $sql .= " `t_warehouse`";
            $sql .= " SET";
            $sql .= " `qty` = @_qty - ".$v['qty'].",";
            $sql .= " `type` = 'out',";
            $sql .= " `modify_date` = '".$date."'";
            $sql .= " WHERE";
            $sql .= " `item_code` = '".$v['item_code']."';";
            // deduct stock base on qty
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
        }
        if(!empty($trans_code))
        {
            //update invoice refer_code field as cross referenece
            $sql = "UPDATE t_transaction_h SET";
            $sql .= " refer_code = '".$dn_num."',";
            $sql .= " modify_date = '".$date."'";
            $sql .= " WHERE trans_code = '".$trans_code."';";  
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
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
});
