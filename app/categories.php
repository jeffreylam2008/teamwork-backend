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
	 * Categories POST Request
	 * categories-post
	 * 
	 * To create new record
	 */
	$app->post('/', function(Request $request, Response $response, array $args){
		$_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";

		$this->logger->addInfo("Entry: POST: Category create");
		$pdo = new Database();
		$db = $pdo->connect_db();
		$this->logger->addInfo("Msg: DB connected");

		// POST Data here
		$body = json_decode($request->getBody(), true);
		$_now = date('Y-m-d H:i:s');

		$db->beginTransaction();
		$sql = "INSERT INTO t_items_category (`cate_code`, `desc`, `create_date`)";
		$sql .= " values ('".$body['i-catecode']."', '".$body['i-desc']."', '".$_now."');";
		$q = $db->prepare($sql);
		$q->execute();
		$_err[] = $q->errorinfo();

		$db->commit();
		$this->logger->addInfo("Msg: DB commit");
		// disconnect DB
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
            $_callback['error']['message'] = "Category: ".$body['i-catecode']." - Insert OK!";
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
	 * Categories PATCH Request
	 * categories-patch
	 * 
	 * To update current record on DB
	 */
	$app->patch('/{cate_code}', function(Request $request, Response $response, array $args){
		$_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
		$_cate_code = $args['cate_code'];

		$this->logger->addInfo("Entry: PATCH: Category amended");
		$pdo = new Database();
		$db = $pdo->connect_db();
		$this->logger->addInfo("Msg: DB connected");
		// POST Data here
		$body = json_decode($request->getBody(), true);
		$_now = date('Y-m-d H:i:s');

		$db->beginTransaction();
		$sql = "UPDATE `t_items_category` ";
		$sql .= "SET `desc` = '".$body["i-desc"]."', ";
		$sql .= "`modify_date` = '".$_now."' ";
		$sql .= "WHERE `cate_code` = '".$_cate_code."';";
		$q = $db->prepare($sql);
		$q->execute();
		$_err[] = $q->errorinfo();
		$db->commit();
		$this->logger->addInfo("Msg: DB commit");
		// disconnect DB
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
            $_callback['error']['message'] = "Category: ".$_cate_code." - Update OK!";
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
	 * Categories DELETE Request
	 * categories-delete
	 * 
	 * To remove record from DB
	 */
	$app->delete('/{cate_code}', function(Request $request, Response $response, array $args){
		$_cate_code = $args['cate_code'];
		$_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
		
		$this->logger->addInfo("Entry: DELETE: Category delete");
		$pdo = new Database();
		$db = $pdo->connect_db();
		$this->logger->addInfo("Msg: DB connected");
		$sql = "DELETE FROM `t_items_category` WHERE cate_code = '".$_cate_code."';";
		$q = $db->prepare($sql);
		$q->execute();
		$_err[] = $q->errorinfo();
		
		// disconnect DB
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
            $_callback['error']['message'] = "Category: ".$_cate_code." - Deleted!";
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

            $this->logger->addInfo("Entry: categories: get header");
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