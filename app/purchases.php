<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/purchases/order', function () {
    /**
     * Purchase order GET Request
     * Purchace Order-get
     * 
     * To get all Purchase Order record
     */
    $this->get('/', function (Request $request, Response $response, array $args) {
        $_param = array();
        $_param = $request->getQueryParams();

        if(empty($_param['page']) && empty( $_param['show']))
        {
            $_param['page'] = "1";
            $_param['show'] = "50";
        }
        if(empty($_param['i-start-date']) && empty($_param['i-end-date']))
        {
            $_param['i-start-date'] = "";
            $_param['i-end-date'] = "";
        }
        if(empty($_param['i-num']))
        {
            $_param['i-num'] = "";
        }
        $_callback = [];
        $_err = [];
        $_query = [];
        $pdo = new Database();
	    $db = $pdo->connect_db();

        // t_transaction_h SQL
        $q = $db->prepare("
        SELECT 
            th.*,
            tpm.payment_method, 
            tsp.name as `supplier`, 
            ts.name as `shop_name`,
            ts.shop_code
        FROM `t_transaction_h` as th 
        LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code 
        LEFT JOIN `t_suppliers` as tsp ON th.supp_code = tsp.supp_code 
        LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code
        LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code
        WHERE th.is_void = 0 
        AND th.prefix = 'PO' 
        AND date(th.create_date) BETWEEN '".$_param['i-start-date']."' 
        AND '".$_param['i-end-date']."'
        OR th.trans_code = '".$_param['i-num']."'
        ORDER BY th.`create_date` DESC;
        ");
 
        $q->execute();
        $_err = $q->errorinfo();
        $_res = $q->fetchAll(PDO::FETCH_ASSOC);
    
        // export data
        if(!empty($_res))
        {
            foreach ($_res as $key => $val) {
                $_query[] = $val;
            }
            $_callback = [
                "query" => $_query,
                "error" => ["code" => $_err[0], "message" => $_err[1]." ".$_err[2]]
            ];
            return $response->withJson($_callback, 200);
        }
    });

    /**
     * GET PO transaction request 
     * @param trans_code
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
                th.trans_code,
                th.create_date as 'date',
                th.employee_code as 'employee_code',
                th.refer_code as 'refernum',
                th.modify_date as 'modifydate',
                tt.pm_code as 'paymentmethod',
                tpm.payment_method as 'paymentmethodname',
                th.prefix as 'prefix',
                th.quotation_code as 'quotation',
                th.remark as 'remark',
                th.shop_code as 'shopcode',
                ts.name as 'shopname',
                th.cust_code as 'cust_code',
                th.supp_code as 'supp_code',
                tsp.name as 'supp_name', 
                th.total as 'total'
            FROM `t_transaction_h` as th
            LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code
            LEFT JOIN `t_suppliers` as tsp ON th.supp_code = tsp.supp_code
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
        return $response->withJson($_callback, 200);
    });

    /**
     * GET Next Number
     * purchase number generator
     * 
     * To gen next purchase number
     */
    $this->get('/getnextnum/', function (Request $request, Response $response, array $args) {
        $err[0] = "";
        $err[1] = "";
        $_data = "";
        $prefix = "PO";
        $_max = "00";
        $pdo = new Database();
        $db = $pdo->connect_db();
        $q = $db->prepare("
            SELECT MAX(trans_code) as max FROM `t_transaction_h` WHERE prefix = '".$prefix."' ORDER BY `create_date` DESC;
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
        $_data = $prefix.date("ym").str_pad($_max, 2, 0, STR_PAD_LEFT);

        $callback = [
            "query" => $_data,
            "error" => [
                "code" => $err[0], 
                "message" => $err[1]
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
        $err[0] = "";
        $err[1] = "";
        $_data = "";
        $prefix = "PO";
        $err[1] = "done!";
        $callback = [
            "query" => $prefix,
            "error" => [
                "code" => $err[0], 
                "message" => $err[1]
            ]
        ];
    
        return $response->withJson($callback, 200);
    });

    /**
     * GET Last supplier
     * quotations-find-latest-record
     * 
     * To get single quotations record
     */
    $this->get('/getlast/supp/{supp_code}', function (Request $request, Response $response, array $args) {
        $_callback = [];
        $_err = [];
        $_res = [];

        $supp_code = $args['supp_code'];
        $pdo = new Database();
        $db = $pdo->connect_db();
        
        $sql = "
        SELECT th.*, 
        tpm.payment_method, 
        tsp.name as `supplier`, 
        ts.name as `shop_name`, 
        ts.shop_code 
        FROM `t_transaction_h` as th 
        LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code 
        LEFT JOIN `t_suppliers` as tsp ON th.supp_code = tsp.supp_code 
        LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code 
        LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code 
        WHERE th.supp_code = '".$supp_code."' AND th.prefix = 'PO' ORDER BY `create_date` DESC;
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
        
        return $response->withJson($_callback, 200);
    });

    /**
     * Edit PO record
     * @param trans_code po number
     */
    $this->patch('/{trans_code}', function (Request $request, Response $response, array $args){
        $_err = [];
		$_done = false;
		$_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_now = date('Y-m-d H:i:s');
        $_new_res = [];
        $pdo = new Database();
		$db = $pdo->connect_db();
        //$sql_d = "";
        // POST Data here
        $body = json_decode($request->getBody(), true);
        extract($body);
    
        $_trans_code = $args['trans_code'];
        $sql = "SELECT * FROM `t_transaction_d` WHERE trans_code = '".$_trans_code."';";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[0] = $q->errorinfo();
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
            
            $q = $db->prepare("UPDATE `t_transaction_h` SET 
                supp_code = '".$suppcode."',
                total = '".$total."',  
                shop_code = '".$shopcode."', 
                remark = '".$remark."', 
                modify_date =  '".$_now."'
                WHERE trans_code = '".$_trans_code."';"
            );
            $q->execute();
            $_err[0] = $q->errorinfo();
            
            if($q->rowCount() != "0")
            {
                foreach($_new_res as $k_itcode => $v)
                {
                    // delete items from this transaction
                    if(!array_key_exists($k_itcode,$items))
                    {

                        $sql_d = "DELETE FROM `t_transaction_d` WHERE trans_code = '".$_trans_code."' AND item_code = '".$k_itcode."';";
                        $q = $db->prepare($sql_d);
                        $q->execute();
                        $_err[2] = $q->errorinfo();
                        //echo $sql_d."\n";
                    }
                }
                foreach($items as $k_itcode => $v)
                {
                    // update item already in transaction
                    if(array_key_exists($k_itcode,$_new_res))
                    {

                        $sql_d = "UPDATE `t_transaction_d` SET
                            qty = '".$v['qty']."',
                            unit = '".$v['unit']."',
                            price = '".$v['price']."',
                            modify_date = '".$_now."'
                            WHERE trans_code = '".$_trans_code."' AND item_code = '".$k_itcode."';";
                        //echo $sql_d."\n";
                        $q = $db->prepare($sql_d);
                        $q->execute();
                        $_err[1] = $q->errorinfo();
                    }
                    // New add items
                    else
                    {
                        $sql_d = "insert into t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, unit, price, discount, create_date)
                            values (
                                '".$_trans_code."',
                                '".$v['item_code']."',
                                '".$v['eng_name']."' ,
                                '".$v['chi_name']."' ,
                                '".$v['qty']."',
                                '".$v['unit']."',
                                '".$v['price']."',
                                '".$v['price_special']."',
                                '".$date."'
                            );";
                        //echo $sql_d."\n";
                        $q = $db->prepare($sql_d);
                        $q->execute();
                        $_err[3] = $q->errorinfo();
                    }   
                }
                
                // tender information input here
                $sql ="UPDATE `t_transaction_t` SET 
                    pm_code = '".$paymentmethod."',
                    total = '".$total."',
                    modify_date = '".$_now."'
                    WHERE trans_code = '".$_trans_code."';";
                $q = $db->prepare($sql);
                $q->execute();
                $_err[4] = $q->errorinfo();
            }
            $_done = true;
            $db->commit();
            //disconnection DB
            $pdo->disconnect_db();
        }

        // finish up the flow
		if($_done)
        {
            if($_err[0][0] == "00000")
            {
                $_callback['query'] = "";
                $_callback["error"] = [
                    "code" => "00000", 
                    "message" => $_trans_code ." Update Success!"
                ]; 
            }
            else
            { 
                $_callback['query'] = "";
                $_callback["error"] = [
                    "code" => "99999", 
                    "message" => "DB Error: ".$_err[0][2]." - ".$_err[1][2]." - ".$_err[2][2]." - ".$_err[3][2]." - ".$_err[4][2]
                ]; 
            }
        }
        return $response->withJson($_callback,200);
    });

    /**
     * POST request
     * Purchase-post
     * @param body
     * 
     * Add new record to DB
     */
     $this->post('/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_done = false;
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $pdo = new Database();
		$db = $pdo->connect_db();
        // POST Data here
        $body = json_decode($request->getBody(), true);
        extract($body);
        // Start transaction 
        $db->beginTransaction();
        // insert record to transaction_h
        $sql = "insert into t_transaction_h (trans_code, refer_code, supp_code, prefix, total, employee_code, shop_code, remark, is_void, is_convert, create_date) 
            values (
                '".$purchasesnum."',
                '".$refernum."',
                '".$suppcode."',
                '".$prefix."',
                '".$total."',
                '".$employee_code."',
                '".$shopcode."',
                '".$remark."',
                '0',
                '0',
                '".$date."'
            );
        ";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[0] = $q->errorinfo();
        // insert record to transaction_d
        if(!empty($db->lastInsertId()))
        {
            foreach($items as $k => $v)
            {
                $q = $db->prepare("insert into t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, unit, price, discount, create_date)
                    values (
                        '".$purchasesnum."',
                        '".$v['item_code']."',
                        '".$v['eng_name']."' ,
                        '".$v['chi_name']."' ,
                        '".$v['qty']."',
                        '".$v['unit']."',
                        '".$v['price']."',
                        '".$v['price_special']."',
                        '".$date."'
                    );
                ");
                $q->execute();
            }
            $_err[1] = $q->errorinfo();
            // tender information input here
            $tr = $db->prepare("insert into t_transaction_t (trans_code, pm_code, total, create_date) 
                values (
                    '".$purchasesnum."',
                    '".$paymentmethod."',
                    '".$total."',
                    '".$date."'
                );
            ");
            $tr->execute();
            $_err[2] = $tr->errorinfo();
        }

        $db->commit();
        //disconnection DB
        $pdo->disconnect_db();
        
        if($_err[0][0] = "00000")
        {
            $_callback['query'] = "";
            $_callback["error"] = [
                "code" => "00000", 
                "message" => $purchasesnum." Insert Success!"
            ]; 
        }
        else
        { 
            $_callback['query'] = "";
            $_callback["error"] = [
                "code" => "99999", 
                "message" => "DB Error: "
                .$_err[0][2]." - "
                .$_err[1][2]." - "
                .$_err[2][2]." - "
                .$_err[3][2]
            ]; 
        }
        return $response->withJson($_callback,200);
    });
    
    /**
     * Delete request
     * @param body
     * 
     * Add new record to DB
     */
    $this->delete('/{trans_code}', function (Request $request, Response $response, array $args) {
        $_trans_code = $args['trans_code'];
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
            UPDATE `t_transaction_h` SET is_void = '1' WHERE trans_code = '".$_trans_code."';
        ";
        // prepare sql statement
        $q = $db->prepare($sql);
        // execute statement
        $q->execute();
        $_err[0] = $q->errorinfo();

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
                "message" => $_trans_code . " Deleted!"
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
});