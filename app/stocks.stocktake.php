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
        $_query = [];
        $_err = []; 
        $_st_num = $args['st_num'];
        $pdo = new Database();
        $db = $pdo->connect_db();
        $sql = " 
            SELECT 
                th.trans_code,
                th.prefix,
                th.employee_code,
                th.shop_code,
                th.remark,
                th.is_convert,
                th.create_date as 'date'
            FROM `t_transaction_h` as th 
            WHERE th.trans_code = '".$_st_num."' ;
        ";
        $sql2 = "
            SELECT 
                td.item_code,
                td.eng_name,
                td.chi_name,
                td.qty,
                td.unit,
                tw.qty as `stockonhand`
            FROM `t_transaction_d` as td
            LEFT JOIN `t_warehouse` as tw ON td.item_code = tw.item_code
            WHERE td.trans_code = '".$_st_num."';
        ";
        $q = $db->prepare($sql);
        $q->execute();
        $_err = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        // execute SQL statement 2
        $q = $db->prepare($sql2);
        $q->execute();
        $_err2 = $q->errorinfo();
        $res2 = $q->fetchAll(PDO::FETCH_ASSOC);
         // export data
         if(!empty($res))
         {
             $_query = $res[0];        
             foreach ($res2 as $key => $val) {
                  $_query["items"] = $res2;
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
         return $response->withJson($_callback, 200);
    });

    /**
    * Get Next stock take number 
    */
    $this->get('/getnextnum/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_data = "";
        $_prefix['prefix'] = "";
        $_max = "00";
        $pdo = new Database();
        $db = $pdo->connect_db();
        // prefix SQL
        $q1 = $db->prepare("
            SELECT prefix FROM `t_prefix` WHERE `uid` = '7' LIMIT 1;
        ");
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

        // disconnect DB session
        $pdo->disconnect_db();
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
        $callback = [
            "query" => $_data,
            "error" => [
                "code" => $_err, 
                "message" => $_err
            ]
        ];
        return $response->withJson($callback, 200);
    });
    /**
     * Get Prefix
     * 
     * To get prefix
     */
    $this->get('/getprefix/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_prefix['prefix'] = "";
        $pdo = new Database();
        $db = $pdo->connect_db();
        // prefix SQL
        $q1 = $db->prepare("
            SELECT prefix FROM `t_prefix` WHERE `uid` = '7' LIMIT 1;
        ");
        $q1->execute();
        $_err = $q1->errorinfo();
        if($q1->rowCount() != "0")
        {
            $_prefix = $q1->fetch();
        }

        // disconnect DB session
        $pdo->disconnect_db();

        $callback = [
            "query" => $_prefix['prefix'],
            "error" => [
                "code" => $_err, 
                "message" => $_err
            ]
        ];
        return $response->withJson($callback, 200);
    });

    /**
     * Check Stock and compare stock different
     * 
     */
     $this->post('/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $pdo = new Database();
		$db = $pdo->connect_db();
        $body = json_decode($request->getBody(), true);
        
        extract($body);
        
        // Start transaction 
        $db->beginTransaction();

        // insert record to transaction_h
        $q = $db->prepare("
            INSERT INTO t_transaction_h (trans_code, prefix, employee_code, shop_code, remark, create_date) 
            VALUES (
                '".$trans_code."',
                '".$prefix."',
                '".$employee_code."',
                '".$shopcode."',
                '".$remark."',
                '".$date."'
            );
        ");
        
        $q->execute();
        $_err[0][] = $q->errorinfo();
        // insert record to transaction_d
        if((int) $db->lastInsertId() != 0)
        {
            foreach($items as $k => $v)
            {
                // deduct stock base on qty
                $q = $db->prepare("
                    INSERT INTO t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, unit, create_date)
                    VALUES (
                        '".$trans_code."',
                        '".$v['item_code']."',
                        '".$v['eng_name']."' ,
                        '".$v['chi_name']."' ,
                        '".$v['qty']."',
                        '".$v['unit']."',
                        '".$date."'
                    );
                ");
                $q->execute();
                $_err[1][] = $q->errorinfo();
            }
        }
        $db->commit();
        //disconnection DB
        $pdo->disconnect_db();
        
        if($_err[0][0] = "00000")
        {
            $_callback['query'] = "";
            $_callback["error"] = [
                "code" => "00000", 
                "message" => $trans_code." Insert Success !"
            ]; 
            return $response->withJson($_callback,200);
        }
        else
        { 
            $_callback['query'] = "";
            $_callback["error"] = [
                "code" => "99999", 
                "message" => "DB Error: "
                .$_err[0][2]." - "
                .$_err[1][2]." - "
                .$_err[2][2]
            ]; 
            return $response->withJson($_callback,404);
        }
    });

    /**
     * Delete StockTake record
     * @param st_num stocktake transaction number required
     */
    $this->delete('/{st_num}', function (Request $request, Response $response, array $args) {
        $_num = $args['st_num'];
        $_err = [];
        $_callback = [
            'query' => "",
            'error' => [
                "code" => "",
                "message" => ""
            ]
        ];
        $pdo = new Database();
        $db = $pdo->connect_db();
        // transaction start
        $db->beginTransaction();
        // sql statement
        $sql = "
            DELETE FROM `t_transaction_h` WHERE trans_code = '".$_num."';
        ";
        // prepare sql statement
        $q = $db->prepare($sql);
        // execute statement
        $q->execute();
        $_err[0][] = $q->errorinfo();
        $sql = "
            DELETE FROM `t_transaction_d` WHERE trans_code = '".$_num."';
        ";
        // prepare sql statement
        $q = $db->prepare($sql);
        // execute statement
        $q->execute();
        $_err[1][] = $q->errorinfo();

        // transaction end
        $db->commit();
        // disconnect DB
        $pdo->disconnect_db();

        // SQL error return
        if($_err[0][0] = "00000" && $_err[1][0] = "00000")
        {
            $_callback['query'] = "";
            $_callback["error"] = [
                "code" => "00000", 
                "message" => $_num . " Deleted!"
            ]; 
        }
        else
        { 
            $_callback['query'] = "";
            $_callback["error"] = [
                "code" => "99999", 
                "message" => "DB Error: ".$_err[0][2]." - ".$_err[1][2]." - ".$_err[2][2]
            ]; 
        }
        return $response->withJson($_callback,200);
    });

    /**
     * Update Stocktake record status
     * @param st_num stocktake transaction number required
     */
    $this->patch("/{st_num}",function(Request $request, Response $response, array $args){
        $_num = "";
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $pdo = new Database();
		$db = $pdo->connect_db();
        $_num = $args['st_num'];

        // Start transaction 
        $db->beginTransaction();

        // insert record to transaction_h
        $q = $db->prepare("
        UPDATE 
            `t_transaction_h`
        SET 
            `is_convert` = '1'
        WHERE `trans_code` = '".$_num."';
        ");
        $q->execute();
        $_err[0][] = $q->errorinfo();
        
        $db->commit();
        //disconnection DB
        $pdo->disconnect_db();
        
        if($_err[0][0] = "00000")
        {
            $_callback['query'] = "";
            $_callback["error"] = [
                "code" => "00000", 
                "message" => $_num." Updated!"
            ]; 
            return $response->withJson($_callback,200);
        }
        else
        { 
            $_callback['query'] = "";
            $_callback["error"] = [
                "code" => "99999", 
                "message" => "DB Error: ".$_err
            ]; 
            return $response->withJson($_callback,401);
        }
    });

    
});