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
        //$_prefix['prefix'] = "";
        $_callback = [];
        $_err = [];
        $_query = [];
        $_where_trans = "";
        $_where_date = "";
        if(!empty($_param))
        {
            $pdo = new Database();
            $db = $pdo->connect_db();
            // prefix SQL 
            // in DN, GRN, ADJ, ST
            
            // only if transaction field param exist
            if(!empty($_param['i-num']))
            {
                $_where_trans = "AND (th.trans_code LIKE ('%".$_param['i-num']."%') OR th.refer_code LIKE ('%".$_param['i-num']."%')) ";
            }
            // otherwise follow date range as default
            else
            {
                $_where_date = "AND (date(th.create_date) BETWEEN '".$_param['i-start-date']."' AND '".$_param['i-end-date']."') ";
            }

            // t_transaction_h SQL
            $q = $db->prepare("
                SELECT 
                    th.*,
                    tc.name as `customer`, 
                    ts.name as `shop_name`,
                    tsp.name as 'supplier',
                    tpm.payment_method as 'payment_method',
                    ts.shop_code
                FROM `t_transaction_h` as th 
                LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code 
                LEFT JOIN `t_customers` as tc ON th.cust_code = tc.cust_code 
                LEFT JOIN `t_suppliers` as tsp ON th.supp_code = tsp.supp_code
                LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code
                LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code
                LEFT JOIN `t_prefix` as tp ON th.prefix = tp.prefix
                WHERE tp.uid = '2' ". $_where_date . $_where_trans.";
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
     * GET Request 
     * To PO record 
     * @param trans_code
     */
    $this->get('/{trans_code}', function (Request $request, Response $response, array $args) {
        // inital variable
        $_callback = [];
        $_query = [];
        $_callback['has'] = false;
        $_trans_code= $args['trans_code'];
        $_err = [];
        $_customers = [];
        $_settlement = 0;
    
        $pdo = new Database();
		$db = $pdo->connect_db();
        $sql = "
            SELECT
                (SELECT COUNT(*) FROM `t_transaction_h` WHERE refer_code = '".$_trans_code."') as `has_grn`,
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
                th.total as 'total',
                th.is_convert as 'is_settle'
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
        $sql3 = "
            SELECT 
                sum(td.qty) as 'po_items'
            FROM `t_transaction_d` as td 
            LEFT JOIN `t_warehouse` as tw ON td.item_code = tw.item_code
            WHERE trans_code = '".$_trans_code."';
        ";
        $sql4 = "
            SELECT
                sum(td.qty) as 'grn_items'
            FROM `t_transaction_h` as th 
            LEFT JOIN `t_transaction_d` as td ON th.trans_code = td.trans_code
            WHERE th.refer_code = '".$_trans_code."';
        ";
    
        // execute SQL Statement 1
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $head = $q->fetchAll(PDO::FETCH_ASSOC);
        // execute SQL statement 2
        $q = $db->prepare($sql2);
        $q->execute();
        $_err[] = $q->errorinfo();
        $po_items = $q->fetchAll(PDO::FETCH_ASSOC);
        $q = $db->prepare($sql3);
        $q->execute();
        $_err[] = $q->errorinfo();
        $res3 = $q->fetch();
        $q = $db->prepare($sql4);
        $q->execute();
        $_err[] = $q->errorinfo();
        $res4 = $q->fetch();

        //disconnection DB
        $pdo->disconnect_db();        

        // export data
        if(!empty($head))
        {
            $_query = $head[0];
            $_settlement = ($res3['po_items'] - $res4['grn_items']);
            foreach ($po_items as $key => $val) {
                 $_query["items"] = $po_items;
            }
            if($_settlement === 0)
            {
                $_query["settlement"] = true; 
            }
            else
            {
                $_query["settlement"] = false; 
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
            $_callback["error"]["code"] = $_err;
            $_callback["error"]["message"] = $_err;
            return $response->withJson($_callback, 200);
        }
        else
        {
            $_callback['query'] = "";
            $_callback['has'] = false;
            $_callback["error"]["code"] = "99999";
            $_callback["error"]["message"] = "Item not found";
            return $response->withJson($_callback, 404);
        }
    });

    /**
     * GET Request
     * To gen next purchase number
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
            SELECT prefix FROM `t_prefix` WHERE `uid` = '2' LIMIT 1;
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
     * Get Request
     * To get PO prefix
     */
    $this->get('/getprefix/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_prefix['prefix'] = "";
        $pdo = new Database();
        $db = $pdo->connect_db();
        // prefix SQL
        $q1 = $db->prepare("
            SELECT prefix FROM `t_prefix` WHERE `uid` = '2' LIMIT 1;
        ");
        $q1->execute();
        $_err[] = $q1->errorinfo();
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
     * GET search supplier
     * To search latest transaction by supplier
     * @param supp_code supplier code
     */
    $this->get('/getlast/supp/{supp_code}', function (Request $request, Response $response, array $args) {
        $_callback = [];
        $_err = [];
        $_res = [];

        $supp_code = $args['supp_code'];
        $pdo = new Database();
        $db = $pdo->connect_db();
        
        $sql = "
        SELECT 
            th.*, 
            tpm.payment_method as `payment_method`, 
            tsp.name as `supplier`, 
            ts.name as `shop_name`, 
            ts.shop_code
        FROM `t_transaction_h` as th 
        LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code 
        LEFT JOIN `t_suppliers` as tsp ON th.supp_code = tsp.supp_code 
        LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code 
        LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code 
        LEFT JOIN `t_prefix` as tp ON th.prefix = tp.prefix
        WHERE th.supp_code = '".$supp_code."' AND tp.uid = '2';
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
     * GET remain item count via GRN
     * @param refer_code reference code
     */
    $this->get('/getgrn/po/{refer_code}', function (Request $request, Response $response, array $args) {
        // inital variable
        $_grn_combo = [];
        $_callback = [];
        $_temp = [];
        $_query = [];
        $_callback['has'] = false;
        $_refer_code = $args['refer_code'];
        $_err = [];
        $_counter = 0;
        $_counter1 = 0;
        // retrieve GRN items SQL statement
        $pdo = new Database();
		$db = $pdo->connect_db();
        $sql = "
            SELECT
                (SELECT COUNT(*) FROM `t_transaction_h` WHERE refer_code = '".$_refer_code."') as `has_grn`,
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
            WHERE th.trans_code = '".$_refer_code."';
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
            FROM `t_transaction_h` as th 
            LEFT JOIN `t_transaction_d` as td ON th.trans_code = td.trans_code 
            LEFT JOIN `t_warehouse` as tw ON td.item_code = tw.item_code
            WHERE th.`trans_code` = '".$_refer_code."' 
            AND th.`prefix` = (select prefix from t_prefix where uid = 2);
        ";
        $sql3 = "
            SELECT 
                td.item_code,
                td.qty      
            FROM `t_transaction_h` as th 
            LEFT JOIN `t_transaction_d` as td ON th.trans_code = td.trans_code
            WHERE th.`refer_code` = '".$_refer_code."' 
            AND th.`prefix` = (select prefix from t_prefix where uid = 5);
        ";
        // retrieve PO items SQL statement
        $q = $db->prepare($sql3);
        $q->execute();
        $_err[] = $q->errorinfo();
        $grn_items = $q->fetchAll(PDO::FETCH_ASSOC);
        // execute SQL Statement 1
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $head = $q->fetchAll(PDO::FETCH_ASSOC);
        // execute SQL statement 2
        $q = $db->prepare($sql2);
        $q->execute();
        $_err[] = $q->errorinfo();
        $po_items = $q->fetchAll(PDO::FETCH_ASSOC);

        //disconnection DB
        $pdo->disconnect_db();
        
        // found po on transaction header and has grn record
        if(!empty($head))
        {
            $_query = $head[0];
            $_query["settlement"] = false;
            
            // create item template
            foreach($grn_items as $k => $v)
            {
                $_grn_combo[$v['item_code']] = 0;
            }
            // addition same item qty
            foreach($grn_items as $k => $v)
            {
      
                $_grn_combo[$v['item_code']] += intval($v['qty']);
            }
            
            foreach($po_items as $k => $v)
            {  
                $_counter += intval($v['qty']);
            }
            
            // subtrack same item qty to get remainer
            foreach($po_items as $k => $v)
            {  
                foreach($_grn_combo as $ik => $iv)
                {    
                    if( $v['item_code'] === $ik)
                    {
                        $po_items[$k]['qty'] = (intval($v['qty']) - $iv);
                        $_counter1 += $iv;

                    }
                }
            }
            
            
            // check remain item on list
            $_counter = $_counter - $_counter1;
            //var_dump($_counter);
            if($_counter === 0)
            {
                $_query["settlement"] = true;
            }
        
            foreach ($po_items as $key => $val) {
                $_query["items"] = $po_items;
            }

            // filter 0 qty items on list
			foreach($_query['items'] as $k => $v)
			{
				if($v['qty'] != 0)
				{
					$_temp[] = $_query['items'][$k];
				}
			}
            $_query['items'] = $_temp;
            
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
            $_callback["error"]["code"] = $_err;
            $_callback["error"]["message"] = $_err;
            return $response->withJson($_callback, 200);
        }
        else
        {
            $_callback['query'] = "";
            $_callback['has'] = false;
            $_callback["error"]["code"] = "99999";
            $_callback["error"]["message"] = "Item not found";
            return $response->withJson($_callback, 404);
        }
    });

    /**
     * GET settlement info 
     */
    $this->get('/settlement/po/{refer_code}',function(Request $request, Response $response, array $args){
        $_refer_code = $args['refer_code'];
        $_err = [];
        $_callback = [];
        $_query = [];

        $pdo = new Database();
		$db = $pdo->connect_db();
        $sql = "
            SELECT 
                tt.trans_code,
                tt.pm_code,
                tt.total
            FROM `t_transaction_h` as th 
            LEFT JOIN t_transaction_t as tt 
            ON th.trans_code = tt.trans_code 
            WHERE th.refer_code = '".$_refer_code."';
        ";
        $q = $db->prepare($sql);
        $q->execute();
        $_err = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);

        $sql1 = "
            SELECT 
                sum(tt.total) as total
            FROM `t_transaction_h` as th 
            LEFT JOIN t_transaction_t as tt 
            ON th.trans_code = tt.trans_code 
            WHERE th.refer_code = '".$_refer_code."';
        ";
        $q = $db->prepare($sql1);
        $q->execute();
        $_err = $q->errorinfo();
        $total = $q->fetch();

        //disconnection DB
        $pdo->disconnect_db();

        if(!empty($res))
        {
            $_callback['query']['all_grn'] = $res;
            $_callback['query']['total'] = $total['total'];
            $_callback["error"]["code"] = $_err[0];
            $_callback["error"]["message"] = $_err[1];
            return $response->withJson($_callback, 200);
        }
        else
        {
            $_callback['query'] = "";
            $_callback["error"]["code"] = "99999";
            $_callback["error"]["message"] = "Record not found!";
            return $response->withJson($_callback, 404);
        }
       
    });

    /**
     * Check transaction_d item exist API
     */
    $this->group('/transaction/h',function(){
        /**
         * Transaction H GET Request
         * Count number of mothly invoice
         * @param queryparam start date
         * @param queryparam end date
         */
        $this->get('/count/', function (Request $request, Response $response, array $args) {
            //$_prefix = $args['prefix'];
            $_param = $request->getQueryParams();
            if(empty($_param["month"])) $_param["month"] = "";
            if(empty($_param["year"])) $_param["year"] = "";

            $_callback = [];
            $_err = [];
            $_query = [];
            $pdo = new Database();
            $db = $pdo->connect_db();
            
            $q = $db->prepare("
            SELECT 
                count(*) as count,
                sum(total) as expand
            FROM 
                `t_transaction_h` as th
            WHERE th.is_void = 0 AND th.prefix = (SELECT prefix FROM `t_prefix` WHERE `uid` = '2') AND month(th.create_date) = '".$_param['month']."'
            AND year(th.create_date) = '".$_param['year']."';
            ");

            $q->execute();
            $_err = $q->errorinfo();
            $_res = $q->fetch(PDO::FETCH_ASSOC);
        
            // export data

            // foreach ($_res as $key => $val) {
            //     $_query[] = $val;
            // }
            $_callback = [
                "query" => $_res,
                "error" => ["code" => $_err[0], "message" => $_err[1]." ".$_err[2]]
            ];
            return $response->withJson($_callback, 200);
       
        });
    });

    /**
     * Edit PO record
     * to finish settlement
     * @param trans_code po number
     */
    $this->patch('/settlement/po/{trans_code}', function (Request $request, Response $response, array $args){
        $_err = [];
		$_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_now = date('Y-m-d H:i:s');
        $_trans_code = $args['trans_code'];
        $_remark = "";
        $_body = json_decode($request->getBody(), true);
        foreach($_body as $k => $v)
        {
            $_remark .= $v['trans_code'];
            if($k < (count($_body)-1)){
                $_remark .= ",";
            }
        }
        
        $pdo = new Database();
		$db = $pdo->connect_db();

        // transaction header
        $q = $db->prepare("UPDATE `t_transaction_h` SET 
            is_convert = 1,
            remark = 'Settled\nGRN: ".$_remark."', 
            modify_date =  '".$_now."'
            WHERE trans_code = '".$_trans_code."';"
        );
        $q->execute();
        $_err = $q->errorinfo();
    
        //disconnection DB
        $pdo->disconnect_db();

        // finish up the flow
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
                "message" => $_err
            ]; 
        }
        return $response->withJson($_callback,200);
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
        $_trans_code = $args['trans_code'];

        $pdo = new Database();
		$db = $pdo->connect_db();
        //$sql_d = "";
        // POST Data here
        $body = json_decode($request->getBody(), true);
        extract($body);
        // To convert money format to decimal
        $total = filter_var($total,FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    
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
            $sql = "UPDATE `t_transaction_h` SET 
            `supp_code` = '".$supp_code."',
            `total` = '".$total."',  
            `shop_code` = '".$shopcode."', 
            `remark` = '".$remark."', 
            `modify_date` =  '".$_now."'
            WHERE `trans_code` = '".$_trans_code."';";
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            
            foreach($_new_res as $k_itcode => $v)
            {
                // delete items from this transaction
                if(!array_key_exists($k_itcode,$items))
                {

                    $sql_d = "DELETE FROM `t_transaction_d` WHERE trans_code = '".$_trans_code."' AND item_code = '".$k_itcode."';";
                    $q = $db->prepare($sql_d);
                    $q->execute();
                    $_err[] = $q->errorinfo();
                }
            }
            foreach($items as $k_itcode => $v)
            {
                // update item already in transaction
                if(array_key_exists($k_itcode,$_new_res))
                {

                    $sql = "UPDATE `t_transaction_d` SET
                        qty = '".$v['qty']."',
                        unit = '".$v['unit']."',
                        price = '".$v['price']."',
                        modify_date = '".$_now."'
                        WHERE trans_code = '".$_trans_code."' AND item_code = '".$k_itcode."';";
                    //echo $sql."\n";
                    $q = $db->prepare($sql);
                    $q->execute();
                    $_err[] = $q->errorinfo();
                }
                // New add items
                else
                {
                    $sql = "insert into t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, unit, price, create_date)
                        values (
                            '".$_trans_code."',
                            '".$v['item_code']."',
                            '".$v['eng_name']."' ,
                            '".$v['chi_name']."' ,
                            '".$v['qty']."',
                            '".$v['unit']."',
                            '".$v['price']."',
                            '".$date."'
                        );";
                    //echo $sql."\n";
                    $q = $db->prepare($sql);
                    $q->execute();
                    $_err[] = $q->errorinfo();
                }   
            }

            // tender information input here
            $sql = "UPDATE `t_transaction_t` SET 
                `pm_code` = '".$paymentmethod."',
                `total` = '".$total."',
                `modify_date` = '".$_now."'
                WHERE `trans_code` = '".$_trans_code."';";
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();

            $db->commit();
            $_done = true;
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
                    "message" => $_err
                ]; 
            }
        }
        return $response->withJson($_callback,200);
    });

    /**
     * POST request
     * To insert new PO record
     * @param body
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
        $sql = "insert into t_transaction_h (trans_code, refer_code, supp_code, prefix, total, employee_code, shop_code, remark, is_void, is_convert, create_date) 
            values (
                '".$purchasesnum."',
                '".$refernum."',
                '".$supp_code."',
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
                $q = $db->prepare("insert into t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, unit, price, create_date)
                    values (
                        '".$purchasesnum."',
                        '".$v['item_code']."',
                        '".$v['eng_name']."' ,
                        '".$v['chi_name']."' ,
                        '".$v['qty']."',
                        '".$v['unit']."',
                        '".$v['price']."',
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
     * To delete PO record
     * @param body
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