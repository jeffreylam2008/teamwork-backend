<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->group('/api/v1/systems/employees', function () {
    /**
     * employee GET request
     * employee-get
     * 
     * To get all employee information 
     */
    $this->get('/', function (Request $request, Response $response, array $args) {
        $err = [];
        $pdo = new Database();
		$db = $pdo->connect_db();
        $q = $db->prepare(
            "SELECT emp.employee_code, 
            emp.username, 
            emp.default_shopcode,
            shp.name as `shop_name`,
            emp.access_level, 
            emp.role, 
            emp.status 
            FROM `t_employee` as `emp`LEFT JOIN `t_shop` as `shp` ON
            emp.default_shopcode = shp.shop_code;");
        $q->execute();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        $err = $q->errorinfo();
        // disconnect DB
        $pdo->disconnect_db();
        
        if(!empty($res))
        {
            foreach ($res as $key => $val) {
                $dbData[] = $val;
            }
            $callback = [
                "query" => $dbData,
                "error" => ["code" => $err[0], "message" => $err[1]." ".$err[2]]
            ];
            return $response->withJson($callback, 200);
        }
    });
    /**
     * employee GET request
     * employee-get
     * 
     * To get all employee information 
     */
    $this->get('/{username}', function (Request $request, Response $response, array $args) {
        $_username = $args['username'];
        $err = "";
        $pdo = new Database();
		$db = $pdo->connect_db();
        $q = $db->prepare("select `employee_code`, `username`, `default_shopcode`, `access_level`, `role`, `status` from `t_employee` where `username` = '".$_username."';");
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
     * employee GET request
     * employee-get
     * 
     * To get all employee information 
     */
    $this->get('/code/{employee_code}', function (Request $request, Response $response, array $args) {
        $_emp_code = $args['employee_code'];
        $err1 = [];
        $err[0] = "";
        $err[1] = "";
        $_data = "";
        $pdo = new Database();
		$db = $pdo->connect_db();
        $q = $db->prepare("select `username`, `default_shopcode`, `access_level`, `role`, `status` from `t_employee` where `employee_code` = '".$_emp_code."';");
        $q->execute();
        $err1 = $q->errorinfo();
        
        // SQL Query success
        if($err1[0] == "00000")
        {
            $err[0] = "00000";
            $err[1] = "";
            $_data = $q->fetch();
        }
        else
        {
            $err[0] = "90002";
            $err[1] = "Something went wrong on fetch employee API";
        }
        // disconnect DB
        $pdo->disconnect_db();

        if(!empty($_data))
        {
            $callback = [
                "query" => $_data,
                "error" => [
                    "code" => $err[0], 
                    "message" => $err[1]
                ]
            ];
            return $response->withJson($callback, 200);
        }
    });

});