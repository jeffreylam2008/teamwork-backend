<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/products/categories', function() use($app) {
	/**
	 * Categories GET Request
	 * categories-get
	 * 
	 * To get all record
	 */
	$app->get('/', function (Request $request, Response $response, array $args) {
		$err="";
		$pdo = new Database();
		$db = $pdo->connect_db();
		$q = $db->prepare("SELECT * FROM `t_items_category` ORDER BY 'cate_code';");
		$q->execute();
		$err = $q->errorinfo();
		foreach ($row = $q->fetchAll(PDO::FETCH_ASSOC) as $key => $val) {
			$dbData[] = $val;
		}
		// disconnect DB
		$pdo->disconnect_db();
		if(!$row)
		{
			return $response->withJson("Not Found!", 404);
		}
		else{
			$callback = [
				"query" => $dbData,
				"error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
			];
			return $response->withJson($callback, 200);
		}
	});
	/**
	 * Categories GET Request
	 * categories-get-by-code
	 * 
	 * To get single record base on code
	 */
	$app->get('/{cate_code}', function(Request $request, Response $response, array $args){
		$err="";
		$_cate_code = $args['cate_code'];
		$pdo = new Database();
		$db = $pdo->connect_db();
		$q = $db->prepare("
			SELECT *,
			(SELECT `cate_code` FROM `t_items_category` WHERE `cate_code` < '".$_cate_code."' ORDER BY `cate_code` DESC LIMIT 1) as `previous`,
        	(SELECT `cate_code` FROM `t_items_category` WHERE `cate_code` > '".$_cate_code."' ORDER BY `cate_code` LIMIT 1) as `next`
			FROM `t_items_category` 
			WHERE cate_code = '".$_cate_code."';
		");
		$q->execute();
		$dbData = $q->fetch();
		$err = $q->errorinfo();
		// disconnect DB
		$pdo->disconnect_db();

		$callback = [
			"query" => $dbData,
			"error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
		];
		return $response->withJson($callback, 200);
	});
	/**
	 * Categories PATCH Request
	 * categories-patch
	 * 
	 * To update current record on DB
	 */
	$app->patch('/{cate_code}', function(Request $request, Response $response, array $args){
		$err = [];
		$_cate_code = $args['cate_code'];
		$pdo = new Database();
		$db = $pdo->connect_db();
		// POST Data here
		$body = json_decode($request->getBody(), true);
		$_now = date('Y-m-d H:i:s');
		$db->beginTransaction();
		$q = $db->prepare("UPDATE `t_items_category` SET `desc` = '".$body["i-desc"]."', `modify_date` = '".$_now."' WHERE `cate_code` = '".$_cate_code."';");
		$q->execute();
		// no fatch on update 
		$err = $q->errorinfo();
		$db->commit();
		// disconnect DB
		$pdo->disconnect_db();
		
		$callback = [
			"query" => "",
			"error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
		];
		return $response->withJson($callback,200);
		
	});
	/**
		* Categories POST Request
		* categories-post
		* 
		* To create new record
		*/
	$app->post('/', function(Request $request, Response $response, array $args){
		$err = [];
		$pdo = new Database();
		$db = $pdo->connect_db();
		// POST Data here
		$body = json_decode($request->getBody(), true);
		$_now = date('Y-m-d H:i:s');
		$db->beginTransaction();
		$q = $db->prepare("insert into t_items_category (`cate_code`, `desc`, `create_date`) values ('".$body['i-catecode']."', '".$body['i-desc']."', '".$_now."');");
		$q->execute();
		// no fatch on insert
		$err = $q->errorinfo();
		$db->commit();
		// disconnect DB
        $pdo->disconnect_db();
		
		$callback = [
			"query" => "", 
			"error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
		];
		return $response->withJson($callback, 200);
	});
	/**
		* Categories DELETE Request
		* categories-delete
		* 
		* To remove record from DB
		*/
	$app->delete('/{cate_code}', function(Request $request, Response $response, array $args){
		$err = [];
		$_cate_code = $args['cate_code'];
		$pdo = new Database();
		$db = $pdo->connect_db();
		$q = $db->prepare("DELETE FROM `t_items_category` WHERE cate_code = '".$_cate_code."';");
		$q->execute();
		$dbData = $q->fetch();
		$err = $q->errorinfo();
		// disconnect DB
		$pdo->disconnect_db();

		$callback = [
			"query" => $dbData,
			"error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
		];

		return $response->withJson($callback, 200);
	});
});