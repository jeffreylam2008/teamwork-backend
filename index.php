<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once './vendor/autoload.php';
require_once './lib/db.php';
$c = new \Slim\Container(); //Create Your container

//Override the default Not Found Handler
$c['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $data = [ 
            "message" => "param not define" 
        ];
        return $c['response']->withJson($data,404);
    };
};
$app = new \Slim\App($c);

/**
 * Categories API
 */
$app->group('/api/v1/products/categories', function () {
    /**
     * Categories GET Request
     * categories-get
     * 
     * To get all record
     */
    $this->get('/', function (Request $request, Response $response, array $args) {
        $err="";
        $db = connect_db();
        $q = $db->prepare("SELECT * FROM `t_items_category` ORDER BY 'cate_code';");
        $q->execute();
        $err = $q->errorinfo();
        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $key => $val) {
            $dbData[] = $val;
        }
        $callback = [
            "query" => $dbData,
            "error" => ["code" => $err[0], "message" => $err[2]]
        ];
    
        if($err[0] == "00000")
            return $response->withJson($callback, 200);
        
    });
    /**
     * Categories GET Request
     * categories-get-by-code
     * 
     * To get single record base on code
     */
    $this->get('/{cate_code}', function(Request $request, Response $response, array $args){
        $err="";
        $_cate_code = $args['cate_code'];
        $db = connect_db();
    
        $q = $db->prepare("select * from `t_items_category` WHERE cate_code = '".$_cate_code."';");
        $q->execute();
        $dbData = $q->fetch();
        $err = $q->errorinfo();
    
        $callback = [
            "query" => $dbData,
            "error" => ["code" => $err[0], "message" => $err[2]]
        ];
    
        return $response->withJson($callback, 200);
    });
    /**
     * Categories PATCH Request
     * categories-patch
     * 
     * To update current record on DB
     */
    $this->patch('/{cate_code}', function(Request $request, Response $response, array $args){
        $err = [];
        $_cate_code = $args['cate_code'];
        $db = connect_db();
        // POST Data here
        $body = json_decode($request->getBody(), true);
        $_now = date('Y-m-d H:i:s');
        $db->beginTransaction();
        $q = $db->prepare("UPDATE t_items_category SET `cate_code` = '".$body["i-catecode"]."', `desc` = '".$body["i-desc"]."', `create_date` = '".$_now."' WHERE `cate_code` = '".$_cate_code."';");
        $q->execute();
        $dbData = $q->fetch();
        $err = $q->errorinfo();
        $db->commit();
        $callback = [
            "query" => $dbData,
            "error" => ["code" => $err[0], "message" => $err[2]]
        ];
        return $response->withJson($callback,200);
        
    });
    /**
     * Categories POST Request
     * categories-post
     * 
     * To create new record
     */
    $this->post('/', function(Request $request, Response $response, array $args){
        $err = [];
        $db = connect_db();
        // POST Data here
        $body = json_decode($request->getBody(), true);
        $_now = date('Y-m-d H:i:s');
    
        $db->beginTransaction();
        $q = $db->prepare("insert into t_items_category (`cate_code`, `desc`, `create_date`) values ('".$body['i-catecode']."', '".$body['i-desc']."', '".$_now."');");
        $q->execute();
        $err = $q->errorinfo();
        $db->commit();
        
        $callback = [
            "code" => $err[0], 
            "message" => $err[2]
        ];
        return $response->withJson($callback, 200);
    });
    /**
     * Categories DELETE Request
     * categories-delete
     * 
     * To remove record from DB
     */
    $this->delete('/{cate_code}', function(Request $request, Response $response, array $args){
        $_cate_code = $args['cate_code'];
        $db = connect_db();
        $q = $db->prepare("DELETE FROM `t_items_category` WHERE cate_code = '".$_cate_code."';");
        $q->execute();
        $dbData = $q->fetch();
        $err = $q->errorinfo();
    
        $callback = [
            "query" => $dbData,
            "error" => ["code" => $err[0], "message" => $err[2]]
        ];
    
        return $response->withJson($callback, 200);
    });
});
/**
 * Items API
 */
