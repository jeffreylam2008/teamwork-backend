<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

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
                ts.shop_code
            FROM `t_transaction_h` as th 
            LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code 
            LEFT JOIN `t_customers` as tc ON th.cust_code = tc.cust_code 
            LEFT JOIN `t_suppliers` as tsp ON th.supp_code = tsp.supp_code 
            LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code
            LEFT JOIN `t_prefix` as tp ON th.prefix = tp.prefix
            WHERE tp.uid in ('4','5','6','7') ". $_where_date . $_where_trans.";
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
        if($q->rowCount() != 0)
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
     * GET Operation By supplier code
     * @param supp_code supplier code required
     */
    $this->get('/getlast/supp/{supp_code}', function (Request $request, Response $response, array $args) {
        $_callback = [];
        $_err = [];
        $_res = [];

        $_supp_code = $args['supp_code'];
        $pdo = new Database();
        $db = $pdo->connect_db();
        
        $sql = "
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
        WHERE th.supp_code = '".$_supp_code."';
        ";
        
        $q = $db->prepare($sql);
        $q->execute();
        $_err = $q->errorinfo();
        if($q->rowCount() != 0)
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


