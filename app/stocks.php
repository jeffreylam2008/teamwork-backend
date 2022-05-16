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
        $_param = $request->getQueryParams();
        //$_prefix['prefix'] = "";
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_err = [];
        $_data = [];
        $_msg = "";
        $_result = true;
        $_where_trans = "";
        $_where_date = "";
        if(!empty($_param))
        {
            $this->logger->addInfo("Entry: stock: get all stock information");
            $pdo = new Database();
            $db = $pdo->connect_db();
            $this->logger->addInfo("Msg: DB connected");
            // prefix SQL 
            // in DN, GRN, ADJ, ST
            
            // only if transaction field param exist
            if(!empty($_param['i-num']))
            {
                $_where_trans = "AND (th.trans_code LIKE ('%".$_param['i-num']."%') OR th.refer_code LIKE ('%".$_param['i-num']."%')) ";
            }
            // otherwise follow date range as default
            if(!empty($_param['i-start-date']) && !empty($_param['i-end-date']) )
            {
                $_where_date = "AND (date(th.create_date) BETWEEN '".$_param['i-start-date']."' AND '".$_param['i-end-date']."') ";
            }
            $sql = "SELECT"; 
            $sql .= " th.*,";
            $sql .= " tc.name as `customer`,";
            $sql .= " ts.name as `shop_name`,";
            $sql .= " tsp.name as 'supplier',";
            $sql .= " ts.shop_code";
            $sql .= " FROM `t_transaction_h` as th";
            $sql .= " LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code";
            $sql .= " LEFT JOIN `t_customers` as tc ON th.cust_code = tc.cust_code";
            $sql .= " LEFT JOIN `t_suppliers` as tsp ON th.supp_code = tsp.supp_code";
            $sql .= " LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code";
            $sql .= " LEFT JOIN `t_prefix` as tp ON th.prefix = tp.prefix";
            $sql .= " WHERE tp.uid in ('4','5','6','7') ". $_where_date . $_where_trans.";";
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $_data = $q->fetchAll(PDO::FETCH_ASSOC);

            //disconnection DB
            $pdo->disconnect_db();
            $this->logger->addInfo("Msg: DB connection closed");

            foreach($_err as $k => $v)
            {
                if($v[0] != "00000")
                {
                    $_result = false;
                    $_msg .= $v[1]."-".$v[2]."|";
                }
                else
                {
                    $_msg .= "SQL #".$k.": SQL execute OK! | ";
                }
            }

            if($_result)
            {
                $_callback['query'] = $_data;
                $_callback['error']['code'] = "00000";
                $_callback['error']['message'] = "Data fetch OK!";
                $this->logger->addInfo("SQL execute ".$_msg);
                return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
            }
            else
            {  
                $_callback['query'] = "";
                $_callback['error']['code'] = "99999";
                $_callback['error']['message'] = "Data fetch Fail - Please try again!";
                $this->logger->addInfo("SQL execute ".$_msg);
                return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
            }
        }
        else
        {
            $_callback = [
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
        $_result = true;
        $_msg = "";
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_err = [];
        $_data = [];
        $cust_code = $args['cust_code'];
        
        $this->logger->addInfo("Entry: stocks: get last customer info for search");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        
        $sql = "SELECT";
        $sql .= " th.*,"; 
        $sql .= " tpm.payment_method,";
        $sql .= " tc.name as `customer`,";
        $sql .= " ts.name as `shop_name`,";
        $sql .= " tsp.name as 'supplier',";
        $sql .= " ts.shop_code";
        $sql .= " FROM `t_transaction_h` as th";
        $sql .= " LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code";
        $sql .= " LEFT JOIN `t_customers` as tc ON th.cust_code = tc.cust_code";
        $sql .= " LEFT JOIN `t_suppliers` as tsp ON th.supp_code = tsp.supp_code";
        $sql .= " LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code";
        $sql .= " LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code";
        $sql .= " INNER JOIN ( select cust_code, max(`create_date`) as MaxDate from `t_transaction_h` group by cust_code ) tm";
        $sql .= " ON th.cust_code = tm.cust_code AND th.`create_date` = tm.MaxDate";
        $sql .= " WHERE th.cust_code = '".$cust_code."';";

        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() != 0)
        {
            $_data = $q->fetchAll(PDO::FETCH_ASSOC);
        }

        //disconnection DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");
        foreach($_err as $k => $v)
        {
            // has error
            if($v[0] != "00000")
            {
                $_result = false;
                $_msg .= $v[1]."-".$v[2]."|";
            }
            else
            {
                $_msg .= "SQL #".$k.": SQL execute OK! | ";
            }
        }
        if($_result)
        {
            $_callback['query'] = $_data;
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Data fetch OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Retrieve Data Problem!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * GET Operation By supplier code
     * @param supp_code supplier code required
     */
    $this->get('/getlast/supp/{supp_code}', function (Request $request, Response $response, array $args) {
        $_result = true;
        $_msg = "";
		$_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_err = [];
        $_data = [];
        $_supp_code = $args['supp_code'];

        $this->logger->addInfo("Entry: stocks: get last supplier info for search");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = "SELECT";
        $sql .=" th.*,";
        $sql .=" tc.name as `customer`,";
        $sql .=" ts.name as `shop_name`,";
        $sql .=" tsp.name as 'supplier',";
        $sql .=" ts.shop_code";
        $sql .=" FROM `t_transaction_h` as th";
        $sql .=" LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code";
        $sql .=" LEFT JOIN `t_customers` as tc ON th.cust_code = tc.cust_code";
        $sql .=" LEFT JOIN `t_suppliers` as tsp ON th.supp_code = tsp.supp_code";
        $sql .=" LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code";
        $sql .=" WHERE th.supp_code = '".$_supp_code."';";
        
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() != 0)
        {
            $_res = $q->fetchAll(PDO::FETCH_ASSOC);
        }

        //disconnection DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");

        foreach($_err as $k => $v)
        {
            // has error
            if($v[0] != "00000")
            {
                $_result = false;
                $_msg .= $v[1]."-".$v[2]."|";
            }
            else
            {
                $_msg .= "SQL #".$k.": SQL execute OK! | ";
            }
        }
        if($_result)
        {
            $_callback['query'] = $_data;
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Data fetch OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Retrieve Data Problem!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });
    
    /**
     * GET Operation to get stockonhand data
     * @param item_code item code required
     */
    $this->get('/onhand/{item_code}', function (Request $request, Response $response, array $args) {
        $_result = true;
        $_msg = "";
		$_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_err = [];
        $_data = [];
        $_item_code = $args['item_code'];

        $this->logger->addInfo("Entry: invoices: get last customer info for search");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        // t_transaction_h SQL
        //$q = $db->prepare("SELECT * FROM `t_transaction_h` as th left join `t_transaction_t` as tt on th.trans_code = tt.trans_code WHERE th.prefix = 'INV' LIMIT 9;");
        $sql = "SELECT qty FROM `t_warehouse` WHERE item_code = '".$_item_code."';";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() != 0)
        {
            $_data = $q->fetch(PDO::FETCH_ASSOC);
        }

        //disconnection DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");
        
        foreach($_err as $k => $v)
        {
            // has error
            if($v[0] != "00000")
            {
                $_result = false;
                $_msg .= $v[1]."-".$v[2]."|";
            }
            else
            {
                $_msg .= "SQL #".$k.": SQL execute OK! | ";
            }
        }
        if($_result)
        {
            $_callback['query'] = $_data;
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Data fetch OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Retrieve Data Problem!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * stock header
     * To get all header information for stock function
     */
    $this->group('/view',function()
    {
        $this->get('/header/username/{username}/', function (Request $request, Response $response, array $args) 
        {
            $_err = [];
            $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
            $_result = true;
            $_msg = "";
            $_data['employee'] = [];
            $_data['menu'] = [];
            $_data['prefix'] = [];
            $_data['dn'] = ["dn_num"=>"", "dn_prefix"=>""];
            $_max = "00";
            $_param = $request->getQueryParams();
            $_username = $args['username'];
            $_result = true;
            $_msg = "";

            $this->logger->addInfo("Entry: purchases: get header");
            $pdo = new Database();
            $db = $pdo->connect_db();
            $this->logger->addInfo("Msg: DB connected");
            $sql = "SELECT ";
            $sql .= " te.employee_code as employee_code,";
            $sql .= " te.username as username,";
            $sql .= " ts.name as shop_name,";
            $sql .= " ts.shop_code as shop_code";
            $sql .= " FROM `t_employee` as te";
            $sql .= " LEFT JOIN `t_shop` as ts";
            $sql .= " ON te.default_shopcode = ts.shop_code where te.username = '".$_username."';";
            // echo $sql."\n";
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $_data['employee'] = $q->fetch(PDO::FETCH_ASSOC);

            if(empty($_param)) $_param['lang'] = "";
            // SQL2
            switch($_param['lang'])
            {
                case "en-us":
                    $sql = "SELECT m_order as `order`, `id`, `parent_id`, lang2 as `name`, slug, `param` FROM `t_menu`;";
                    break;
                case "zh-hk":
                    $sql = "SELECT m_order as `order`, `id`, `parent_id`, lang1 as `name`, slug, `param` FROM `t_menu`;";
                    break;
                default:
                    $sql = "SELECT m_order as `order`, `id`, `parent_id`, lang2 as `name`, slug, `param` FROM `t_menu`;";
                    break;
            }
            //echo $sql2."\n";
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $_data['menu'] = $q->fetchAll(PDO::FETCH_ASSOC);

            //disconnection DB
            $pdo->disconnect_db();
            $this->logger->addInfo("Msg: DB connection closed");

            foreach($_err as $k => $v)
            {
                if($v[0] != "00000")
                {
                    $_result = false;
                    $_msg .= $v[1]."-".$v[2]."|";
                }
                else
                {
                    $_msg .= "SQL #".$k.": SQL execute OK! | ";
                }
            }

            if($_result)
            {
                $_callback['query'] = $_data;
                $_callback['error']['code'] = "00000";
                $_callback['error']['message'] = "Header data fetch OK!";
                $this->logger->addInfo("SQL execute ".$_msg);
                return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
            }
            else
            {  
                $_callback['query'] = "";
                $_callback['error']['code'] = "99999";
                $_callback['error']['message'] = "Header data fetch Fail - Please try again!";
                $this->logger->addInfo("SQL execute ".$_msg);
                return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
            }
            
        });
    });
});
 