$app->group('/api/v1/products/items', function () {
    /**
     * Items GET Request
     * item-get
     * 
     * To get all items record
     */
    $this->get('/', function (Request $request, Response $response, array $args) {
        $err=[];
        $db = connect_db();
        $q = $db->prepare("SELECT * FROM `t_items` ORDER BY 'item_code';");
        $q->execute();
        $err = $q->errorinfo();
        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $key => $val) {
            $dbData[] = $val;
        }
        $callback = [
            "query" => $dbData,
            "error" => ["code" => $err[0], "message" => $err[2]]
        ];
    
        if($err[0] == "00000")
            return $response->withJson($callback, 200);
    });
    /**
     * Items GET Request
     * items-get-by-code
     * 
     * To verify current code record is exist
     */
    $this->get('/has/category/{cate_code}', function(Request $request, Response $response, array $args){
        $err=[];
        $_cate_code = $args['cate_code'];
        $db = connect_db();
        $q = $db->prepare("select count(*) as counter from `t_items` WHERE cate_code = '".$_cate_code."';");
        $q->execute();
        $dbData = $q->fetch();
        $err = $q->errorinfo();
        $callback = [
            "query" => "",
            "error" => []
        ];
        if($dbData['counter'] === 0)
        {
            $callback = [
                "query" => false,
                "error" => ["code" => 10001, "message" => "No Dependence"]
            ];
        }
        else
        {
            $callback = [
                "query" => true,
                "error" => ["code" => $err[0], "message" => $dbData['counter']." Item/s in use on this category"]
            ];
        }
        return $response->withJson($callback, 200);
    });
    /**
     * Items GET Request
     * items-get-by-code
     * 
     * To get single items record based on the code
     */
    $this->get('/{item_code}', function(Request $request, Response $response, array $args){
        $err=[];
        $_item_code = $args['item_code'];
        $db = connect_db();
        $q = $db->prepare("select * from `t_items` WHERE item_code = '".$_item_code."';");
        $q->execute();
        $dbData = $q->fetch();
        $err = $q->errorinfo();
    
        $callback = [
            "query" => $dbData,
            "error" => ["code" => $err[0], "message" => $err[2]]
        ];
    
        return $response->withJson($callback, 200);
    });
    /**
     * Items POST Request
     * items-post
     * 
     * To create new items record 
     */
    $this->post('/',function(Request $request, Response $response, array $args){
        $err=[];
        $db = connect_db();
        
    
        // POST Data here
        $body = json_decode($request->getBody(), true);
        //extract($body);
        $db->beginTransaction();
        
        $_now = date('Y-m-d H:i:s');
        $q = $db->prepare("insert into t_items (`item_code`, `eng_name` ,`chi_name`, `desc`, `price`, `price_special`, `cate_code`,`unit`, `create_date`) 
            values (
                '".$body['i-itemcode']."',
                '".$body['i-engname']."',
                '".$body['i-chiname']."',
                '".$body['i-desc']."',
                '".$body['i-price']."',
                '".$body['i-specialprice']."',
                '".$body['i-category']."',
                '".$body['i-unit']."',
                '".$_now."'
            );
        ");
        $q->execute();
        $err = $q->errorinfo();
        $db->commit();
        $callback = [
            "code" => $err[0], 
            "message" => $err[2]
        ];
        return $response->withJson($callback,200);
    });
    /**
     * Items PATCH Request
     * items-patch
     * 
     * To update existing record
     */
    $this->patch('/{item_code}', function(Request $request, Response $response, array $args){
        
        $_item_code = $args['item_code'];
        $db = connect_db();
        $_now = date('Y-m-d H:i:s');
        // // POST Data here
        $body = json_decode($request->getBody(), true);
        $db->beginTransaction();
        $q = $db->prepare("
        UPDATE `t_items` SET 
        `eng_name` = '".$body['i-engname']."', 
        `chi_name` = '".$body['i-chiname']."', 
        `desc` = '".$body['i-desc']."',
        `price` = '".$body['i-price']."', 
        `price_special` = '".$body['i-specialprice']."',
        `cate_code` = '".$body['i-category']."', 
        `unit`= '".$body['i-unit']."',
        `modify_date` = '".$_now."'
        WHERE `item_code` = '".$_item_code."';");
        
        $q->execute();
        $dbData = $q->fetch();
        $err = $q->errorinfo();
        $db->commit();
        $callback = [
            "query" => $dbData,
            "error" => ["code" => $err[0], "message" => $err[2]]
        ];
        return $response->withJson($callback, 200);
    });
    /**
     * Items DELETE Request
     * items-delete
     * 
     * To delete items record from DB
     */
    $this->delete('/{item_code}', function (Request $request, Response $response, array $args) {
        $_item_code = $args['item_code'];
        $db = connect_db();
        $q = $db->prepare("DELETE FROM `t_items` WHERE item_code = '".$_item_code."';");
        $q->execute();
        $dbData = $q->fetch();
        $err = $q->errorinfo();
    
        $callback = [
            "query" => $dbData,
            "error" => ["code" => $err[0], "message" => $err[2]]
        ];
    
        return $response->withJson($callback, 200);
    });

});
/**
 * Customer API
 */
$app->group('/api/v1/customers', function () {
    /**
     * Customer GET Request
     * customer-get
     * 
     * To get all customer record
     */
    $this->get('/', function(Request $request, Response $response, array $args) {
        $err = "";
        $db = connect_db();
        $q = $db->prepare("select * from `t_customers`;");
        $q->execute();
        $err = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($res))
        {
            foreach ($res as $key => $val) {
                $dbData[] = $val;
            }
            $callback = [
                "query" => $dbData,
                "error" => ["code" => $err[0], "message" => $err[2]]
            ];
            return $response->withJson($callback, 200);
        }
    });
    /**
     * Customer GET Request
     * customer-get-by-code
     * 
     * To get single record based on the customer code
     */
    $this->get('/{cust_code}', function(Request $request, Response $response, array $args){
        $_cust_code = $args['cust_code'];
        $err = "";
        $db = connect_db();
        $q = $db->prepare("select * from `t_customers` where cust_code = '".$_cust_code."';");
        $q->execute();
        $err = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        //var_dump($res);
        if(!empty($res))
        {
            foreach ($res as $key => $val) {
                $dbData[] = $val;
            }
            $callback = [
                "query" => $dbData,
                "error" => ["code" => $err[0], "message" => $err[2]]
            ];
            return $response->withJson($callback, 200);
        }
    });
    /**
     * Customer POST Request
     * customer-post
     * 
     * To create new record on customer table 
     */
    $this->post("/", function(Request $request, Response $response, array $args){
       
    });
});
/**
 * Quotation API
 */
