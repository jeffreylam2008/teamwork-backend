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
        $_param = $request->getQueryParams();
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_err = [];
        $_data = [];
        $_msg = "";
        $_result = true;
        $_where_trans = "";
        $_where_date = "";

        $this->logger->addInfo("Entry: purchases: get all purchases");
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
        else
        {
            $_where_date = "AND (date(th.create_date) BETWEEN '".$_param['i-start-date']."' AND '".$_param['i-end-date']."') ";
        }

        $sql = "SELECT ";
        $sql .= " th.*,";
        $sql .= " tc.name as `customer`,";
        $sql .= " ts.name as `shop_name`,";
        $sql .= " tsp.name as 'supp_name',";
        $sql .= " tpm.payment_method as 'payment_method',";
        $sql .= " ts.shop_code";
        $sql .= " FROM `t_transaction_h` as th";
        $sql .= " LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code";
        $sql .= " LEFT JOIN `t_customers` as tc ON th.cust_code = tc.cust_code";
        $sql .= " LEFT JOIN `t_suppliers` as tsp ON th.supp_code = tsp.supp_code";
        $sql .= " LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code";
        $sql .= " LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code";
        $sql .= " LEFT JOIN `t_prefix` as tp ON th.prefix = tp.prefix";
        $sql .= " WHERE th.is_void = 0 AND th.prefix = (SELECT prefix FROM t_prefix WHERE uid = 2) ";
        $sql .= $_where_date . $_where_trans.";";
        // $this->logger->addInfo("SQL: ".$sql);
        // t_transaction_h SQL
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $_data = $q->fetchAll(PDO::FETCH_ASSOC);

        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");

        // export data

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
    });

    /**
     * GET Request 
     * To PO record 
     * @param trans_code trans_code
     */
    $this->get('/{trans_code}', function (Request $request, Response $response, array $args) {
        // inital variable
        $_callback = [];
        $_data = [];
        $_callback['has'] = false;
        $_result = true;
        $_msg = "";
        $_trans_code= $args['trans_code'];
        $_err = [];
        $_customers = [];
        $_settlement = 0;
    
        $this->logger->addInfo("Entry: purchases: get purchases by trans_code");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = "SELECT";
        $sql .= " (SELECT COUNT(*) FROM `t_transaction_h` WHERE refer_code = '".$_trans_code."') as `has_grn`,";
        $sql .= " th.trans_code,";
        $sql .= " th.create_date as 'date',";
        $sql .= " th.employee_code as 'employee_code',";
        $sql .= " th.refer_code as 'refer_num',";
        $sql .= " th.modify_date as 'modifydate',";
        $sql .= " tt.pm_code as 'paymentmethod',";
        $sql .= " tpm.payment_method as 'paymentmethodname',";
        $sql .= " th.prefix as 'prefix',";
        $sql .= " th.quotation_code as 'quotation',";
        $sql .= " th.remark as 'remark',";
        $sql .= " th.shop_code as 'shopcode',";
        $sql .= " ts.name as 'shopname',";
        $sql .= " th.cust_code as 'cust_code',";
        $sql .= " th.supp_code as 'supp_code',";
        $sql .= " tsp.name as 'supp_name',";
        $sql .= " th.total as 'total',";
        $sql .= " th.is_convert as 'is_convert',";
        $sql .= " th.is_settle as 'is_settle'";
        $sql .= " FROM `t_transaction_h` as th";
        $sql .= " LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code";
        $sql .= " LEFT JOIN `t_suppliers` as tsp ON th.supp_code = tsp.supp_code";
        $sql .= " LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code";
        $sql .= " LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code";
        $sql .= " WHERE th.trans_code = '".$_trans_code."';";
        
        //$this->logger->addInfo("SQL: ".$sql);

        // execute SQL Statement 1
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() != 0)
        {
            $head = $q->fetch(PDO::FETCH_ASSOC);
            // $this->logger->addInfo("SQL: ".$sql);

            $sql = "SELECT";
            $sql .= " td.item_code,";
            $sql .= " td.eng_name,";
            $sql .= " td.chi_name,";
            $sql .= " td.qty,";
            $sql .= " td.unit,";
            $sql .= " td.price,";
            $sql .= " td.discount as 'price_special',";
            $sql .= " tw.qty as 'stockonhand'";
            $sql .= " FROM `t_transaction_d` as td ";
            $sql .= " LEFT JOIN `t_warehouse` as tw ON td.item_code = tw.item_code";
            $sql .= " WHERE trans_code = '".$_trans_code."';";
            // execute SQL statement 2
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $po_items = $q->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT ";
            $sql .= " sum(td.qty) as 'po_items'";
            $sql .= " FROM `t_transaction_d` as td ";
            $sql .= " LEFT JOIN `t_warehouse` as tw ON td.item_code = tw.item_code";
            $sql .= " WHERE trans_code = '".$_trans_code."';";

            // $this->logger->addInfo("SQL: ".$sql);
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $res3 = $q->fetch();

            $sql = "SELECT";
            $sql .= " sum(td.qty) as 'grn_items'";
            $sql .= " FROM `t_transaction_h` as th ";
            $sql .= " LEFT JOIN `t_transaction_d` as td ON th.trans_code = td.trans_code";
            $sql .= " WHERE th.refer_code = '".$_trans_code."';";
            // $this->logger->addInfo("SQL: ".$sql);
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $res4 = $q->fetch();

            $_data = $head;
            $_settlement = ($res3['po_items'] - $res4['grn_items']);
            foreach ($po_items as $key => $val) {
                 $_data["items"] = $po_items;
            }
            if($_settlement === 0)
            {
                $_data["settlement"] = true; 
            }
            else
            {
                $_data["settlement"] = false; 
            }
            // calcuate subtotal
            if(!empty($_data["items"]))
            {
                foreach($_data["items"] as $k => $v)
                {
                    extract($v);
                    $_data["items"][$k]["subtotal"] = ($qty * $price);
                }
            }
        }
        else
        {
            $_result = false;
        }
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
            $_callback['has'] = true;
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Data fetch OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['query'] = "";
            $_callback['has'] = false;
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Data fetch Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * GET Request
     * To gen next purchase number
     * @param session_id session ID 
     */
    $this->get('/getnextnum/{session_id}', function (Request $request, Response $response, array $args) {
        $_session_id = $args['session_id'];
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_prefix = "";

        $this->logger->addInfo("Entry: purchases: getnextnum");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        if(empty($_session_id))
        {
            $_session_id = "";
        }

        // prefix SQL
        $sql = "SELECT prefix FROM `t_prefix` WHERE `uid` = '2' LIMIT 1;";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() > 0)
        {
            $_prefix = $q->fetch(PDO::FETCH_ASSOC);
        }

        $sql = "SELECT `last`, `prefix`, `suffix`, `session_id` FROM `t_trans_num_generator` WHERE `prefix` in (SELECT prefix FROM t_prefix WHERE uid = 2)  ORDER BY `create_date` DESC LIMIT 1";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $_data = $q->fetch(PDO::FETCH_ASSOC);

        
        // define variable
        $prefix = $_prefix['prefix'];
        $suffix = date("ym");

        if(empty($_data['last']))
        {
            $_last = 0;
            $last = $_last + 1;
            $insert = true;

        }
        // session_id is different then give a new one
        elseif( strcmp($_data['session_id'],$_session_id) != 0 )
        {
            // reset counter to zero
            if($_data['last'] >= 199){
                $_data['last'] = 0;
            }
            $last = $_data['last'] + 1;
            $insert = true;

        }
        // remain same id
        else
        {
            $prefix = $_data['prefix'];
            $suffix = $_data['suffix'];
            $last = $_data['last'];
            $insert = false;
        }

        $last = str_pad($last,3,0,STR_PAD_LEFT);
        if($insert){
            $sql = "INSERT INTO `t_trans_num_generator` (`prefix`, `suffix`, `last`, `session_id`, `create_date`, `expiry_date`)  VALUES(  '".$prefix."', '".$suffix."', '".$last."', '".$_session_id."', '".date('Y-m-d H:i:s')."', null);";
            // $this->logger->addInfo("SQL = ".$sql);
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
        }

        $_data = $prefix.$suffix.$last;

        // disconnect DB session
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
    });

    /**
     * Get Request
     * To get PO prefix
     */
    $this->get('/getprefix/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];

        $this->logger->addInfo("Entry: purchases: getprefix");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        // prefix SQL
        $sql = "SELECT prefix FROM `t_prefix` WHERE `uid` = '2' LIMIT 1;";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() != 0)
        {
            $_data = $q->fetch(PDO::FETCH_ASSOC);
        }

        // disconnect DB session
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
            $_callback['query'] = $_data['prefix'];
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
    });

    /**
     * GET search supplier
     * To search latest transaction by supplier
     * @param supp_code supplier code
     */
    $this->get('/getlast/supp/{supp_code}', function (Request $request, Response $response, array $args) {
        $_result = true;
        $_msg = "";
		$_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_err = [];
        $_data = [];
        $supp_code = $args['supp_code'];
        
        $this->logger->addInfo("Entry: purchases: get last supplier info for search");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        
        $sql = "SELECT";
        $sql .= " th.*, ";
        $sql .= " tpm.payment_method as `payment_method`, ";
        $sql .= " tsp.name as `supp_name`, ";
        $sql .= " ts.name as `shop_name`, ";
        $sql .= " ts.shop_code";
        $sql .= " FROM `t_transaction_h` as th ";
        $sql .= " LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code ";
        $sql .= " LEFT JOIN `t_suppliers` as tsp ON th.supp_code = tsp.supp_code ";
        $sql .= " LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code ";
        $sql .= " LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code"; 
        $sql .= " LEFT JOIN `t_prefix` as tp ON th.prefix = tp.prefix";
        $sql .= " WHERE th.supp_code = '".$supp_code."' AND tp.uid = '2'; ";

        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() != 0)
        {
            $_data = $q->fetchAll(PDO::FETCH_ASSOC);
        }
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
            $_callback['error']['message'] = "OK!";
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
     * GET remain item count via GRN
     * @param refer_code reference code
     */
    $this->get('/getgrn/po/{refer_code}', function (Request $request, Response $response, array $args) {
        // inital variable
        $_result = true;
        $_msg = "";
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
        $this->logger->addInfo("Entry: purchases: get grn refer PO by refer_code");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = " SELECT";
        $sql .= " (SELECT COUNT(*) FROM `t_transaction_h` WHERE refer_code = '".$_refer_code."') as `has_grn`,";
        $sql .= " th.trans_code,";
        $sql .= " th.create_date as 'date',";
        $sql .= " th.employee_code as 'employee_code',";
        $sql .= " th.refer_code as 'refernum',";
        $sql .= " th.modify_date as 'modifydate',";
        $sql .= " tt.pm_code as 'paymentmethod',";
        $sql .= " tpm.payment_method as 'paymentmethodname',";
        $sql .= " th.prefix as 'prefix',";
        $sql .= " th.quotation_code as 'quotation',";
        $sql .= " th.remark as 'remark',";
        $sql .= " th.shop_code as 'shopcode',";
        $sql .= " ts.name as 'shopname',";
        $sql .= " th.cust_code as 'cust_code',";
        $sql .= " th.supp_code as 'supp_code',";
        $sql .= " tsp.name as 'supp_name', ";
        $sql .= " th.total as 'total'";
        $sql .= " FROM `t_transaction_h` as th";
        $sql .= " LEFT JOIN `t_transaction_t` as tt ON th.trans_code = tt.trans_code";
        $sql .= " LEFT JOIN `t_suppliers` as tsp ON th.supp_code = tsp.supp_code";
        $sql .= " LEFT JOIN `t_payment_method` as tpm ON tt.pm_code = tpm.pm_code";
        $sql .= " LEFT JOIN `t_shop` as ts ON th.shop_code = ts.shop_code";
        $sql .= " WHERE th.trans_code = '".$_refer_code."'; ";
        // execute SQL Statement 1
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $head = $q->fetch(PDO::FETCH_ASSOC);

        
        // found po on transaction header and has grn record
        if(!empty($head))
        {       
            $sql = "SELECT ";
            $sql .= " td.item_code, ";
            $sql .= " td.eng_name,";
            $sql .= " td.chi_name,";
            $sql .= " td.qty, ";
            $sql .= " td.unit,";
            $sql .= " td.price,";
            $sql .= " td.discount as 'price_special',";
            $sql .= " tw.qty as 'stockonhand'";
            $sql .= " FROM `t_transaction_h` as th ";
            $sql .= " LEFT JOIN `t_transaction_d` as td ON th.trans_code = td.trans_code ";
            $sql .= " LEFT JOIN `t_warehouse` as tw ON td.item_code = tw.item_code";
            $sql .= " WHERE th.`trans_code` = '".$_refer_code."' ";
            $sql .= " AND th.`prefix` = (select prefix from t_prefix where uid = 2);";
            // execute SQL statement 2
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $po_items = $q->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT ";
            $sql .= " td.item_code,";
            $sql .= " td.qty";
            $sql .= " FROM `t_transaction_h` as th ";
            $sql .= " LEFT JOIN `t_transaction_d` as td ON th.trans_code = td.trans_code";
            $sql .= " WHERE th.`refer_code` = '".$_refer_code."' ";
            $sql .= " AND th.`prefix` = (select prefix from t_prefix where uid = 5);";
            // retrieve PO items SQL statement
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $grn_items = $q->fetchAll(PDO::FETCH_ASSOC);

            //disconnection DB
            $pdo->disconnect_db();
            $this->logger->addInfo("Msg: DB connection closed");

            $_query = $head;
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

        }
        else
        {
            $_result = false;
        }

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
            $_callback['query'] = $_query;
            $_callback['has'] = true;
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Data fetch OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {
            $_callback['query'] = "";
            $_callback['has'] = false;
            $_callback["error"]["code"] = "99999";
            $_callback["error"]["message"] = "Item not found";
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * GET settlement info 
     */
    $this->get('/settlement/po/{refer_code}',function(Request $request, Response $response, array $args){
        $_refer_code = $args['refer_code'];
        $_err = [];
        $_callback = ['query' => ['all_grn'=>"", 'total'=>''] , 'error' => ["code" => "", "message" => ""]];
        $_data = [];
        $_result = true;
        $_msg = "";

        $this->logger->addInfo("Entry: purchases: get settlement by refer_code");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = " SELECT";
        $sql .= " tt.trans_code, ";
        $sql .= " tt.pm_code, ";
        $sql .= " tt.total ";
        $sql .= " FROM `t_transaction_h` as th  ";
        $sql .= " LEFT JOIN t_transaction_t as tt "; 
        $sql .= " ON th.trans_code = tt.trans_code ";
        $sql .= " WHERE th.refer_code = '".$_refer_code."';";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() != 0)
        {
            $_data = $q->fetchAll(PDO::FETCH_ASSOC);
        }
        //$this->logger->addInfo("SQL: ".$sql);
        $sql = "SELECT";
        $sql .= " sum(tt.total) as total";
        $sql .= " FROM `t_transaction_h` as th ";
        $sql .= " LEFT JOIN t_transaction_t as tt ";
        $sql .= " ON th.trans_code = tt.trans_code ";
        $sql .= " WHERE th.refer_code = '".$_refer_code."';";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() != 0)
        {
            $total = $q->fetch();
        }
        //$this->logger->addInfo("SQL: ".$sql);

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
            $_callback = [
                "query" => [
                    "all_grn" => $_data,
                    "total" => $total["total"],
                ],
                "error" => [ 
                    "code" => "00000",
                    "message" => "Data fetch OK!",
                ]
            ];
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback = [
                "query" => [
                    "all_grn" => "",
                    "total" => 0,
                ],
                "error" => [ 
                    "code" => "99999",
                    "message" => "Retrieve Data Problem!",
                ]
            ];
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
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
            $_err = [];
            $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
            $_result = true;
            $_msg = "";
            $_data = [];
            $_param = $request->getQueryParams();
            if(empty($_param["month"])) $_param["month"] = date('m');
            if(empty($_param["year"])) $_param["year"] = date('Y');

            $this->logger->addInfo("Entry: purchases: get count");
            $pdo = new Database();
            $db = $pdo->connect_db();
            $this->logger->addInfo("Msg: DB connected");
            $sql = "SELECT ";
            $sql .= " count(*) as count,";
            $sql .= " sum(total) as expand";
            $sql .= " FROM ";
            $sql .= " `t_transaction_h` as th";
            $sql .= " WHERE th.is_void = 0 AND th.prefix = (SELECT prefix FROM `t_prefix` WHERE `uid` = '2') AND month(th.create_date) = '".$_param['month']."'";
            $sql .= " AND year(th.create_date) = '".$_param['year']."';";
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $_data = $q->fetch(PDO::FETCH_ASSOC);
        
            //disconnection DB
            $pdo->disconnect_db();
            $this->logger->addInfo("Msg: DB connection closed");
            
            // export data
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
       
        });
    
        /**
         * Transaction H GET Request
         * Supplier Check 
         * To check Supplier code on transaction h table (use it on delete customer)
         * 
         * @param prefix existing prefix 
         * @param supp_code supplier code to look up record
         */
        $this->get('/{prefix}/suppliers/{supp_code}', function (Request $request, Response $response, array $args) {
            $_err = [];
            $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
            $_result = true;
            $_msg = "";
            $_data = [];
            $supp_code = $args['supp_code'];
            $prefix = $args['prefix'];

            $this->logger->addInfo("Entry: purchases: check supplier by supp_code has transaction_h exist");
            $pdo = new Database();
            $db = $pdo->connect_db();
            $this->logger->addInfo("Msg: DB connected");

            $sql = "SELECT * FROM `t_transaction_h` where supp_code = '". $supp_code ."' AND prefix = '".$prefix."';";
            $this->logger->addInfo("SQL: ".$sql);
            $q = $db->prepare($sql);
            $q->execute();
            $_data = $q->fetchAll(PDO::FETCH_ASSOC);
            $_err[] = $q->errorinfo();
            //disconnection DB
            $pdo->disconnect_db();
            $this->logger->addInfo("Msg: DB connection closed");

            if(!$_data)
            {
                $_data = ["has" => false, "data"=> ""];
            }
            else
            {
                $_data = ["has" => true, "data"=> $_data];
            }

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
        $_result = true;
        $_msg = "";
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
            // $this->logger->addInfo("remark => ".$_remark);
        }
        
        $this->logger->addInfo("Entry: PATCH: purchases settlement");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = "SELECT `remark` FROM `t_transaction_h` WHERE `trans_code` = '".$_trans_code."' LIMIT 1 INTO @_remark;";
        $sql .= " UPDATE `t_transaction_h` SET";
        $sql .= " `is_convert` = 1,";
        $sql .= " `is_settle` = 1,";
        $sql .= " `remark` = concat(@_remark,'\n\n','Settled!\nGRN: ".$_remark."'),";
        $sql .= " `modify_date` =  '".$_now."'";
        $sql .= " WHERE `trans_code` = '".$_trans_code."';";

        // $this->logger->addInfo("SQL: ".$sql);

        // echo $sql;
        // transaction header
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        //disconnection DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");

        // finish up the flow
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
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Transaction: ".$_trans_code." - Update OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Insert Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * Edit PO record
     * @param trans_code po number
     */
    $this->patch('/{trans_code}', function (Request $request, Response $response, array $args){
        $_err = [];
		$_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_now = date('Y-m-d H:i:s');
        $_new_res = [];
        $_trans_code = $args['trans_code'];

        $this->logger->addInfo("Entry: PATCH: purchases");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        //$sql_d = "";
        // POST Data here
        $body = json_decode($request->getBody(), true);
        extract($body);
        // To convert money format to decimal
        $total = filter_var($total,FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    
        $sql = "SELECT * FROM `t_transaction_d` WHERE trans_code = '".$_trans_code."';";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        
        if($q->rowCount() != 0)
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
            $sql = "UPDATE `t_transaction_h` SET ";
            $sql .= " `supp_code` = '".$supp_code."',";
            $sql .= " `total` = '".$total."',";
            $sql .= " `shop_code` = '".$shopcode."',";
            $sql .= " `remark` = '".$remark."',";
            $sql .= " `modify_date` =  '".$_now."'";
            $sql .= " WHERE `trans_code` = '".$_trans_code."';";
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            
            if($q->rowCount() != 0)
            {
                foreach($_new_res as $k_itcode => $v)
                {
                    // delete items from this transaction
                    if(!array_key_exists($k_itcode,$items))
                    {
                        $sql = "DELETE FROM `t_transaction_d` WHERE trans_code = '".$_trans_code."' AND item_code = '".$k_itcode."';";
                        $q = $db->prepare($sql);
                        $q->execute();
                        $_err[] = $q->errorinfo();
                    }
                }
                foreach($items as $k_itcode => $v)
                {
                    // update item already in transaction
                    if(array_key_exists($k_itcode,$_new_res))
                    {
                        $sql = "UPDATE `t_transaction_d` SET";
                        $sql .= " qty = '".$v['qty']."',";
                        $sql .= " unit = '".$v['unit']."',";
                        $sql .= " price = '".$v['price']."',";
                        $sql .= " modify_date = '".$_now."'";
                        $sql .= " WHERE trans_code = '".$_trans_code."' AND item_code = '".$k_itcode."';";
                        //echo $sql."\n";
                        $q = $db->prepare($sql);
                        $q->execute();
                        $_err[] = $q->errorinfo();
                    }
                    // New add items
                    else
                    {
                        $sql = "insert into t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, unit, price, create_date)";
                        $sql .= " values (";
                        $sql .= " '".$_trans_code."',";
                        $sql .= " '".$v['item_code']."',";
                        $sql .= " '".$v['eng_name']."' ,";
                        $sql .= " '".$v['chi_name']."' ,";
                        $sql .= " '".$v['qty']."',";
                        $sql .= " '".$v['unit']."',";
                        $sql .= " '".$v['price']."',";
                        $sql .= " '".$date."');";
                        //echo $sql."\n";
                        $q = $db->prepare($sql);
                        $q->execute();
                        $_err[] = $q->errorinfo();
                    }   
                }

                // tender information input here
                $sql = "UPDATE `t_transaction_t` SET ";
                $sql .= "`pm_code` = '".$paymentmethod."',";
                $sql .= "`total` = '".$total."',";
                $sql .= "`modify_date` = '".$_now."'";
                $sql .= "WHERE `trans_code` = '".$_trans_code."';";
                $q = $db->prepare($sql);
                $q->execute();
                $_err[] = $q->errorinfo();
            }
            $db->commit();
            $this->logger->addInfo("Msg: DB commit");
            //disconnection DB
            $pdo->disconnect_db();
            $this->logger->addInfo("Msg: DB connection closed");
        }

        // finish up the flow
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
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Transaction: ".$_trans_code." - Update OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Insert Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * POST request
     * To insert new PO record
     * @param body
     */
     $this->post('/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";

        $this->logger->addInfo("Entry: POST: purchases");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        
        // POST Data here
        $body = json_decode($request->getBody(), true);
        extract($body);
        // To convert money format to decimal
        $total = filter_var($total,FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        // Start transaction 
        $db->beginTransaction();
        // insert record to transaction_h
        $sql = "insert into t_transaction_h (trans_code, refer_code, supp_code, prefix, total, employee_code, shop_code, remark, is_void, is_convert, is_settle, create_date) ";
        $sql .= " values (";
        $sql .= " '".$purchases_num."',";
        $sql .= " '".$refer_num."',";
        $sql .= " '".$supp_code."',";
        $sql .= " '".$prefix."',";
        $sql .= " '".$total."',";
        $sql .= " '".$employee_code."',";
        $sql .= " '".$shopcode."',";
        $sql .= " '".$remark."',";
        $sql .= " '0',";
        $sql .= " '0',";
        $sql .= " '0',";
        $sql .= " '".$date."'";
        $sql .= " );";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        // insert record to transaction_d
        if(!empty($db->lastInsertId()))
        {
            foreach($items as $k => $v)
            {
                $sql = "insert into t_transaction_d (trans_code, item_code, eng_name, chi_name, qty, unit, price, create_date)";
                $sql .= " values (";
                $sql .= " '".$purchases_num."',";
                $sql .= " '".$v['item_code']."',";
                $sql .= " '".$v['eng_name']."' ,";
                $sql .= " '".$v['chi_name']."' ,";
                $sql .= " '".$v['qty']."',";
                $sql .= " '".$v['unit']."',";
                $sql .= " '".$v['price']."',";
                $sql .= " '".$date."'";
                $sql .= " );";
                $q = $db->prepare($sql);
                $q->execute();
            }
            $_err[] = $q->errorinfo();
            // tender information input here
            $sql = "insert into t_transaction_t (trans_code, pm_code, total, create_date)";
            $sql .= " values (";
            $sql .= " '".$purchases_num."',";
            $sql .= " '".$paymentmethod."',";
            $sql .= " '".$total."',";
            $sql .= " '".$date."'";
            $sql .= " );";
            $tr = $db->prepare($sql);
            $tr->execute();
            $_err[] = $tr->errorinfo();
        }

        $db->commit();
        $this->logger->addInfo("Msg: DB commit");
        //disconnection DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");

        // var_dump($_err);
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
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Transaction: ".$purchases_num." - Insert OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Insert Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });
    
    /**
     * Delete request
     * To delete PO record
     * @param body
     */
    $this->delete('/{trans_code}', function (Request $request, Response $response, array $args) {
        $_trans_code = $args['trans_code'];
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";

        $this->logger->addInfo("Entry: Delete: purchases");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        // transaction start
        $db->beginTransaction();
        // sql statement
        $sql = "UPDATE `t_transaction_h` SET is_void = '1' WHERE trans_code = '".$_trans_code."';";
        // $this->logger->addInfo("SQL: ".$sql);

        // prepare sql statement
        $q = $db->prepare($sql);
        // execute statement
        $q->execute();
        $_err[] = $q->errorinfo();

        // transaction end
        $db->commit();
        $this->logger->addInfo("Msg: DB commit");
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
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Transaction: ".$_trans_code." - Deleted!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Delete Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

     /**
     * View of purchase
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

            //SQL 3
            $sql = "SELECT prefix FROM `t_prefix` WHERE `uid` = '2' LIMIT 1;";
            //echo $sql3."\n";
            $q = $db->prepare($sql);
            $q->execute();
            $_err[] = $q->errorinfo();
            $_data['prefix'] = $q->fetch(PDO::FETCH_ASSOC);

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