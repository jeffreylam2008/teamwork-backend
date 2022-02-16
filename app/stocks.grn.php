<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Stocks -> Good Recevied Note
 */
$app->group('/api/v1/stocks/po/grn', function () {
    /**
     * GRN GET request 
     * GRN-get-by-code
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
    
        $this->logger->addInfo("Entry: stocks: Get stocks grn by trans_code");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = "SELECT";
        $sql .= " th.trans_code as 'grn_num',";
        $sql .= " th.create_date as 'date',";
        $sql .= " th.employee_code as 'employee_code',";
        $sql .= " th.refer_code as 'refer_num',";
        $sql .= " th.modify_date as 'modifydate',";
        $sql .= " tt.pm_code as 'paymentmethod',";
        $sql .= " tpm.payment_method as 'paymentmethodname',";
        $sql .= " th.prefix as 'prefix',";
        $sql .= " th.quotation_code as 'quotation',";
        $sql .= " th.remark as 'remark',";
        $sql .= " th.shop_code as 'shop_code',";
        $sql .= " ts.name as 'shopname',";
        $sql .= " th.supp_code as 'supp_code',";
        $sql .= " tsp.name as 'supp_name', ";
        $sql .= " th.total as 'total'";
        $sql .= " FROM `t_transaction_h` as th";
        $sql .= " LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code";
        $sql .= " LEFT JOIN `t_suppliers` as tsp ON th.supp_code = tsp.supp_code";
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
            $sql .= " tw.qty as 'stockonhand'";
            $sql .= " FROM `t_transaction_d` as td";
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
    
    /*
    * Get Next delivery note number 
    */
    $this->get('/getnextnum/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_max = "00";
        
        $this->logger->addInfo("Entry: stocks grn: getnextnum");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        
        // prefix SQL
        $sql = "SELECT prefix FROM `t_prefix` WHERE `uid` = '5' LIMIT 1;";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q1->rowCount() != 0)
        {
            $_prefix = $q1->fetch();
        }
        // get next number SQL
        $sql = "SELECT MAX(trans_code) as max FROM `t_transaction_h` WHERE prefix = '".$_prefix['prefix']."' ORDER BY `create_date` DESC;";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $_data = $q->fetch();

        // disconnect DB session
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");

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

        $this->logger->addInfo("Entry: stocks: getprefix");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        // prefix SQL
        $sql = "SELECT prefix FROM `t_prefix` WHERE `uid` = '5' LIMIT 1;";
        $q1 = $db->prepare($sql);
        $q1->execute();
        $_err = $q1->errorinfo();
        if($q1->rowCount() != 0)
        {
            $_data = $q1->fetch();
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
     * Goods received POST request
     *
     * grn-post
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
        $sql = "INSERT INTO t_transaction_h (trans_code, prefix, employee_code, refer_code, supp_code, shop_code, total, remark, create_date)";
        $sql .= " VALUES (";
        $sql .= " '".$grn_num."',";
        $sql .= " '".$prefix."',";
        $sql .= " '".$employee_code."',";
        $sql .= " '".$po_num."',";
        $sql .= " '".$supp_code."',";
        $sql .= " '".$shop_code."',";
        $sql .= " '".$total."',";
        $sql .= " '".$remark."',";
        $sql .= " '".$date."'";
        $sql .= " );";

        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        // insert record to transaction_d
        if(!empty($db->lastInsertId()))
        {
            foreach($items as $k => $v)
            {
                // add stock
                /* retrieve stockonhand from warehouse table */
                $sql = "SELECT `qty` FROM `t_warehouse` WHERE `item_code` = '".$v['item_code']."' LIMIT 1 INTO @_qty;";
                /* insert items to transaction_d */
                $sql .= " INSERT INTO t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, pstock, unit, price, create_date)";
                $sql .= " VALUES (";
                $sql .= " '".$grn_num."',";
                $sql .= " '".$v['item_code']."',";
                $sql .= " '".$v['eng_name']."' ,";
                $sql .= " '".$v['chi_name']."' ,";
                $sql .= " '+".$v['qty']."',";
                $sql .= " '".$v['stockonhand']."',";
                $sql .= " '".$v['unit']."',";
                $sql .= " '".$v['price']."',";
                $sql .= " '".$date."'";
                $sql .= " );";

                /* update warehouse stockonhand */
                $sql .= "UPDATE `t_warehouse` SET";
                $sql .= " `qty` = @_qty + ".$v['qty'].",";
                $sql .= " `type` = 'in',";
                $sql .= " `modify_date` = '".$date."'";
                $sql .= " WHERE";
                $sql .= " `item_code` = '".$v['item_code']."';";

                $q = $db->prepare($sql);
                $q->execute();
                $_err[] = $q->errorinfo();
            }
            // insert record to transaction tender
            $sql = "INSERT INTO t_transaction_t (trans_code, pm_code, total, create_date)";
            $sql .= " VALUES (";
            $sql .= " '".$grn_num."',";
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
            $_callback['error']['message'] = "Transaction: ".$grn_num." - Insert OK!";
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