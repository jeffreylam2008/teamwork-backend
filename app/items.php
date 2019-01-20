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