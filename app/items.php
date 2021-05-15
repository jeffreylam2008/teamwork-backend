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
        
        $pdo = new Database();
		$db = $pdo->connect_db();
        $q = $db->prepare("
            SELECT ti.*, tw.qty as 'stockonhand'
            FROM `t_items` as ti
            LEFT JOIN `t_warehouse` as tw ON ti.item_code = tw.item_code
            WHERE cate_code IN ( '".$_req."');
        ");
        
        $q->execute();
        $dbData = $q->fetchAll();
        $err = $q->errorinfo();
        //disconnection DB
        $pdo->disconnect_db();

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
        $pdo = new Database();
		$db = $pdo->connect_db();
        $q = $db->prepare("
            SELECT *,
            (SELECT `item_code` FROM `t_items` WHERE `item_code` < '".$_item_code."' ORDER BY `item_code` DESC LIMIT 1) as `previous`,
            (SELECT `item_code` FROM `t_items` WHERE `item_code` > '".$_item_code."' ORDER BY `item_code` LIMIT 1) as `next`
            FROM `t_items` WHERE item_code = '".$_item_code."';"
        );
        $q->execute();
        $dbData = $q->fetch();
        $err = $q->errorinfo();
        //disconnection DB
        $pdo->disconnect_db();

        $callback = [
            "query" => $dbData,
            "error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
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

});