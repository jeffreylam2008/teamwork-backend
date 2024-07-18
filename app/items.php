<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/products/items', function () {
    /**
     * Items GET Request
     * item-get
     * 
     * To get all items record
     */
    $this->get('/', function (Request $request, Response $response, array $args) {
        $err=[];
        $_param = $request->getQueryParams();
        if(empty($_param["show_start"]) && empty($_param["show_end"]))
        {
            $show = "";
        }
        else
        {
            if($_param["show_start"] >= $_param["show_end"])
                $_param["show_end"] = 99999;
            $show = "LIMIT ".$_param["show_start"].", ".($_param["show_end"] - $_param["show_start"]);
        }
            
        $pdo = new Database();
        $db = $pdo->connect_db();
        $q = $db->prepare("
            SELECT 
                ti.uid,
                ti.item_code, 
                ti.eng_name,
                ti.chi_name,
                ti.desc,
                ti.price,
                ti.price_special,
                ti.cate_code,
                ti.unit,
                tw.qty as 'stockonhand', 
                tw.type 
            FROM `t_items` as  ti
            LEFT JOIN `t_warehouse` as tw ON ti.item_code = tw.item_code
            ORDER BY ti.item_code ".$show
        );
        $q->execute();
        $err = $q->errorinfo();
        //disconnection DB
        $pdo->disconnect_db();


        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $key => $val) {
            $dbData[] = $val;
        }
        $callback = [
            "query" => $dbData,
            "error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
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
        $_cate_code = $args['cate_code'];
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];

        $this->logger->addInfo("Entry: Category: check any item in use");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        $sql = "SELECT * from `t_items` WHERE cate_code = '".$_cate_code."';";
        $this->logger->addInfo($sql);
        $q = $db->prepare($sql);
        $q->execute();
        $_data = $q->fetch();
        $_err[] = $q->errorinfo();
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
    });

    /**
     * Items GET
     * items-get-by
     */
    $this->get('/category/{attr:.*}', function(Request $request, Response $response, array $args){
        $_req = [];
        $_req = $request->getAttribute('attr');
        $_req = str_replace("/","','", $_req);
        
        $this->logger->addInfo("Entry: items: Search by catgory session");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        $sql = "SELECT ti.*, tw.qty as stockonhand ";
        $sql .= "FROM `t_items` as ti ";
        $sql .= "LEFT JOIN `t_warehouse` as tw ON ti.item_code = tw.item_code ";
        $sql .= "WHERE cate_code IN ( '".$_req."'); ";
        //$this->logger->addInfo("SQL: ".$sql);
        $q = $db->prepare($sql);

        $q->execute();
        $dbData = $q->fetchAll();
        $err = $q->errorinfo();
        //disconnection DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");

        $callback = [
            "query" => $dbData,
            "error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
        ];
        return $response->withJson($callback, 200);
    });

    /**
     * Items GET Request
     * items-get-by-code
     * 
     * To get single items record based on the code
     */
    $this->get('/{item_code}', function(Request $request, Response $response, array $args){
        $_err=[];
        $_result = true;
        $_msg = "";
        $_callback = [];
        $_query = [];
        $_item_code = $args['item_code'];

        if(!empty($_item_code))
        {       
            $this->logger->addInfo("Entry: items: Get item by ID");
            $pdo = new Database();
            $db = $pdo->connect_db();
            $this->logger->addInfo("Msg: DB connected");
            $sql = "SELECT ti.*,";
            $sql .= "tw.qty as stockonhand, ";
            $sql .= "(SELECT `item_code` FROM `t_items` WHERE `item_code` < '".$_item_code."' ORDER BY `item_code` DESC LIMIT 1) as `previous`, ";
            $sql .= "(SELECT `item_code` FROM `t_items` WHERE `item_code` > '".$_item_code."' ORDER BY `item_code` LIMIT 1) as `next` ";
            $sql .= "FROM `t_items` as ti ";
            $sql .= "LEFT JOIN `t_warehouse` as tw ON ti.item_code = tw.item_code ";
            $sql .= "WHERE ti.item_code LIKE '%".$_item_code."%';";
            // For Debug Only
            $this->logger->addInfo($sql);
            $q = $db->prepare($sql);
            
            $q->execute();
            $_query = $q->fetchAll();
            $_err[] = $q->errorinfo();
            //disconnection DB
            $pdo->disconnect_db();
            $this->logger->addInfo("Msg: DB connection closed");
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
            $this->logger->addInfo("SQL execute : 404 not found!");
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * Items GET
     * get-total-active-items-count
     * 
     * To get total number of items
     */
    $this->get('/count/',function(Request $request, Response $response, array $args){
        $err1 = [];
        $err[0] = "";
        $err[1] = "";

        $pdo = new Database();
		$db = $pdo->connect_db();
        $q = $db->prepare("SELECT count(*) as count FROM `t_items`;");
        $q->execute();
        $err1 = $q->errorinfo();
        $res = $q->fetch(PDO::FETCH_ASSOC);

        if($err1[0] == "00000")
        {
            $err[0] = $err1[0];
            $err[1] = "Success!<br>DB: " .$err1[1]. " ".$err1[2];
        } 
        else
        {
            $err[0] = $err1[0];
            $err[1] = "Error<br>DB: " .$err1[1]. " ".$err1[2];
        }

        $callback = [
            "query" => $res, 
            "error" => ["code" => $err[0], "message" => $err[1]]
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
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";

        $this->logger->addInfo("Entry: POST: item create");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        // POST Data here
        $body = json_decode($request->getBody(), true);
        // extract($body);
        $db->beginTransaction();
        
        $_now = date('Y-m-d H:i:s');
        $sql = "INSERT INTO t_items (`item_code`, `eng_name` ,`chi_name`, `desc`, `price`, `price_special`, `type`, `cate_code`,`unit`, `image_name`, `image_body`, `create_date`) ";
        $sql .= " VALUES (";
        $sql .= " '".$body['i-itemcode']."',";
        $sql .= " '".$body['i-engname']."',";
        $sql .= " '".$body['i-chiname']."',";
        $sql .= " '".$body['i-desc']."',";
        $sql .= " '".$body['i-price']."',";
        $sql .= " '".$body['i-specialprice']."',";
        $sql .= " '".$body['i-type']."',";
        $sql .= " '".$body['i-category']."',";
        $sql .= " '".$body['i-unit']."',";
        $sql .= " '".$body['i-img']['name']."',";
        $sql .= " :img_data,";
        $sql .= " '".$_now."');";

        // for debug only 
        // $this->logger->addInfo("Msg: ".$sql);
        $q = $db->prepare($sql);
        $q->bindParam(':img_data', $body['i-img']['content'], PDO::PARAM_LOB);
        $q->execute();
        $_err[] = $q->errorinfo();

        // create warehouse record
        $sql = "insert into t_warehouse (`item_code`, `qty` ,`type`, `create_date`)";
        $sql .= " values (";
        $sql .= " '".$body['i-itemcode']."',";
        $sql .= " '0',";
        $sql .= " 'in',";
        $sql .= " '".$_now."');";
    
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        
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
            $_callback['error']['message'] = "Item: ".$body['i-itemcode']." - Insert OK!";
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
     * Items PATCH Request
     * items-patch
     * 
     * To update existing record
     */
    $this->patch('/{item_code}', function(Request $request, Response $response, array $args){
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_file = "";
        $_item_code = $args['item_code'];
        $this->logger->addInfo("Entry: PATCH: item amended");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        $_now = date('Y-m-d H:i:s');
        //POST Data here
        $body = json_decode($request->getBody(), true);

        if(array_key_exists('i-img', $body))
        {
            $_file = "`image_name` = '".$body['i-img']['name']."', `image_body` = :img_data,";
            // ".$body['i-img']['content']."
        }
        $db->beginTransaction();
        $sql = "UPDATE `t_items` SET";
        $sql .= " `eng_name` = '".$body['i-engname']."',";
        $sql .= " `chi_name` = '".$body['i-chiname']."',";
        $sql .= " `desc` = '".$body['i-desc']."',";
        $sql .= " `price` = '".$body['i-price']."',";
        $sql .= " `price_special` = '".$body['i-specialprice']."',";
        $sql .= " `cate_code` = '".$body['i-category']."',";
        $sql .= " `type` = '".$body['i-type']."',";
        $sql .= " `unit`= '".$body['i-unit']."',";
        $sql .= " ".$_file."";
        $sql .= "  `modify_date` = '".$_now."'";
        $sql .= " WHERE `item_code` = '".$_item_code."';";
        // debug SQL statement
        // $this->logger->addInfo("Msg: ".$sql);
        $q = $db->prepare($sql);
        $q->bindParam(':img_data', $body['i-img']['content'], PDO::PARAM_LOB);
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
            $_callback['error']['message'] = "Item: ".$_item_code." - Update OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Update Failure - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * Items DELETE Request
     * items-delete
     * 
     * To delete items record from DB
     */
    $this->delete('/{item_code}', function (Request $request, Response $response, array $args) {
        $_item_code = $args['item_code'];
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $this->logger->addInfo("Entry: DELETE: item delete");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        $q = $db->prepare("DELETE FROM `t_items` WHERE item_code = '".$_item_code."';");
        $q->execute();
        $_err[] = $q->errorinfo();
        // delete item from warehouse
        $q = $db->prepare("DELETE FROM `t_warehouse` WHERE item_code = '".$_item_code."';");
        $q->execute();
        $_err[] = $q->errorinfo();

        //disconnect DB
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
            $_callback['error']['message'] = "Item: ".$_item_code." - Deleted!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Delete Failure - Please try again!";
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

            $this->logger->addInfo("Entry: items: get header");
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