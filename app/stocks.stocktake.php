<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;



/**
 * Stocks -> Stock Take
 * 
 */
$app->group('/api/v1/stocks/stocktake', function () {
    /**
     * Get stock take by ID 
     * @param st_num stocktake transaction number required
     */
    $this->get('/{st_num}', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_st_num = $args['st_num'];

        $this->logger->addInfo("Entry: stocktake: get stocktake by trans_code");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        
        $sql = "SELECT";
        $sql .= " th.trans_code,";
        $sql .= " th.prefix,";
        $sql .= " th.employee_code,";
        $sql .= " th.shop_code,";
        $sql .= " th.remark,";
        $sql .= " th.is_convert,";
        $sql .= " th.create_date as 'date'";
        $sql .= " FROM `t_transaction_h` as th ";
        $sql .= " WHERE th.trans_code = '".$_st_num."' ;";
        
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        if($q->rowCount() != 0)
        {
            $sql = "SELECT";
            $sql = " td.item_code,";
            $sql = " td.eng_name,";
            $sql = " td.chi_name,";
            $sql = " td.qty,";
            $sql = " td.unit,";
            $sql = " tw.qty as `stockonhand`";
            $sql = " FROM `t_transaction_d` as td";
            $sql = " LEFT JOIN `t_warehouse` as tw ON td.item_code = tw.item_code";
            $sql = " WHERE td.trans_code = '".$_st_num."';";
            // execute SQL statement 2
            $q = $db->prepare($sql2);
            $q->execute();
            $_err[] = $q->errorinfo();
            $res2 = $q->fetchAll(PDO::FETCH_ASSOC);

            $_data = $res[0];        
            foreach ($res2 as $key => $val) {
                $_data["items"] = $res2;
            }
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
    * Get Next stock take number 
    */
    $this->get('/getnextnum/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_max = "00";

        $this->logger->addInfo("Entry: stocktake: getnextnum");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        // prefix SQL
        $sql = "SELECT prefix FROM `t_prefix` WHERE `uid` = '7' LIMIT 1;";
        $q1 = $db->prepare($sql);
        $q1->execute();
        $_err[] = $q1->errorinfo();
        if($q1->rowCount() != "0")
        {
            $_prefix = $q1->fetch();
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

        $this->logger->addInfo("Entry: stocktake: getprefix");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        // prefix SQL
        $sql = "SELECT prefix FROM `t_prefix` WHERE `uid` = '7' LIMIT 1;";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() != "0")
        {
            $_prefix = $q->fetch();
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
     * Check Stock and compare stock different
     * 
     */
     $this->post('/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        
        $this->logger->addInfo("Entry: POST: stocktake");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        $body = json_decode($request->getBody(), true);
        extract($body);
        // Start transaction 
        $db->beginTransaction();

        $sql = "INSERT INTO t_transaction_h (trans_code, prefix, employee_code, shop_code, remark, create_date) ";
        $sql .= " VALUES (";
        $sql .= " '".$trans_code."',";
        $sql .= " '".$prefix."',";
        $sql .= " '".$employee_code."',";
        $sql .= " '".$shopcode."',";
        $sql .= " '".$remark."',";
        $sql .= " '".$date."'";
        $sql .= " );";
        // insert record to transaction_h
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        // insert record to transaction_d
        if((int) $db->lastInsertId() != 0)
        {
            foreach($items as $k => $v)
            {
                $sql = "INSERT INTO t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, unit, create_date)";
                $sql .= " VALUES (";
                $sql .= " '".$trans_code."',";
                $sql .= " '".$v['item_code']."',";
                $sql .= " '".$v['eng_name']."' ,";
                $sql .= " '".$v['chi_name']."' ,";
                $sql .= " '".$v['qty']."',";
                $sql .= " '".$v['unit']."',";
                $sql .= " '".$date."'";
                $sql .= " );";
                // deduct stock base on qty
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
     * Delete StockTake record
     * @param st_num stocktake transaction number required
     */
    $this->delete('/{st_num}', function (Request $request, Response $response, array $args) {
        $_trans_code = $args['trans_code'];
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_num = $args['st_num'];

        $this->logger->addInfo("Entry: DELETE: stocktake");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        // transaction start
        $db->beginTransaction();
        // sql statement
        $sql = "DELETE FROM `t_transaction_h` WHERE trans_code = '".$_num."';";
        // prepare sql statement
        $q = $db->prepare($sql);
        // execute statement
        $q->execute();
        $_err[] = $q->errorinfo();

        $sql = "DELETE FROM `t_transaction_d` WHERE trans_code = '".$_num."';";
        // prepare sql statement
        $q = $db->prepare($sql);
        // execute statement
        $q->execute();
        $_err[] = $q->errorinfo();

        // transaction end
        $db->commit();
        $this->logger->addInfo("Msg: DB commit");
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
     * Update Stocktake record status
     * @param st_num stocktake transaction number required
     */
    $this->patch("/{st_num}",function(Request $request, Response $response, array $args){
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_now = date('Y-m-d H:i:s');
        $_trans_code = $args['st_num'];

        $this->logger->addInfo("Entry: PATCH: stocktake");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        // Start transaction 
        $db->beginTransaction();
        $sql = "UPDATE `t_transaction_h` SET `is_convert` = '1' WHERE `trans_code` = '".$_trans_code."';";
        // insert record to transaction_h
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        
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

    
});