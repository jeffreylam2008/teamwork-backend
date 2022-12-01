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
        $err=[];
        $_cate_code = $args['cate_code'];
        $pdo = new Database();
		$db = $pdo->connect_db();
        $q = $db->prepare("select count(*) as counter from `t_items` WHERE cate_code = '".$_cate_code."';");
        $q->execute();
        $dbData = $q->fetch();
        $err = $q->errorinfo();
        //disconnection DB
        $pdo->disconnect_db();

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
        $this->logger->addInfo("SQL: ".$sql);
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
        $err=[];
        $_item_code = $args['item_code'];
        $this->logger->addInfo("Entry: items: edit session");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        $sql = "SELECT *,";
        $sql .= "tw.qty as stockonhand, ";
        $sql .= "(SELECT `item_code` FROM `t_items` WHERE `item_code` < '".$_item_code."' ORDER BY `item_code` DESC LIMIT 1) as `previous`, ";
        $sql .= "(SELECT `item_code` FROM `t_items` WHERE `item_code` > '".$_item_code."' ORDER BY `item_code` LIMIT 1) as `next` ";
        $sql .= "FROM `t_items` as ti ";
        $sql .= "LEFT JOIN `t_warehouse` as tw ON ti.item_code = tw.item_code ";
        $sql .= "WHERE ti.item_code = '".$_item_code."';";
        
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
        $_err=[];
        $pdo = new Database();
        $db = $pdo->connect_db();
        
        // POST Data here
        $body = json_decode($request->getBody(), true);
        //extract($body);
        $db->beginTransaction();
        
        $_now = date('Y-m-d H:i:s');
        $q = $db->prepare("insert into t_items (`item_code`, `eng_name` ,`chi_name`, `desc`, `price`, `price_special`, `type`, `cate_code`,`unit`, `image_name`, `image_body`, `create_date`) 
            values (
                '".$body['i-itemcode']."',
                '".$body['i-engname']."',
                '".$body['i-chiname']."',
                '".$body['i-desc']."',
                '".$body['i-price']."',
                '".$body['i-specialprice']."',
                '".$body['i-type']."',
                '".$body['i-category']."',
                '".$body['i-unit']."',
                '".$body['i-img']['name']."',
                '".$body['i-img']['content']."',
                '".$_now."'
            );
        ");

        $q->execute();
        $_err = $q->errorinfo();

        // create warehouse record
        $q = $db->prepare("insert into t_warehouse (`item_code`, `qty` ,`type`, `create_date`) 
            values (
                '".$body['i-itemcode']."',
                '0',
                'in',
                '".$_now."'
            );
        ");
        $q->execute();
        $_err = $q->errorinfo();
        
        $db->commit();
        //disconnection DB
        $pdo->disconnect_db();
        if($_err[0] === "00000")
        {
            $_err[1] = $body['i-itemcode'] . " - Created Successful!";
        }
        $callback = [
            "query" => "",
            "error" => ["code" => $_err[0], "message" => $_err[1]." ".$_err[2]]
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
        $_err=[];
        $_file = "";
        $_item_code = $args['item_code'];
        $pdo = new Database();
		$db = $pdo->connect_db();
        $_now = date('Y-m-d H:i:s');
        //POST Data here
        $body = json_decode($request->getBody(), true);


        if(array_key_exists('i-img', $body))
        {
            $_file = "`image_name` = '".$body['i-img']['name']."',
            `image_body` = '".$body['i-img']['content']."',";
        }
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
        ".$_file."
        `modify_date` = '".$_now."'
        WHERE `item_code` = '".$_item_code."';");
        
        $q->execute();
        $_err = $q->errorinfo();
        $db->commit();
        //disconnection DB
        $pdo->disconnect_db();
        if($_err[0] === "00000")
        {
            $_err[1] = $_item_code . " - Edit Successful!";
        }
        $callback = [
            "query" => "",
            "error" => ["code" => $_err[0], "message" => $_err[1]." ".$_err[2]]
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
        $pdo = new Database();
		$db = $pdo->connect_db();
        $q = $db->prepare("DELETE FROM `t_items` WHERE item_code = '".$_item_code."';");
        $q->execute();
        $_err = $q->errorinfo();
        // delete item from warehouse
        $q = $db->prepare("DELETE FROM `t_warehouse` WHERE item_code = '".$_item_code."';");
        $q->execute();
        $_err = $q->errorinfo();

        //disconnection DB
        $pdo->disconnect_db();
        if($_err[0] === "00000")
        {
            $_err[1] = $_item_code." - Deleted Successful!";
        }

        $callback = [
            "query" => "",
            "error" => ["code" => $_err[0], "message" => $_err[1]." ".$_err[2]]
        ];
    
        return $response->withJson($callback, 200);
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