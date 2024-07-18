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

        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_err = [];
        $_data = [];
        $_msg = "";
        $_result = true;
        $this->logger->addInfo("Entry: employees: get all employee");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $sql = "SELECT emp.employee_code, ";
        $sql .= "emp.username, ";
        $sql .= "emp.default_shopcode,";
        $sql .= "shp.name as `shop_name`,";
        $sql .= "role.access_level,";
        $sql .= "emp.role_code,"; 
        $sql .= "emp.status,";
        $sql .= "(CASE WHEN emp.status=1 THEN 'Active' WHEN emp.status=0 THEN 'Disabled' END) as `status_name`";
        $sql .= "FROM `t_employee` as `emp` ";
        $sql .= "LEFT JOIN `t_shop` as shp ON emp.default_shopcode = shp.shop_code ";
        $sql .= "LEFT JOIN `t_employee_role` as role ON emp.role_code = role.role_code;";
        $q = $db->prepare($sql);
        $q->execute();
        $_err[] = $q->errorinfo();
        if($q->rowCount() != 0)
        {
            $_data = $q->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            $_result = false;
        }
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
        $_err = [];
        $_data = "";
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";

        $this->logger->addInfo("Entry: Employee: Get employee info by emp_code");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $sql = "select emp.employee_code, emp.username, emp.default_shopcode, shp.name as `shop_name`, ";
        $sql .= "emp.status, role.role_code, role.access_level, role.name as role_name ";
        $sql .= "from `t_employee` emp ";
        $sql .= "LEFT JOIN `t_shop` shp ";
        $sql .= "ON emp.default_shopcode = shp.shop_code ";
        $sql .= "LEFT JOIN `t_employee_role` role ";
        $sql .= "ON emp.role_code = role.role_code ";
        $sql .= "where emp.employee_code = '".$_emp_code."';";
        $q = $db->prepare($sql);
        $q->execute();
        $_data = $q->fetch();
        $_err[] = $q->errorinfo();

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
     * employee GET request
     * employee-get
     * 
     * To get employee roles information 
     */
    $this->get('/roles/', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        
        $this->logger->addInfo("Entry: Employee: Get Roles");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $sql = "SELECT * FROM `t_employee_role`;";
    
        $q = $db->prepare($sql);
        $q->execute();
        $_data = $q->fetchAll(PDO::FETCH_ASSOC);
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
     * employee Patch request
     * employee-patch
     * 
     * To update employee information by employee ID
     */
    $this->patch('/{employee_code}', function (Request $request, Response $response, array $args) {
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_salt = "password";
        $_pwd = "";
		$_employee_code = $args['employee_code'];

        $this->logger->addInfo("Entry: Employee: Patch employee");
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
            $sql = "UPDATE `t_employee` SET ";
            $sql .= "`employee_code` = '".$_body['i-emp-code']."', ";
            $sql .= "`username` = '".$_body['i-username']."', ";
            $sql .= "`default_shopcode` = '".$_body['i-shops']."', ";
            $sql .= "`password` = '".$_pwd."', ";
            $sql .= "`role_code` = '".$_body['i-emp-roles']."',";
            $sql .= "`status` = '".$_body['i-status']."', ";
            $sql .= "`modify_date` = '".$_now."' ";
            $sql .= "WHERE `employee_code` = '".$_employee_code."'; ";
            
        }
        else
        {
            $sql = "UPDATE `t_employee` SET ";
            $sql .= "`employee_code` = '".$_body['i-emp-code']."', ";
            $sql .= "`username` = '".$_body['i-username']."', ";
            $sql .= "`default_shopcode` = '".$_body['i-shops']."', ";
            $sql .= "`role_code` = '".$_body['i-emp-roles']."',";
            $sql .= "`status` = '".$_body['i-status']."', ";
            $sql .= "`modify_date` = '".$_now."' ";
            $sql .= "WHERE `employee_code` = '".$_employee_code."';";
        }
        $q = $db->prepare($sql);
        $q->execute();

		// no fatch on update 
        $_err[] = $q->errorinfo();
        $db->commit();
    
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
            $_callback['query'] = "";
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Data Update OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['query'] = "";
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Data Update Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * employee POST request
     * employee-post
     * 
     * To insert new employee 
     */
    $this->post('/', function (Request $request, Response $response, array $args) {
        $_salt = "password";
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_data = [];
        $_pwd = "";

        $this->logger->addInfo("Entry: Employee: Do Insert");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

		// POST Data here
        $_body = json_decode($request->getBody(), true);
        $_now = date('Y-m-d H:i:s');
        //start DB transaction
        $db->beginTransaction();
        $_pwd = crypt($_body['i-pwd'], $_salt);

        $sql = "INSERT INTO `t_employee` (";
        $sql .= " `employee_code`, `username`, `default_shopcode`, `password`, `role_code`, `status`, `create_date`)";
        $sql .= " VALUES ('".$_body['i-emp-code']."',";
        $sql .= " '".$_body['i-username']."', ";
        $sql .= " '".$_body['i-shops']."',";
        $sql .= " '".$_pwd."',";
        $sql .= " '".$_body['i-emp-roles']."',";
        $sql .= " '".$_body['i-status']."',";
        $sql .= " '".$_now."');";
        //$this->logger->addInfo($sql);        
        $q = $db->prepare($sql);
        $q->execute();

		// no fatch on update 
        $_err[] = $q->errorinfo();
        $db->commit();
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
            $_callback['query'] = $_data;
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "User Created - OK!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['query'] = "";
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Create User Failure - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });

    /**
     * employee DEL request
     * To delete employee
     */
    $this->delete('/{employee_code}', function (Request $request, Response $response, array $args){
        $_emp_code = $args['employee_code'];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        $_err = [];
        $this->logger->addInfo("Entry: DELETE: employee delete");
        $pdo = new Database();
		$db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");
        $sql = "DELETE FROM t_employee WHERE employee_code = '".$_emp_code."';";
        $this->logger->addInfo($sql);

        $q = $db->prepare($sql);
        $q->execute();
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
            $_callback['error']['message'] = "Employee: ".$_emp_code." - Deleted!";
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