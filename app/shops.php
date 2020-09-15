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
        $err = [];
        $pdo = new Database();
		$db = $pdo->connect_db();
        $q = $db->prepare("select * from `t_shop`;");
        $q->execute();
        $err = $q->errorinfo();
        // disconnect DB
        $pdo->disconnect_db();
        
        //$result = $db->query( "select * from `t_shop`;");
        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $key => $val) {
            $dbData[] = $val;
        }
        $callback = [
            "query" => $dbData,
            "error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
        ];
        return $response->withJson($callback, 200);
    });

    /**
     * Shop GET request with ID
     * shop-get
     * 
     * To get shop record 
     */
     $this->get('/{shop_code}', function (Request $request, Response $response, array $args) {
        $err = [];
        $pdo = new Database();
        $db = $pdo->connect_db();
        $_shop_code = $args['shop_code'];
        $q = $db->prepare("
            SELECT * ,
            (SELECT `shop_code` FROM `t_shop` WHERE `shop_code` < '".$_shop_code."' ORDER BY `shop_code` DESC LIMIT 1) as `previous`,
            (SELECT `shop_code` FROM `t_shop` WHERE `shop_code` > '".$_shop_code."' ORDER BY `shop_code` LIMIT 1) as `next`
            FROM `t_shop` 
            WHERE `shop_code` = '".$_shop_code."';
        ");
        $q->execute();
        $err = $q->errorinfo();
        $res = $q->fetch(PDO::FETCH_ASSOC);
        // disconnect DB
        $pdo->disconnect_db();
        if(!empty($res))
        {
            $callback = [
                "query" => $res,
                "error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
            ];
            return $response->withJson($callback, 200);
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
        $pdo = new Database();
		$db = $pdo->connect_db();
        $_shop_code = $args['shop_code'];
        $_body = json_decode($request->getBody(), true);
        $_now = date('Y-m-d H:i:s');

        $db->beginTransaction();
        $q = $db->prepare("UPDATE `t_shop` SET 
        `name` = '".$_body['i-name']."',
        `phone` = '".$_body['i-phone']."',
        `address1` = '".$_body['i-address1']."',
        `address2` = '".$_body['i-address2']."',
        `modify_date` = '".$_now."'
        WHERE `shop_code` = '".$_shop_code."';");
        $q->execute();
        $_err = $q->errorinfo();
        $db->commit();
        // disconnect DB
        $pdo->disconnect_db();

        $callback = [
            "query" => "",
            "error" => ["code" => $_err[0], "message" => $_err[1]." ".$_err[2]]
        ];
        return $response->withJson($callback, 200);
    });
});