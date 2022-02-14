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
        // To convert money format to decimal
        $total = filter_var($total,FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        // Start transaction 
        $db->beginTransaction();

        // insert record to transaction_h
        $sql = "
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
        ";

        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        // insert record to transaction_d
        if(!empty($db->lastInsertId()))
        {
            foreach($items as $k => $v)
            {
                // add stock

                $sql = "/* retrieve stockonhand from warehouse table */
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
                    `item_code` = '".$v['item_code']."';";

                $q = $db->prepare($sql);
                $q->execute();
                $_err[] = $q->errorinfo();
                
            }
            // insert record to transaction tender
            $sql = "
                INSERT INTO t_transaction_t (trans_code, pm_code, total, create_date) 
                VALUES (
                    '".$grn_num."',
                    '".$paymentmethod."',
                    '".$total."',
                    '".$date."'
                );
            ";

            $q = $db->prepare($sql);
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