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
                th.shop_code as 'shopcode',
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
       
        $q = $db->prepare("
            INSERT INTO t_transaction_h (trans_code, cust_code, quotation_code, refer_code, prefix, employee_code, shop_code, remark, create_date) 
            VALUES (
                '".$dn_num."',
                '".$cust_code."',
                '".$quotation."',
                '".$refer_num."',
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
        foreach($items as $k => $v)
        {
            // deduct stock base on qty
            $q = $db->prepare("
                SELECT `qty` FROM `t_warehouse` WHERE `item_code` = '".$v['item_code']."' LIMIT 1 INTO @_qty;

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
        if(!empty($trans_code))
        {
            //update invoice refer_code field as cross referenece
            $q = $db->prepare("
                UPDATE t_transaction_h SET 
                    refer_code = '".$dn_num."',
                    modify_date = '".$date."'
                WHERE trans_code = '".$trans_code."';
            ");
            $q->execute();
            $_err[] = $q->errorinfo();
        }
        
        $db->commit();
        //disconnection DB
        $pdo->disconnect_db();
        
        if($_err[0][0] = "00000")
        {
            $_callback['query'] = "";
            $_callback["error"] = [
                "code" => "00000", 
                "message" => $dn_num." Insert Success!"
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