$app->group('/api/v1/inventory/quotations', function () {
    /**
     * Quotations GET Request
     * quotations-get
     * 
     * To get all quotations record
     */
    $this->get('/', function (Request $request, Response $response, array $args) {
        $_callback = [];
        $_err = [];
        $_err2 = [];
        $_err3 = [];
        $_pm = [];
        $_cust = [];
        $_query = [];
        $db = connect_db();
        $sql = "
            SELECT * FROM `t_transaction_h` as th
            LEFT JOIN `t_transaction_t` as tt on th.trans_code = tt.trans_code WHERE th.prefix = 'QTA';
        ";
        $sql2 = "
            SELECT pm_code, payment_method FROM `t_payment_method`;
        ";
        $sql3 = "
            SELECT * FROM `t_customers`;
        ";
        // t_transaction_h SQL
        $q = $db->prepare($sql);
        $q->execute();
        $_err = $q->errorinfo();
        $_res = $q->fetchAll(PDO::FETCH_ASSOC);
    
        // t_payment_method SQL
        $q = $db->prepare($sql2);
        $q->execute();
        $_err2 = $q->errorinfo();
        $_res2 = $q->fetchAll(PDO::FETCH_ASSOC);
        
        // t_customer SQL
        $q = $db->prepare($sql3);
        $q->execute();
        $_err3 = $q->errorinfo();
        $_res3 = $q->fetchAll(PDO::FETCH_ASSOC);
    
        // t_shop SQL
        $q = $db->prepare("SELECT * FROM `t_shop`;");
        $q->execute();
        $_err4 = $q->errorinfo();
        $_res4 = $q->fetchAll(PDO::FETCH_ASSOC);

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
            $_res[$k]['is_convert'] == 1 ? $_res[$k]['is_convert'] = "No" : $_res[$k]['is_convert'] = "Yes";
        }
    
        //var_dump($_cust);
    
        // export data
        if(!empty($_res))
        {
            foreach ($_res as $key => $val) {
                $_query[] = $val;
            }
            $_callback = [
                "query" => $_query,
                "error" => ["code" => $_err[0], "message" => $_err[2]]
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
    
        $db = connect_db();
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
        $_err = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        // execute SQL statement 2
        $q = $db->prepare($sql2);
        $q->execute();
        $_err2 = $q->errorinfo();
        $res2 = $q->fetchAll(PDO::FETCH_ASSOC);
        // execute SQL statement 3
        $q = $db->prepare($sql3);
        $q->execute();
        $_err3 = $q->errorinfo();
        $res3 = $q->fetchAll(PDO::FETCH_ASSOC);
        
    
        // export data
        if(!empty($res))
        {
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
     * Quotation PATCH request
     * quotations-patch
     * @param body
     * 
     * to update input to database
     */
    $this->patch('/{trans_code}', function(Request $request, Response $response, array $args)
    {
        $err = [];
        $db = connect_db();
        // POST Data here
        $body = json_decode($request->getBody(), true);
        extract($body);
    
        $_trans_code = $args['trans_code'];
        $sql = "SELECT * FROM `t_transaction_d` WHERE trans_code = '".$_trans_code."';";
        $q = $db->prepare($sql);
        $q->execute();
        $err = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        extract($res);
    
        foreach($res as $k => $v)
        {
            $_new_res[$v['item_code']] = $res[$k];
        }
        
        $db->beginTransaction();
        // transaction header
        $_now = date('Y-m-d H:i:s');
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
        $err = $q->errorinfo();
        
        if($err[2]==null)
        {
            foreach($_new_res as $k => $v)
            {
                if(!array_key_exists($v["item_code"],$items))
                {
                    $sql_d = "DELETE FROM `t_transaction_d` WHERE item_code = '".$v["item_code"]."'";
                    $q = $db->prepare($sql_d);
                    $q->execute();
                    $err = $q->errorinfo();
                    //echo $sql_d."\n";
                }
            }
            foreach($items as $k => $v)
            {
                // Items saved as before
                if(array_key_exists($v["item_code"],$_new_res))
                {
                    $sql_d = "UPDATE `t_transaction_d` SET
                        qty = '".$v['qty']."',
                        unit = '".$v['unit']."',
                        price = '".$v['price']."',
                        modify_date = '".$_now."'
                        WHERE trans_code = '".$_trans_code."' AND item_code = '".$k."';";
                    //echo $sql_d."\n";
                    $q = $db->prepare($sql_d);
                    $q->execute();
                    $err = $q->errorinfo();
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
                    $err = $q->errorinfo();
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
            $err = $q->errorinfo();
        }
        $db->commit();
    
        $callback = [
            "code" => $err[0], 
            "message" => $err[2]
        ];
        return $response->withJson($callback,200);
    });
    
    
    /**
     * Quotations POST request
     * quotations-post
     * @param body
     * 
     * Add new record to DB
     */
    $this->post('/', function (Request $request, Response $response, array $args) {
        $err="";
        $db = connect_db();
        
        // POST Data here
        $body = json_decode($request->getBody(), true);
        extract($body);
        //var_dump($body);
        
        $db->beginTransaction();
    
        $sql = "insert into t_transaction_h (trans_code, cust_code ,quotation_code, prefix, total, employee_code, shop_code, remark, create_date) 
            values (
                '".$quotation."',
                '".$customer['cust_code']."',
                '',
                '".$prefix."',
                '".$total."',
                '".$employeecode."',
                '".$shopcode."',
                '".$remark."',
                '".$date."'
            );
        ";
        $q = $db->prepare($sql);
        $q->execute();
        $err = $q->errorinfo();
    
        if($err[2]==null)
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
            $err[] = $q->errorinfo();
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
            $err[] = $tr->errorinfo();
        }
        $db->commit();
    
        if($err[2] == null)
        {
            $err[2] = "Record inserted!";
        }
    
        $callback = [
            "code" => $err[0], "message" => $err[2]
        ];
        return $response->withJson($callback,200);
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
    
/**
 * Invoices API
 */
$app->group('/api/v1/inventory/invoices', function () {
    /**
     * Invoices GET request
     * invoices-get
     * 
     * To get all invoice record
     */
    $this->get('/', function (Request $request, Response $response, array $args) {
        $_callback = [];
        $_err = [];
        $_err2 = [];
        $_err3 = [];
        $_pm = [];
        $_cust = [];
        $_query = [];
        $db = connect_db();

        // t_transaction_h SQL
        $q = $db->prepare("SELECT * FROM `t_transaction_h` as th left join `t_transaction_t` as tt on th.trans_code = tt.trans_code WHERE th.prefix = 'INV';");
        $q->execute();
        $_err = $q->errorinfo();
        $_res = $q->fetchAll(PDO::FETCH_ASSOC);
    
        // t_payment_method SQL
        $q = $db->prepare("SELECT pm_code, payment_method FROM `t_payment_method`;");
        $q->execute();
        $_err2 = $q->errorinfo();
        $_res2 = $q->fetchAll(PDO::FETCH_ASSOC);
        
        // t_customers SQL
        $q = $db->prepare("SELECT * FROM `t_customers`;");
        $q->execute();
        $_err3 = $q->errorinfo();
        $_res3 = $q->fetchAll(PDO::FETCH_ASSOC);
        
        // t_shop SQL
        $q = $db->prepare("SELECT * FROM `t_shop`;");
        $q->execute();
        $_err4 = $q->errorinfo();
        $_res4 = $q->fetchAll(PDO::FETCH_ASSOC);
        

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
        }
    
        //var_dump($_cust);
    
        // export data
        if(!empty($_res))
        {
            foreach ($_res as $key => $val) {
                $_query[] = $val;
            }
            $_callback = [
                "query" => $_query,
                "error" => ["code" => $_err[0], "message" => $_err[2]]
            ];
            return $response->withJson($_callback, 200);
        }
    });
    
    /**
     * Invoices GET request 
     * invoices-get-by-code
     * 
     * Get invoices by ID
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
    
        $db = connect_db();
        $sql = "
            SELECT 
                th.trans_code as 'invoicenum',
                th.create_date as 'date',
                th.employee_code as 'employee_code',
                th.modify_date as 'modifydate',
                tt.pm_code as 'paymentmethod',
                th.prefix as 'prefix',
                th.quotation_code as 'quotation',
                th.remark as 'remark',
                th.shop_code as 'shopcode',
                th.cust_code as 'cust_code',
                th.total as 'total'
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
        $_err = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        // execute SQL statement 2
        $q = $db->prepare($sql2);
        $q->execute();
        $_err2 = $q->errorinfo();
        $res2 = $q->fetchAll(PDO::FETCH_ASSOC);
        // execute SQL statement 3
        $q = $db->prepare($sql3);
        $q->execute();
        $_err3 = $q->errorinfo();
        $res3 = $q->fetchAll(PDO::FETCH_ASSOC);
        
    
        // export data
        if(!empty($res))
        {
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
     * PATCH request
     * invoices-patch-by-code
     * @param body
     * 
     * to update input to database
     */
    
    $this->patch('/{trans_code}', function(Request $request, Response $response, array $args)
    {
        $err = [];
        $db = connect_db();
        //$sql_d = "";
        // POST Data here
        $body = json_decode($request->getBody(), true);
        extract($body);
    
        $_trans_code = $args['trans_code'];
        $sql = "SELECT * FROM `t_transaction_d` WHERE trans_code = '".$_trans_code."';";
        $q = $db->prepare($sql);
        $q->execute();
        $err = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        extract($res);
    
        foreach($res as $k => $v)
        {
            $_new_res[$v['item_code']] = $res[$k];
        }
        
        $db->beginTransaction();
    
        $_now = date('Y-m-d H:i:s');
        
        $q = $db->prepare("UPDATE `t_transaction_h` SET 
            cust_code = '".$customer['cust_code']."',
            quotation_code = '".$quotation."', 
            total = '".$total."', 
            employee_code = '".$employeecode."', 
            shop_code = '".$shopcode."', 
            remark = '".$remark."', 
            modify_date =  '".$_now."'
            WHERE trans_code = '".$_trans_code."';"
        );
        $q->execute();
        $err = $q->errorinfo();
        
        if($err[2]==null)
        {
            foreach($_new_res as $k => $v)
            {
                if(!array_key_exists($v["item_code"],$items))
                {
                    $sql_d = "DELETE FROM `t_transaction_d` WHERE item_code = '".$v["item_code"]."'";
                    $q = $db->prepare($sql_d);
                    $q->execute();
                    $err = $q->errorinfo();
                    //echo $sql_d."\n";
                }
            }
            foreach($items as $k => $v)
            {
                // Items saved as before
                if(array_key_exists($v["item_code"],$_new_res))
                {
                    $sql_d = "UPDATE `t_transaction_d` SET
                        qty = '".$v['qty']."',
                        unit = '".$v['unit']."',
                        price = '".$v['price']."',
                        modify_date = '".$_now."'
                        WHERE trans_code = '".$_trans_code."' AND item_code = '".$k."';";
                    //echo $sql_d."\n";
                    $q = $db->prepare($sql_d);
                    $q->execute();
                    $err = $q->errorinfo();
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
                    $err = $q->errorinfo();
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
            $err = $q->errorinfo();
        }
        $db->commit();
    
        $callback = [
            "code" => $err[0], 
            "message" => $err[2]
        ];
        return $response->withJson($callback,200);
    });
    
    
    /**
     * POST request
     * invoices-post
     * @param body
     * 
     * Add new record to DB
     */
    $this->post('/', function (Request $request, Response $response, array $args) {
        $err="";
        $db = connect_db();
        
        // POST Data here
        $body = json_decode($request->getBody(), true);
        extract($body);
    
        $db->beginTransaction();
        // insert record to transaction_h
        $sql = "insert into t_transaction_h (trans_code, cust_code ,quotation_code, prefix, total, employee_code, shop_code, remark, create_date) 
            values (
                '".$invoicenum."',
                '".$customer['cust_code']."',
                '".$quotation."',
                '".$prefix."',
                '".$total."',
                '".$employeecode."',
                '".$shopcode."',
                '".$remark."',
                '".$date."'
            );
        ";
        $q = $db->prepare($sql);
        $q->execute();
        $err = $q->errorinfo();
        // insert record to transaction_d
        if($err[2]==null)
        {
            foreach($items as $k => $v)
            {
                $q = $db->prepare("insert into t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, unit, price, discount, create_date)
                    values (
                        '".$invoicenum."',
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
            $err[] = $q->errorinfo();
            // tender information input here
            $tr = $db->prepare("insert into t_transaction_t (trans_code, pm_code, total, create_date) 
                values (
                    '".$invoicenum."',
                    '".$paymentmethod."',
                    '".$total."',
                    '".$date."'
                );
            ");
            $tr->execute();
            $err[] = $tr->errorinfo();
        }

        // if has quotation
        if(!empty($quotation))
        {
            $sql = $db->prepare(
                "UPDATE t_transaction_h SET
                is_convert = 1, 
                modify_date =  '".$date."'
                WHERE trans_code = '".$quotation."';"
            );
            $sql->execute();
            $err[] = $sql->errorinfo();
        }

        $db->commit();
    
        if($err[2] == null)
        {
            $err[2] = "Record inserted!";
        }
    
        $callback = [
            "code" => $err[0], "message" => $err[2]
        ];
        return $response->withJson($callback,200);
     });
    
    /**
     * Check transaction_d item exist API
     */
    $this->group('/transaction/d',function(){
        /**
         * Transaction D GET Request
         * Trans-d-get-by-code
         * 
         * To check items on transaction d table (use it on delete items)
         */
        $this->get('/{item_code}', function (Request $request, Response $response, array $args) {
            $item_code = $args['item_code'];
            $db = connect_db();
            $sql = "SELECT * FROM `t_transaction_d` where item_code = '". $item_code ."';";
        
            $q = $db->prepare($sql);
            $q->execute();
            $dbData = $q->fetch();
            $err = $q->errorinfo();
        
            $callback = [
                "query" => $dbData,
                "error" => ["code" => $err[0], "message" => $err[2]]
            ];
        
            return $response->withJson($callback, 200);
        });
    });
});
/**
 * Payment API
 */
$app->group('/api/v1/systems/payments',function(){
    /**
     * Payment GET Request
     * payment-get
     * 
     * To get all payment record 
     */
    $this->get('/', function (Request $request, Response $response, array $args) {
        $db = connect_db();
        $sql = "select * from `t_payment_method`; ";
        $q = $db->prepare($sql);
        $q->execute();
        $err = $q->errorinfo();
        $result = $q->fetchAll();
        //var_dump($result);
        $new = [];
        foreach($result as $k => $v)
        {
            extract($v);
            $new[$pm_code] = $v;
        }
        $err = $q->errorinfo();
        $callback = [
            "query" => $new,
            "error" => ["code" => $err[0], "message" => $err[2]]
        ];
        return $response->withJson($callback,200);
    });
    $this->post('/', function (Request $request, Response $response, array $args) {
        // $err="";
        
        
        // // POST Data here
        // $body = json_decode($request->getBody(), true);
        // //var_dump($data);
    
        // $callback = [
        //     "code" => "", "message" => ""
        // ];
        // return $response->withJson($callback,200);
    });
});
/**
 * Employee API
 */
$app->group('/api/v1/systems/employee', function () {
    /**
     * Employee GET Request
     * employee-get-by-code
     * 
     * To get employee record
     * 
     */
    $this->get('/{username}', function (Request $request, Response $response, array $args) {
        $_username = $args['username'];
        $db = connect_db();
        if(isset($_username) && !empty($_username))
        {
            $sql = "select * from `t_employee` where username = '".$_username."'; ";
            $q = $db->prepare($sql);
            $q->execute();
            $result = $q->fetch();
            $err = $q->errorinfo();
            $callback = [
                "query" => $result,
                "error" => ["code" => $err[0], "message" => $err[2]]
            ];
            return $response->withJson($callback,200);
        }
    });
});
/** 
 * Menu 
 */
$app->group('/api/v1/systems/menu', function () {
    /**
     * Menu get request
     * menu-get-sidebar
     * 
     * To get sidebar menu
     */
    $this->get('/side', function (Request $request, Response $response, array $args) {
        $data = [
            ["order" => 0, "id" => 2, "parent_id" => "", "name" => "Dushboard", "isParent" => "", "slug"=>"dushboard", "param" => "dushboard/index"],
            ["order" => 0, "id" => 5, "parent_id" => "", "name" => "Customers", "isParent" => "", "slug"=>"customers", "param" => "customers/index"],
            ["order" => 0, "id" => 3, "parent_id" => "", "name" => "Products", "isParent" => "", "slug"=>"", "param" => "products/index"],
            ["order" => 0, "id" => 4, "parent_id" => "", "name" => "Inventories", "isParent" => "", "slug"=>"", "param" => "inventories/index"],
            ["order" => 0, "id" => 23, "parent_id" => 3, "name" => "Items", "isParent" => "", "slug"=>"products/items", "param" => "items/index"],		
            ["order" => 0, "id" => 54, "parent_id" => 3, "name" => "Categories", "isParent" => "", "slug"=>"products/categories", "param" => "categories/index"],
            ["order" => 0, "id" => 65, "parent_id" => 4, "name" => "Invoices", "isParent" => "", "slug"=>"invoices", "param" => "invoices"],
            ["order" => 0, "id" => 32, "parent_id" => 65, "name" => "Create", "isParent" => "", "slug"=>"invoices/donew", "param" => "invoices/create"],
            ["order" => 0, "id" => 22, "parent_id" => "", "name" => "Administration", "isParent" => "", "slug"=>"", "param" => "administration"],
            ["order" => 0, "id" => 71, "parent_id" => 22, "name" => "Settings", "isParent" => "", "slug"=>"administration/settings", "param" => "administration/settings"],
            ["order" => 0, "id" => 35, "parent_id" => 71, "name" => "Shop", "isParent" => "", "slug"=>"administration/shops", "param" => "shops/index"],
            ["order" => 0, "id" => 6, "parent_id" => 71, "name" => "Employees", "isParent" => "", "slug"=>"administration/employees", "param" => "employees/index"],
            ["order" => 0, "id" => 33, "parent_id" => 4, "name" => "Quotations", "isParent" => "", "slug"=>"quotations", "param" => "quotations"],
            ["order" => 0, "id" => 26, "parent_id" => 65, "name" => "List", "isParent" => "", "slug"=>"invoices/list", "param" => "invoices/invlist"],
            ["order" => 0, "id" => 333, "parent_id" => 33, "name" => "Create", "isParent" => "", "slug"=>"quotations/donew", "param" => "quotations/create"],
            ["order" => 0, "id" => 332, "parent_id" => 33, "name" => "List", "isParent" => "", "slug"=>"quotations/list", "param" => "quotations/qualist"],
            ["order" => 0, "id" => 43, "parent_id" => 71, "name" => "Payment Method", "isParent" => "", "slug"=>"administration/payments", "param" => "payments/index"],
        ];
        return $response->withJson($data, 200);
    });
    $this->get('/top', function (Request $request, Response $response, array $args) {
        $data = [
            "message" => "this is menu top API"
        ];
        return $response->withJson($data, 200);
    });
});
/**
 * Shop API
 */
$app->group('/api/v1/systems/shops', function () {
    /**
     * Shop GET request
     * shop-get
     * 
     * To get shop record 
     */
    $this->get('/', function (Request $request, Response $response, array $args) {
        $err = "";
        $db = connect_db();
        $q = $db->prepare("select * from `t_shop`;");
        $q->execute();
        $err = $q->errorinfo();
        //$result = $db->query( "select * from `t_shop`;");
        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $key => $val) {
            $dbData[] = $val;
        }
        $callback = [
            "query" => $dbData,
            "error" => ["code" => $err[0], "message" => $err[2]]
        ];
        return $response->withJson($callback, 200);
    });
});

$app->run();
