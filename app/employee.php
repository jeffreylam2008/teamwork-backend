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
        $q = $db->prepare("
            SELECT emp.employee_code, 
            emp.username, 
            emp.default_shopcode,
            shp.name as `shop_name`,
            role.access_level,
            emp.role_code, 
            emp.status,
            (CASE WHEN emp.status=1 THEN 'Active' WHEN emp.status=0 THEN 'Disabled' END) as `status_name`
            FROM `t_employee` as `emp` 
            LEFT JOIN `t_shop` as shp ON emp.default_shopcode = shp.shop_code 
            LEFT JOIN `t_employee_role` as role ON emp.role_code = role.role_code;
        ");
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
        $q = $db->prepare("select t1.employee_code,
            t1.username,
            t1.default_shopcode,
            t2.name, 
            t1.role_code, 
            t1.status
            from `t_employee` as t1
            LEFT JOIN `t_shop` as t2
            ON t1.default_shopcode = t2.shop_code where t1.username = '".$_username."';");
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
        $q = $db->prepare("
            select emp.employee_code, emp.username, emp.default_shopcode, shp.name as `shop_name`,
            emp.status, role.role_code, role.access_level, role.name
            from `t_employee` emp 
            LEFT JOIN `t_shop` shp 
            ON emp.default_shopcode = shp.shop_code
            LEFT JOIN `t_employee_role` role
            ON emp.role_code = role.role_code
            where emp.employee_code = '".$_emp_code."';");
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
    
    /**
     * employee Patch request
     * employee-patch
     * 
     * To update employee information by employee ID
     */
    $this->patch('/{employee_code}', function (Request $request, Response $response, array $args) {
        $err1 = [];
        $_salt = "password";
        $err[0] = "";
        $err[1] = "";
        $_data = "";
        $_pwd = "";
		$_employee_code = $args['employee_code'];
		$pdo = new Database();
		$db = $pdo->connect_db();
		// POST Data here
        $_body = json_decode($request->getBody(), true);
        //var_dump($body);
        $_now = date('Y-m-d H:i:s');
        $db->beginTransaction();

        if(!empty($_body['i-pwd']) && !empty($_body['i-confirm-pwd']))
        {
            $_pwd = crypt($_body['i-pwd'], $_salt);
            $q1 = $db->prepare("
                UPDATE `t_employee` SET
                `employee_code` = '".$_body['i-emp-code']."',
                `username` = '".$_body['i-username']."',
                `default_shopcode` = '".$_body['i-shops']."',
                `password` = '".$_pwd."',
                `status` = '".$_body['i-status']."',
                `modify_date` = '".$_now."'
                WHERE `employee_code` = '".$_employee_code."';
            ");

            // echo  "UPDATE `t_employee` SET
            // `employee_code` = '".$_body['i-emp-code']."',
            // `username` = '".$_body['i-username']."',
            // `default_shopcode` = '".$_body['i-shops']."',
            // `password` = '".$_pwd."',
            // `status` = '".$_body['i-status']."',
            // `modify_date` = '".$_now."'
            // WHERE `employee_code` = '".$_employee_code."';";
        }
        else
        {
            $q1 = $db->prepare("
                UPDATE `t_employee` SET
                `employee_code` = '".$_body['i-emp-code']."',
                `username` = '".$_body['i-username']."',
                `default_shopcode` = '".$_body['i-shops']."',
                `status` = '".$_body['i-status']."',
                `modify_date` = '".$_now."'
                WHERE `employee_code` = '".$_employee_code."';
            ");
        }
        
        $q1->execute();

		// no fatch on update 
        $err1 = $q1->errorinfo();

        $db->commit();
    
        // disconnect DB
        $pdo->disconnect_db();

        if($err1[0] == "00000" )
        {
            $err[0] = $err1[0];
            $err[1] = "Update Completed!";
        } 
        else
        {
            $err[0] = $err1[0];
            $err[1] = "Error! DB: " .$err1[1]. " ".$err1[2];
        }

        $callback = [
			"query" => $_data, 
			"error" => ["code" => $err[0], "message" => $err[1]]
        ];
        
		return $response->withJson($callback, 200);
    });

    /**
     * employee POST request
     * employee-post
     * 
     * To insert new employee 
     */
    $this->post('/', function (Request $request, Response $response, array $args) {
        $err1 = [];
        $_salt = "password";
        $err[0] = "";
        $err[1] = "";
        $_data = "";
        $_pwd = "";

		$pdo = new Database();
		$db = $pdo->connect_db();
		// POST Data here
        $_body = json_decode($request->getBody(), true);
        //var_dump($body);
        $_now = date('Y-m-d H:i:s');
        //start DB transaction
        $db->beginTransaction();
        $_pwd = crypt($_body['i-pwd'], $_salt);

        $q1 = $db->prepare("
            INSERT INTO `t_employee` (
                `employee_code`,
                `username`,
                `default_shopcode`,
                `password`,
                `status`,
                `create_date`
            ) VALUES (
                '".$_body['i-emp-code']."',
                '".$_body['i-username']."',
                '".$_body['i-shops']."',
                '".$_pwd."',
                '".$_body['i-status']."',
                '".$_now."'
            );
        ");
        $q1->execute();

		// no fatch on update 
        $err1 = $q1->errorinfo();

        $db->commit();
    
        // disconnect DB
        $pdo->disconnect_db();

        if($err1[0] == "00000" )
        {
            $err[0] = $err1[0];
            $err[1] = "Update Completed!";
        } 
        else
        {
            $err[0] = $err1[0];
            $err[1] = "Error! DB: " .$err1[1]. " ".$err1[2];
        }

        $callback = [
			"query" => $_data, 
			"error" => ["code" => $err[0], "message" => $err[1]]
        ];
        
		return $response->withJson($callback, 200);
    });
});