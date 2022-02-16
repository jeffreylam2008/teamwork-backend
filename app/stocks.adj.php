<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Stocks -> adjustment
 * 
 */
$app->group('/api/v1/stocks/adj', function () {
    /**
     * ADJ GET request 
     * ADJ-get-by-code
     * 
     * Get ADJ by trans_code
     */
    $this->get('/{trans_code}', function (Request $request, Response $response, array $args) {
        // inital variable
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];

        $_trans_code= $args['trans_code'];
        $this->logger->addInfo("Entry: stocks adjustment: get transaction by code");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = "SELECT";
        $sql .= " th.trans_code as 'adj_num',";
        $sql .= " th.create_date as 'date',";
        $sql .= " th.employee_code as 'employee_code',";
        $sql .= " th.modify_date as 'modifydate',";
        $sql .= " th.refer_code as 'refer_num',";
        $sql .= " th.prefix as 'prefix',";
        $sql .= " th.quotation_code as 'quotation',";
        $sql .= " th.remark as 'remark',";
        $sql .= " th.shop_code as 'shop_code',";
        $sql .= " ts.name as 'shopname'";
        $sql .= " FROM `t_transaction_h` as th";
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
            $sql .= " td.pstock as 'stockonhand'";
            $sql .= " FROM `t_transaction_d` as td";
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
        $_prefix['prefix'] = "";
        $_max = "00";


        $this->logger->addInfo("Entry: stocks adjustment: getnextnum");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        // prefix SQL
        $sql = "SELECT prefix FROM `t_prefix` WHERE `uid` = '6' LIMIT 1;";
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
        }
        $_data = $_prefix['prefix'].date("ym").str_pad($_max, 2, 0, STR_PAD_LEFT);
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
        $_prefix['prefix'] = "";

        $this->logger->addInfo("Entry: stocks adjustment: getprefix");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        // prefix SQL
        $sql = "SELECT prefix FROM `t_prefix` WHERE `uid` = '6' LIMIT 1;";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        
        if($q->rowCount() != 0)
        {
            $_data = $q->fetch();
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
     * POST Create new adjustment transaction
     * 
     */
    $this->post('/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";

        $this->logger->addInfo("Entry: POST: Stocks Adjustment");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $body = json_decode($request->getBody(), true);
        extract($body);
        
        // Start transaction 
        $db->beginTransaction();

        // insert record to transaction_h
        $sql = "INSERT INTO t_transaction_h (trans_code, refer_code, prefix, employee_code, shop_code, remark, create_date)";
        $sql .= " VALUES (";
        $sql .= " '".$adj_num."',";
        $sql .= " '".$refer_num."',";
        $sql .= " '".$prefix."',";
        $sql .= " '".$employee_code."',";
        $sql .= " '".$shopcode."',";
        $sql .= " '".$remark."',";
        $sql .= " '".$date."'";
        $sql .= " );";        
        $q = $db->prepare($sql);
        // $this->logger->addInfo("SQL: ".$sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        // insert record to transaction_d
        if(!empty($db->lastInsertId()))
        {
            foreach($items as $k => $v)
            {
                if($v['qty'] > 0)
                {
                    $v['qty'] = "+".intval($v['qty']);
                }
                $sql = "INSERT INTO t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, pstock, unit, create_date)";
                $sql .= " VALUES (";
                $sql .= " '".$adj_num."',";
                $sql .= " '".$v['item_code']."',";
                $sql .= " '".$v['eng_name']."' ,";
                $sql .= " '".$v['chi_name']."' ,";
                $sql .= " '".$v['qty']."',";
                $sql .= " '".$v['stockonhand']."',";
                $sql .= " '".$v['unit']."',";
                $sql .= " '".$date."'";
                $sql .= " );";
                // deduct stock base on qty
                $q = $db->prepare($sql);
                $q->execute();
                $_err[] = $q->errorinfo();

                $sql = "SET @qty := (SELECT `qty` FROM `t_warehouse` WHERE `item_code` = '".$v['item_code']."') + ".$v['qty'].";";
                $q = $db->prepare($sql);
                $q->execute();
                $_err[] = $q->errorinfo();
                
                $sql = "UPDATE";
                $sql .= " `t_warehouse`";
                $sql .= " SET";
                $sql .= " `qty` = @qty,";
                $sql .= " `modify_date` = '".$date."'"; 
                $sql .= " WHERE `item_code` = '".$v['item_code']."';";
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
            $_callback['error']['message'] = "Transaction: ".$adj_num." - Adjust OK!";
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