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
                th.trans_code as 'dn_num',
                th.create_date as 'date',
                th.employee_code as 'employee_code',
                th.refer_code as 'refer_num',
                th.modify_date as 'modifydate',
                (SELECT ttt.pm_code FROM `t_transaction_t` as ttt WHERE ttt.trans_code = th.refer_code) as 'paymentmethod',
                (SELECT tpmm.payment_method FROM `t_transaction_t` as ttt LEFT JOIN `t_payment_method` as tpmm ON ttt.pm_code = tpmm.pm_code WHERE ttt.trans_code = th.refer_code)  as 'paymentmethodname',
                th.prefix as 'prefix',
                th.quotation_code as 'quotation',
                th.remark as 'remark',
                th.shop_code as 'shop_code',
                ts.name as 'shopname',
                th.cust_code as 'cust_code',
                tsp.name as 'cust_name', 
                tsp.delivery_addr,
                tsp.district_code,
                tsp.from_time,
                tsp.to_time,
                tsp.delivery_remark,
                th.total as 'total'
            FROM `t_transaction_h` as th
            LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code
            LEFT JOIN `t_customers` as tsp ON th.cust_code = tsp.cust_code
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
                td.pstock as 'stockonhand'
            FROM `t_transaction_d` as td
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
     * GET Operation By customer code
     * @param cust_code customer code required
     */
    $this->get('/getlast/cust/{cust_code}', function (Request $request, Response $response, array $args) {
        $_callback = [];
        $_err = [];
        $_res = [];

        $cust_code = $args['cust_code'];
        $pdo = new Database();
        $db = $pdo->connect_db();
        
        $sql = "
        SELECT 
            th.*, 
            tpm.payment_method, 
            tc.name as `customer`, 
            ts.name as `shop_name`, 
            tsp.name as 'supplier',
            ts.shop_code 
        FROM `t_transaction_h` as th 
        LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code 
        LEFT JOIN `t_customers` as tc ON th.cust_code = tc.cust_code 
        LEFT JOIN `t_suppliers` as tsp ON th.supp_code = tsp.supp_code
        LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code 
        LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code 
        INNER JOIN ( select cust_code, max(`create_date`) as MaxDate from `t_transaction_h` group by cust_code ) tm 
        ON th.cust_code = tm.cust_code AND th.`create_date` = tm.MaxDate 
        WHERE th.cust_code = '".$cust_code."';
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
     * GET Operation to get next order number
     */
    $this->get('/getnextnum/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_data = "";
        $_prefix['prefix'] = "";
        $_max = "00";
        $pdo = new Database();
        $db = $pdo->connect_db();

        $q1 = $db->prepare("
            SELECT prefix FROM `t_prefix` WHERE `uid` = '4' LIMIT 1;
        ");
        $q1->execute();
        $_err = $q1->errorinfo();
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
     * GET Operation to get prefix
     */
    $this->get('/getprefix/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $prefix = [];
        $pdo = new Database();
        $db = $pdo->connect_db();
        $q1 = $db->prepare("
            SELECT * FROM `t_prefix` WHERE `uid` = '4' LIMIT 1;
        ");
        $q1->execute();
        $_err = $q1->errorinfo();
        if($q1->rowCount() > 0)
        {
            $prefix = $q1->fetch();
        }
        // disconnect DB session
        $pdo->disconnect_db();
        $callback = [
            "query" => $prefix['prefix'],
            "error" => [
                "code" => $_err, 
                "message" => $_err
            ]
        ];
        return $response->withJson($callback, 200);
    });

    /**
     * POST Operation to Insert new DN information
     * @param body body as required
     */
    $this->post('/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_done = false;
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $pdo = new Database();
		$db = $pdo->connect_db();
        // POST Data here
        $quotation = "";
        $invoicenum = "";
        $body = json_decode($request->getBody(), true);
        extract($body);
        // Start transaction 
        $db->beginTransaction();

        // insert record to transaction_h
        $q = $db->prepare("
            INSERT INTO t_transaction_h (trans_code, cust_code ,quotation_code, refer_code, prefix, employee_code, shop_code, remark, create_date) 
            VALUES (
                '".$dn_num."',
                '".$cust_code."',
                '".$quotation."',
                '".$invoicenum."',
                '".$dn_prefix."',
                '".$employee_code."',
                '".$shopcode."',
                '".$remark."',
                '".$date."'
            );
        ");
        $q->execute();
        $_err[] = $q->errorinfo();
        // insert record to transaction_d
        if(!empty($db->lastInsertId()))
        {
            foreach($items as $k => $v)
            {
                // deduct stock base on qty
                $q = $db->prepare("
                    /* retrieve stockonhand from warehouse table */
                    SELECT `qty` FROM `t_warehouse` WHERE `item_code` = '".$v['item_code']."' LIMIT 1 INTO @_qty;
                    /* Insest product to transaction_D */
                    INSERT INTO t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, pstock, unit, create_date)
                    VALUES (
                        '".$dn_num."',
                        '".$v['item_code']."',
                        '".$v['eng_name']."' ,
                        '".$v['chi_name']."' ,
                        '-".$v['qty']."',
                        @_qty,
                        '".$v['unit']."',
                        '".$date."'
                    );
                   
                    /* update qty */
                    UPDATE 
                        `t_warehouse`
                    SET 
                        `qty` = @_qty - ".$v['qty'].",
                        `type` = 'out',
                        `modify_date` = '".$date."'
                    WHERE
                        `item_code` = '".$v['item_code']."';
                ");
                $q->execute();
                $_err[] = $q->errorinfo();
            }
            if(!empty($invoicenum))
            {
                //update invoice refer_code field as cross referenece
                $q = $db->prepare("
                    UPDATE t_transaction_h SET 
                        refer_code = '".$dn_num."',
                        modify_date = '".$date."'
                    WHERE trans_code = '".$invoicenum."';
                ");
                $q->execute();
                $_err[] = $q->errorinfo();
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
                "message" => $dn_num." Insert Success !"
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
                .$_err[2][2]
            ]; 
        }
        return $response->withJson($_callback,200);
     });
});

/**
 * Stocks
 */
$app->group('/api/v1/stocks', function () {
    /**
     * GET Operation to get all stocks data
     */
    $this->get('/', function (Request $request, Response $response, array $args) {
        $_param = array();
        $_param = $request->getQueryParams();
        //$_prefix['prefix'] = "";
        $_callback = [];
        $_err = [];
        $_query = [];
        if(!empty($_param))
        {
            $pdo = new Database();
            $db = $pdo->connect_db();
            // prefix SQL 
            // in DN, GRN, ADJ, ST


            // t_transaction_h SQL
            $q = $db->prepare("
            SELECT 
                th.*,
                tc.name as `customer`, 
                ts.name as `shop_name`,
                tsp.name as 'supplier',
                ts.shop_code
            FROM `t_transaction_h` as th 
            LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code 
            LEFT JOIN `t_customers` as tc ON th.cust_code = tc.cust_code 
            LEFT JOIN `t_suppliers` as tsp ON th.supp_code = tsp.supp_code 
            LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code
            LEFT JOIN `t_prefix` as tp ON th.prefix = tp.prefix
            WHERE tp.uid in ('4','5','6','7') AND date(th.create_date) BETWEEN '".$_param['i-start-date']."'
            AND '".$_param['i-end-date']."' OR th.trans_code = '".$_param['i-dn']."';
            ");
    
            $q->execute();
            $_err = $q->errorinfo();
            $_res = $q->fetchAll(PDO::FETCH_ASSOC);

            //disconnection DB
            $pdo->disconnect_db();

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
        }
        else
        {
            $_callback = [
                "query" => $_query,
                "error" => ["code" => "99999", "message" => "Query String Not Found!"]
            ];
            return $response->withJson($_callback, 404);
        }
    });
    /**
     * GET Operation to get stockonhand data
     * @param item_code item code required
     */
    $this->get('/onhand/{item_code}', function (Request $request, Response $response, array $args) {
        $_callback = [];
        $_err = [];
        $_item_code = $args['item_code'];
        $pdo = new Database();
        $db = $pdo->connect_db();

        // t_transaction_h SQL
        //$q = $db->prepare("SELECT * FROM `t_transaction_h` as th left join `t_transaction_t` as tt on th.trans_code = tt.trans_code WHERE th.prefix = 'INV' LIMIT 9;");
        $q = $db->prepare("
            SELECT qty FROM `t_warehouse` WHERE item_code = '".$_item_code."';
        ");

        $q->execute();
        $_err = $q->errorinfo();
        $_res = $q->fetch();

        //disconnection DB
        $pdo->disconnect_db();

        // export data
        if(!empty($_res))
        {
            if($_err[0] === "00000")
            {
                $_err[1] = "Query Successful!";
            }
            $_callback = [
                "query" => $_res,
                "error" => ["code" => $_err[0], "message" => $_err[1]." ".$_err[2]]
            ];
            return $response->withJson($_callback, 200);
        }
      
    });
});

/**
 * Stocks -> Good Recevied Note
 */
$app->group('/api/v1/stocks/po/grn', function () {
    /**
     * GRN GET request 
     * GRN-get-by-code
     * 
     * Get GRN by trans_code
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
                th.trans_code as 'grn_num',
                th.create_date as 'date',
                th.employee_code as 'employee_code',
                th.refer_code as 'refer_num',
                th.modify_date as 'modifydate',
                tt.pm_code as 'paymentmethod',
                tpm.payment_method as 'paymentmethodname',
                th.prefix as 'prefix',
                th.quotation_code as 'quotation',
                th.remark as 'remark',
                th.shop_code as 'shop_code',
                ts.name as 'shopname',
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
    
    /*
    * Get Next delivery note number 
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
            SELECT prefix FROM `t_prefix` WHERE `uid` = '5' LIMIT 1;
        ");
        $q1->execute();
        $_err = $q1->errorinfo();
        if($q1->rowCount() != "0")
        {
            $_prefix = $q1->fetch();
        }
        // get next number SQL
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
            SELECT prefix FROM `t_prefix` WHERE `uid` = '5' LIMIT 1;
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
     * Goods received POST request
     *
     * grn-post
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
        $q = $db->prepare("
            INSERT INTO t_transaction_h (trans_code, prefix, employee_code, refer_code, supp_code, shop_code, total, remark, create_date) 
            VALUES (
                '".$grn_num."',
                '".$prefix."',
                '".$employee_code."',
                '".$po_num."',
                '".$supp_code."',
                '".$shop_code."',
                '".$total."',
                '".$remark."',
                '".$date."'
            );
        ");
        $q->execute();
        $_err[] = $q->errorinfo();
        // insert record to transaction_d
        if(!empty($db->lastInsertId()))
        {
            foreach($items as $k => $v)
            {
                // add stock
                $q = $db->prepare("
                    /* retrieve stockonhand from warehouse table */
                    SELECT `qty` FROM `t_warehouse` WHERE `item_code` = '".$v['item_code']."' LIMIT 1 INTO @_qty;
                    /* insert items to transaction_d */
                    INSERT INTO t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, pstock, unit, price, create_date)
                    VALUES (
                        '".$grn_num."',
                        '".$v['item_code']."',
                        '".$v['eng_name']."' ,
                        '".$v['chi_name']."' ,
                        '+".$v['qty']."',
                        '".$v['stockonhand']."',
                        '".$v['unit']."',
                        '".$v['price']."',
                        '".$date."'
                    );

                    /* update warehouse stockonhand */
                    UPDATE 
                        `t_warehouse`
                    SET 
                        `qty` = @_qty + ".$v['qty'].",
                        `type` = 'in',
                        `modify_date` = '".$date."'
                    WHERE
                        `item_code` = '".$v['item_code']."';
                ");
                $q->execute();
                $_err[] = $q->errorinfo();
            }
            // insert record to transaction tender
            $q = $db->prepare("
                INSERT INTO t_transaction_t (trans_code, pm_code, total, create_date) 
                VALUES (
                    '".$grn_num."',
                    '".$paymentmethod."',
                    '".$total."',
                    '".$date."'
                );
            ");
            $q->execute();
        }
        $db->commit();
        
        //disconnection DB
        $pdo->disconnect_db();
        
        if($_err[0][0] = "00000")
        {
            $_callback['query'] = "";
            $_callback["error"] = [
                "code" => "00000", 
                "message" => $grn_num . " Insert Success !"
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
                .$_err[2][2]
            ]; 
        }
        return $response->withJson($_callback,201);
    });
});

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
                th.trans_code as 'adj_num',
                th.create_date as 'date',
                th.employee_code as 'employee_code',
                th.modify_date as 'modifydate',
                th.refer_code as 'refer_num', 
                th.prefix as 'prefix',
                th.quotation_code as 'quotation',
                th.remark as 'remark',
                th.shop_code as 'shop_code',
                ts.name as 'shopname'
            FROM `t_transaction_h` as th
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
                td.pstock as 'stockonhand'
            FROM `t_transaction_d` as td
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

    /*
    * Get Next delivery note number 
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
            SELECT prefix FROM `t_prefix` WHERE `uid` = '6' LIMIT 1;
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
            SELECT prefix FROM `t_prefix` WHERE `uid` = '6' LIMIT 1;
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
     * POST Create new adjustment transaction
     * 
     */
    $this->post('/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_done = false;
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $pdo = new Database();
		$db = $pdo->connect_db();
        $body = json_decode($request->getBody(), true);
        
        extract($body);
        
        // Start transaction 
        $db->beginTransaction();

        // insert record to transaction_h
        $q = $db->prepare("
            INSERT INTO t_transaction_h (trans_code, refer_code, prefix, employee_code, shop_code, remark, create_date) 
            VALUES (
                '".$adj_num."',
                '".$refer_num."',
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
                if($v['qty'] > 0)
                {
                    $v['qty'] = "+".intval($v['qty']);
                }
                // deduct stock base on qty
                $q = $db->prepare("
                    INSERT INTO t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, pstock, unit, create_date)
                    VALUES (
                        '".$adj_num."',
                        '".$v['item_code']."',
                        '".$v['eng_name']."' ,
                        '".$v['chi_name']."' ,
                        '".$v['qty']."',
                        '".$v['stockonhand']."',
                        '".$v['unit']."',
                        '".$date."'
                    );
                ");
                $q->execute();
                $_err[1][] = $q->errorinfo();

                $q = $db->prepare("
                    SET @qty := (SELECT `qty` FROM `t_warehouse` WHERE `item_code` = '".$v['item_code']."') + ".$v['qty'].";
                ");
                $q->execute();
                $_err[2][] = $q->errorinfo();
            
                $q = $db->prepare("
                    UPDATE 
                        `t_warehouse`
                    SET 
                        `qty` = @qty,
                        `modify_date` = '".$date."' 
                    WHERE `item_code` = '".$v['item_code']."';
                ");
                $q->execute();
                $_err[3][] = $q->errorinfo();
            }
        }
        
        $db->commit();
        //disconnection DB
        $pdo->disconnect_db();
        
        if($_err[0][0] = "00000" && $_err[1][0] = "00000" && $_err[2][0] = "00000" && $_err[3][0] = "00000")
        {
            $_callback['query'] = "";
            $_callback["error"] = [
                "code" => "00000", 
                "message" => $adj_num." Insert Success !"
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
                .$_err[2][2]." - "
                .$_err[3][2]
            ]; 
            return $response->withJson($_callback,404);
        }
    });
});

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
                '".$num."',
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
                        '".$num."',
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
                "message" => $num." Insert Success !"
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