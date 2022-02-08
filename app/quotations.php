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
        $_param = array();
        $_param = $request->getQueryParams();

        // if(empty($_param['page']) && empty( $_param['show']))
        // {
        //     $_param['page'] = "1";
        //     $_param['show'] = "50";
        // }
        // if(empty($_param['i-end-date']))
        // {
        //     $_param['i-end-date'] = strval(date("Y-m-d"));
        // }
        $_callback = [];
        $_err = [];
        $_query = [];
        $_where_trans = "";
        $_where_date = "";
        $pdo = new Database();
	    $db = $pdo->connect_db();

        // only if transaction field param exist
        if(!empty($_param['i-quotation-num']))
        {
            $_where_trans = "AND ( th.trans_code LIKE ('%".$_param['i-quotation-num']."%') ) ";
            
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
            WHERE th.is_void = 0
            AND th.prefix = (SELECT prefix FROM t_prefix WHERE uid = 3) 
            ".$_where_date.$_where_trans.";
        ";
        $q = $db->prepare($sql);
 
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
     * Quotation GET Request
     * quotations-get-by-code
     * 
     * To get single quotations record
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
                th.trans_code as 'quotation',
                th.create_date as 'date',
                th.employee_code as 'employee_code',
                th.modify_date as 'modifydate',
                tt.pm_code as 'paymentmethod',
                tpm.payment_method as 'paymentmethodname',
                th.prefix as 'prefix',
                th.remark as 'remark',
                th.shop_code as 'shopcode',
                ts.name as 'shopname',
                th.cust_code as 'cust_code',
                tc.name as 'cust_name', 
                th.is_convert as 'is_convert', 
                th.total as 'total'
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
            WHERE td.trans_code = '".$_trans_code."';
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
     * Quotation GET Request
     * quotations-find-latest-record
     * 
     * To get single quotations record
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
        tc.name as `customer`, 
        ts.name as `shop_name`, 
        ts.shop_code 
        FROM `t_transaction_h` as th 
        LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code 
        LEFT JOIN `t_customers` as tc ON th.cust_code = tc.cust_code 
        LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code 
        LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code 
        WHERE th.cust_code = '".$cust_code."' AND th.prefix = 'QTA' ORDER BY `create_date` DESC;
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
     * Quotation GET Request
     * quotations-find-item-record
     */
    $this->get('/getinfo/cust/{cust_code}', function (Request $request, Response $response, array $args) {
        $_callback = [];
        $_err = [];
        $_res = [];

        $cust_code = $args['cust_code'];

        $pdo = new Database();
        $db = $pdo->connect_db();
        
        $sql = "
        SELECT 
            th.prefix,
            td.trans_code, 
            th.create_date,
            td.item_code, 
            td.eng_name, 
            td.chi_name, 
            td.unit, 
            td.price, 
            td.qty 
        FROM `t_transaction_h` as th 
        LEFT JOIN `t_transaction_d` as td 
        ON th.trans_code = td.trans_code 
        WHERE th.cust_code = '".$cust_code."' 
        AND (
            th.prefix = ( SELECT prefix FROM t_prefix WHERE uid = 1 ) 
            OR th.prefix = ( SELECT prefix FROM t_prefix WHERE uid = 3) 
        )
        GROUP BY td.item_code DESC
        ORDER BY th.create_date DESC;
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
        
        return $response->withHeader('Access-Control-Allow-Origin', '*')->withJson($_callback, 200);
    });

    /**
     * Next Quotations number
     * Quotations number generator
     * 
     * To gen next Quotations number
     */
    $this->get('/getnextnum/', function (Request $request, Response $response, array $args) {
        $err[0] = "";
        $err[1] = "";
        $_data = "";
        $prefix = "QTA";
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
        $prefix = "QTA";
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
     * Quotation PATCH request
     * quotations-patch
     * @param body
     * 
     * to update input to database
     */
    $this->patch('/{trans_code}', function(Request $request, Response $response, array $args){
        $_err = [];
        $_done = false;
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_now = date('Y-m-d H:i:s');
        $_new_res = [];
        $pdo = new Database();
		$db = $pdo->connect_db();
        // POST Data here
        $body = json_decode($request->getBody(), true);
        extract($body);
        
        // To convert money format to decimal
        $total = filter_var($total,FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        $_trans_code = $args['trans_code'];
        $sql = "SELECT * FROM `t_transaction_d` WHERE trans_code = '".$_trans_code."';";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($res as $k => $v)
        {
            $_new_res[$v['item_code']] = $v["item_code"];
        }

        $db->beginTransaction();
        // transaction header
        
        $q = $db->prepare("UPDATE `t_transaction_h` SET 
            cust_code = '".$cust_code."',
            total = '".$total."', 
            employee_code = '".$employee_code."', 
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

        // disconnect DB
        $pdo->disconnect_db();
        
        if($_done)
        {
            if($_err[0][0] == "00000")
            {
                $_callback['query'] = "";
                $_callback["error"] = [
                    "code" => "00000", 
                    "message" => $_trans_code." Update Success!"
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
     * Quotations POST request
     * quotations-post
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
        // To convert money format to decimal
        $total = filter_var($total,FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        $db->beginTransaction();
        $sql = "insert into t_transaction_h (trans_code, cust_code, prefix, total, employee_code, shop_code, remark, is_void, is_convert, create_date) 
            values (
                '".$quotation."',
                '".$cust_code."',
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
    
        if(!empty($db->lastInsertId()))
        {
            foreach($items as $k => $v)
            {
                $q = $db->prepare("insert into t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, unit, price, discount, create_date)
                    values (
                        '".$quotation."',
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
                    '".$quotation."',
                    '".$paymentmethod."',
                    '".$total."',
                    '".$date."'
                );
            ");
            $tr->execute();
            $_err[2] = $tr->errorinfo();
        }
        $db->commit();
        // disconnect DB
        $pdo->disconnect_db();
        
        if($_err[0][0] = "00000")
        {
            $_callback['query'] = "";
            $_callback["error"] = [
                "code" => "00000", 
                "message" => $quotation." Insert Success!"
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
     * Quotations Delete Request
     * quotations-delete
     * 
     * To remove quotation record based on quotation code
     */
    $this->delete('/{trans_code}', function(Request $request, Response $response, array $args){
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
            $sql = "UPDATE `t_transaction_h` SET is_void = '1' WHERE trans_code = '".$_trans_code."';";
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
        if($_err[0][0] = "00000")
        {
            $_callback['query'] = "";
            $_callback["error"] = [
                "code" => "00000", 
                "message" => $_trans_code ." Deleted!"
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