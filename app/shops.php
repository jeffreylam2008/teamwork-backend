<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/systems/shops', function () {
    /**
     * Shop GET request
     * shop-get
     * 
     * To get shop record 
     */
    $this->get('/', function (Request $request, Response $response, array $args) {
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_err = [];
        $_data = [];
        $_msg = "";
        $_result = true;
        
        $this->logger->addInfo("Entry: shops: get all shops information");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = "select * from `t_shop`;";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        // disconnect DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");

        if($q->rowCount() != 0)
        {
            //$result = $db->query( "select * from `t_shop`;");
            $_data = $q->fetchAll(PDO::FETCH_ASSOC);
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

    /**
     * Shop GET request with ID
     * shop-get
     * 
     * To get shop record 
     */
    $this->get('/{shop_code}', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_shop_code = $args['shop_code'];

        $this->logger->addInfo("Entry: shops: get shop info by shop_code");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        
        $sql = "SELECT * ,";
        $sql .= " (SELECT `shop_code` FROM `t_shop` WHERE `shop_code` < '".$_shop_code."' ORDER BY `shop_code` DESC LIMIT 1) as `previous`,";
        $sql .= " (SELECT `shop_code` FROM `t_shop` WHERE `shop_code` > '".$_shop_code."' ORDER BY `shop_code` LIMIT 1) as `next`";
        $sql .= " FROM `t_shop` ";
        $sql .= " WHERE `shop_code` = '".$_shop_code."';";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        $_res = $q->fetch(PDO::FETCH_ASSOC);

        // disconnect DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");
        if($q->rowCount() != 0)
        {
            $_data = $_res;
        }
        else
        {
            $_result = false;
        }
        //var_dump($_data);
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
     * Shop Patch request
     * shop-patch
     * 
     * To patch shop record 
     */
    $this->patch('/{shop_code}', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_shop_code = $args['shop_code'];
        $_now = date('Y-m-d H:i:s');
        $_body = json_decode($request->getBody(), true);

        $this->logger->addInfo("Entry: PATCH: shops");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        
        
        $db->beginTransaction();
        $sql = "UPDATE `t_shop` SET";
        $sql .= " `name` = '".$_body['i-name']."',";
        $sql .= " `phone` = '".$_body['i-phone']."',";
        $sql .= " `address1` = '".$_body['i-address1']."',";
        $sql .= " `address2` = '".$_body['i-address2']."',";
        $sql .= " `modify_date` = '".$_now."'";
        $sql .= " WHERE `shop_code` = '".$_shop_code."';";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
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
            $_callback['error']['message'] = "Transaction: ".$_shop_code." - Update OK!";
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
     * View of Items
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

            $this->logger->addInfo("Entry: shop: get header");
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