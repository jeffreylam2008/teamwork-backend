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
        $_callback = [
            'query' => "",
            'error' => [
                "code" => "", 
                "message" => ""
            ]];
        $_err = [];
        $_pm = [];
        $_cust = [];
        $_query = [];
        $pdo = new Database();
		$db = $pdo->connect_db();

        // t_transaction_h SQL
        $q = $db->prepare("SELECT * FROM `t_transaction_h` as th LEFT JOIN `t_transaction_t` as tt on th.trans_code = tt.trans_code WHERE th.prefix = 'QTA';");
        $q->execute();
        $_err[0] = $q->errorinfo();
        $_res = $q->fetchAll(PDO::FETCH_ASSOC);
    
        // t_payment_method SQL
        $q = $db->prepare("SELECT pm_code, payment_method FROM `t_payment_method`;");
        $q->execute();
        $_err[1] = $q->errorinfo();
        $_res2 = $q->fetchAll(PDO::FETCH_ASSOC);
        
        // t_customer SQL
        $q = $db->prepare("SELECT * FROM `t_customers`;");
        $q->execute();
        $_err[2] = $q->errorinfo();
        $_res3 = $q->fetchAll(PDO::FETCH_ASSOC);
    
        // t_shop SQL
        $q = $db->prepare("SELECT * FROM `t_shop`;");
        $q->execute();
        $_err[3] = $q->errorinfo();
        $_res4 = $q->fetchAll(PDO::FETCH_ASSOC);
        // disconnect DB
        $pdo->disconnect_db();
        
        // convert payment_method to key and value array
        foreach($_res2 as $k => $v)
        {  
            extract($v);
            $_pm[$pm_code] = $payment_method;
        }
        // convert customer to key and value array
        foreach($_res3 as $k => $v)
        {
            extract($v);
            $_cust[$cust_code] = $v;
        }
        // convert Shop to key and value array
        foreach ($_res4 as $k => $v) {
            extract($v);
            $_shops[$shop_code] = $v;
        }
        // Map payment_method to array
        foreach($_res as $k => $v)
        {
            if(array_key_exists($v['pm_code'],$_pm))
            {
                $_res[$k]['payment_method'] = $_pm[$v['pm_code']];
            }
            if(array_key_exists($v['cust_code'], $_cust))
            {
                $_res[$k]['customer'] = $_cust[$v['cust_code']]['name'];
            }
            if(array_key_exists($v['shop_code'], $_shops))
            {
                $_res[$k]['shop_name'] = $_shops[$v['shop_code']]['name'];
            }
            $_res[$k]['is_convert'] === 1 ? $_res[$k]['is_convert'] = "Yes" : $_res[$k]['is_convert'] = "No";
        }

        // export data
        if(!empty($_res))
        {
            //consolidated other information to one array
            foreach ($_res as $key => $val) {
                $_query[] = $val;
            }
            if($_err[0][0] == "00000")
            {
                $_callback["query"] = $_query;
                $_callback["error"] = [
                    "code" => "00000", 
                    "message" => "Query Success!"
                ];
            }
            else
            {
                $_callback["query"] = "";
                $_callback["error"] = [
                    "code" => "99999", 
                    "message" => $_err[0][2]."-".$_err[1][2]."-".$_err[2][2]."-".$_err[3][2]
                ];
            }
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
        $_callback = [
            'query' => "",
            'has' => false,
            'error' => [
                "code" => "", 
                "message" => ""
            ]];
		//$_final_err = [];
        $_query = [];
        $_callback = ['query' => "" ,'has' => false, 'error' => ["code" => "", "message" => ""]];
        $_trans_code= $args['trans_code'];
        $_err = [];

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
                th.prefix as 'prefix',
                th.remark as 'remark',
                th.shop_code as 'shopcode',
                th.cust_code as 'cust_code',
                th.total as 'total',
                th.is_convert as 'is_convert'
            FROM `t_transaction_h` as th
            left join `t_transaction_t` as tt on th.trans_code = tt.trans_code WHERE th.trans_code = '".$_trans_code."';
        ";
        $sql2 = "
            SELECT 
                item_code,
                eng_name,
                chi_name,
                qty,
                unit,
                price,
                discount
            FROM `t_transaction_d` WHERE trans_code = '".$_trans_code."';
        ";
        $sql3 = "
            SELECT * FROM `t_customers`;
        ";
        // execute SQL Statement 1
        $q = $db->prepare($sql);
        $q->execute();
        $_err[0] = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        // execute SQL statement 2
        $q = $db->prepare($sql2);
        $q->execute();
        $_err[1] = $q->errorinfo();
        $res2 = $q->fetchAll(PDO::FETCH_ASSOC);
        // execute SQL statement 3
        $q = $db->prepare($sql3);
        $q->execute();
        $_err[2] = $q->errorinfo();
        $res3 = $q->fetchAll(PDO::FETCH_ASSOC);
        // disconnect DB
        $pdo->disconnect_db();
        
        if($_err[0][0] == "00000")
        {
            // export data
            $_query = $res[0];        
            foreach ($res2 as $key => $val) {
                $_query["items"] = $res2;
            }
            // Get customer data from DB
            foreach($res3 as $k => $v)
            {
                extract($v);
                $_customers[$cust_code] = $v;
            }
            // customer data marge
            if(array_key_exists($_query['cust_code'], $_customers))
            {
                $_query['customer'] = [
                    "cust_code" => $_query['cust_code'],
                    "name" => $_customers[$_query['cust_code']]['name']
                ];
            }
            // calcuate subtotal
            foreach($_query["items"] as $k => $v)
            {
                extract($v);
                $_query["items"][$k]["subtotal"] = number_format(($qty * $price),2);
            }
            //var_dump($_query);
            $_callback['query'] = $_query;
            $_callback['has'] = true;
            $_callback["error"] = [
                "code" => $_err[0][0], 
                "message" => "Query Success!"
            ]; 
        }
        else
        { 
            $_callback['query'] = "";
            $_callback['has'] = false;
            $_callback["error"] = [
                "code" => "99999", 
                "message" => "DB Error: ".$_err[0][2]." - ".$_err[1][2]." - ".$_err[2][2]
            ]; 
        }
        return $response->withJson($_callback, 200);

    });
    
    /** 
     * Quotation PATCH request
     * quotations-patch
     * @param body
     * 
     * to update input to database
     */
    $this->patch('/{trans_code}', function(Request $request, Response $response, array $args)
    {
        $_err = [];
        $_done = false;
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_now = date('Y-m-d H:i:s');
        $_new_res = "";
        $pdo = new Database();
		$db = $pdo->connect_db();
        // POST Data here
        $body = json_decode($request->getBody(), true);
        extract($body);
        
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
            cust_code = '".$customer['cust_code']."',
            total = '".$total."', 
            employee_code = '".$employeecode."', 
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
                            '',
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
                    "message" => "Update Success!"
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
        
        $db->beginTransaction();
    
        $sql = "insert into t_transaction_h (trans_code, cust_code ,quotation_code, prefix, total, employee_code, shop_code, remark, is_void, is_convert, create_date) 
            values (
                '".$quotation."',
                '".$customer['cust_code']."',
                '',
                '".$prefix."',
                '".$total."',
                '".$employeecode."',
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
                        '',
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
                "message" => "Insert Success!"
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
        return $response->withJson($_trans_code,200);
    });
    
    /**
     * Check transaction_d item exist
     */
    // $this->group('/transaction/d',function(){
    //     $this->get('/{item_code}', function (Request $request, Response $response, array $args) {
    //         $item_code = $args['item_code'];
    //         $db = connect_db();
    //         $sql = "SELECT * FROM `t_transaction_d` where item_code = '". $item_code ."';";
        
    //         $q = $db->prepare($sql);
    //         $q->execute();
    //         $dbData = $q->fetch();
    //         $err = $q->errorinfo();
        
    //         $callback = [
    //             "query" => $dbData,
    //             "error" => ["code" => $err[0], "message" => $err[2]]
    //         ];
        
    //         return $response->withJson($callback, 200);
    //     });
    //     $this->post('/', function (Request $request, Response $response, array $args) {
           
    //     });
    // });
});