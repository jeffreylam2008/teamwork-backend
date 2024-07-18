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
            $sql2 = "SELECT";
            $sql2 .= " td.item_code,";
            $sql2 .= " td.eng_name,";
            $sql2 .= " td.chi_name,";
            $sql2 .= " td.qty,";
            $sql2 .= " td.unit,";
            $sql2 .= " tw.qty as `stockonhand`";
            $sql2 .= " FROM `t_transaction_d` as td";
            $sql2 .= " LEFT JOIN `t_warehouse` as tw ON td.item_code = tw.item_code";
            $sql2 .= " WHERE td.trans_code = '".$_st_num."';";
            // execute SQL statement 2
            $q2 = $db->prepare($sql2);
            $q2->execute();
            $_err[] = $q2->errorinfo();
            $res2 = $q2->fetchAll(PDO::FETCH_ASSOC);

            $_data = $res[0];        
            foreach ($res2 as $key => $val) {
                $_data["items"] = $res2;
            }
            // if(!empty($_data["items"]))
            // {
            //     foreach($_data["items"] as $k => $v)
            //     {
            //         extract($v);
            //         $_data["items"][$k]["subtotal"] = ($qty * $price);
            //     }
            // }
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
    * @param session_id session id
    */
    $this->get('/getnextnum/{session_id}', function (Request $request, Response $response, array $args) {
        $_session_id = $args['session_id'];
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_prefix = ['prefix' => ""];
        $insert = false;

        $this->logger->addInfo("Entry: stocktake: getnextnum");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        if(empty($_session_id))
        {
            $_session_id = "";
        }
        // prefix SQL
        $sql = "SELECT prefix FROM `t_prefix` WHERE `uid` = '7' LIMIT 1;";
        // $this->logger->addInfo("SQL = ".$sql);
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() > 0)
        {
            $_prefix = $q->fetch(PDO::FETCH_ASSOC);
        }

        // Get last number from transaction nummber generator
        $sql = "SELECT `last`, `prefix`, `suffix`, `session_id` FROM `t_trans_num_generator` WHERE `prefix` in (SELECT prefix FROM t_prefix WHERE uid = 7)  ORDER BY `create_date` DESC LIMIT 1";
        // $this->logger->addInfo("SQL = ".$sql);
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $_data = $q->fetch(PDO::FETCH_ASSOC);
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
            $sql = "INSERT INTO `t_trans_num_generator` (`prefix`, `suffix`, `last`, `session_id`, `create_date`, `expiry_date`)  VALUES('".$prefix."', '".$suffix."', '".$last."', '".$_session_id."', '".date('Y-m-d H:i:s')."', null);";
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
     * Get Prefix for current transaction
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
     * @body
     */
     $this->post('/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_body = json_decode($request->getBody(), true);
        extract($_body);

        $this->logger->addInfo("Entry: POST: stocktake");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
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
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_num = $args['st_num'];

        $this->logger->addInfo("Entry: DELETE: stocktake ".$_num);
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        // transaction start
        $db->beginTransaction();
        // sql statement
        $sql = "DELETE FROM `t_transaction_h` WHERE trans_code = '".$_num."';";
        $this->logger->addInfo("SQL = ".$sql);
        // prepare sql statement
        $q = $db->prepare($sql);
        // execute statement
        $q->execute();
        $_err[] = $q->errorinfo();

        $sql = "DELETE FROM `t_transaction_d` WHERE trans_code = '".$_num."';";
        $this->logger->addInfo("SQL = ".$sql);
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
            $_callback['error']['message'] = "Transaction: ".$_num." - Deleted!";
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
        $_body = json_decode($request->getBody(), true);
        extract($_body);

        $this->logger->addInfo("Entry: PATCH: stocktake");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        // Start transaction 
        $db->beginTransaction();

        foreach($items as $k => $v)
        {
            if(empty($v['qty'])) $v['qty'] = 0;
            $sql = "UPDATE t_transaction_d ";
            $sql .= "SET `qty` = '".$v['qty']."', ";
            $sql .= "`modify_date` = '".$_now."' ";
            $sql .= "WHERE `item_code` = '".$v['item_code']."' AND `trans_code` = '".$_trans_code."';";

            // deduct stock base on qty
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
        }

        $sql = "UPDATE t_transaction_h ";
        $sql .= "SET `modify_date`='".$_now."', ";
        $sql .= "`remark`='".$remark."' ";
        $sql .= "WHERE `trans_code`= '".$_trans_code."'; ";
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
    /**
     * Update Stocktake record status
     * @param st_num stocktake transaction number required
     */
    $this->patch("/isconvert/{st_num}",function(Request $request, Response $response, array $args){
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
    /**
     * View of Stocktake
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
            $_param = $request->getQueryParams();
            $_username = $args['username'];
            $_result = true;
            $_msg = "";

            $this->logger->addInfo("Entry: Stocktake: get header");
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
            if(!empty($_param['lang'])){
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
            }
            //SQL 3
            $sql = "SELECT prefix FROM `t_prefix` WHERE `uid` = '7' LIMIT 1;";
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