<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->group('/api/v1/systems/login', function () {
    /**
     * global variable
     * login expire 
     */
    // $_expire = 86400;
    $_expire = 9999999999999999999999;
    /**
     * login GET Request
     * login-post
     * 
     * To post employee record and get token
     * 
     * Return login token
     */
    $this->post('/', function (Request $request, Response $response, array $args) use($_expire) {
        // POST Data here
        // $body = json_decode($request->getBody(), true);
        
        $_body = json_decode($request->getBody(), true);
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_err = [];
        $_data = [];
        $_msg = "";
        $_result = true;
        $_salt = "password";
        $_token = "";
        $_check = "";
        // current time
        $_now = date('Y-m-d H:i:s');
        $_username = htmlspecialchars($_body['username']);
        $_shopcode = htmlspecialchars($_body['shopcode']);
        $_password = htmlspecialchars($_body['password']);
        

        $this->logger->addInfo("Entry: POST: Login");
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        // SQL statement here
        $sql = "SELECT * FROM `t_employee` WHERE ";
        $sql .= "`username` = '".$_username."' AND `default_shopcode` = '".$_shopcode."';";
        $q = $db->prepare($sql);
        // $this->logger->addInfo($sql);
        $q->execute();
        $_res = $q->fetch();
        $_err['sql'][] = $q->errorinfo();
        $_check = $q->rowCount();
        // check username, passsword and shopcode in database
        if($_check == 1)
        {
            // extract data
            extract($_res);
            // Time Expire Checking
            $lasttime = strtotime( $last_login );
            $curtime = strtotime( $_now );
            // time difference
            $diff = $curtime - $lasttime;
        }
        
        // verify user input
        if(empty($_username) && empty($_password) && empty($_shopcode))
        { 
            $this->logger->addInfo("Msg: usename, password, shopcode is empty");
            $_data = "";
            $_err['api']['code'] = "10012";
            $_err['api']['msg'] = "Username and Password Cannot Be Empty";
            $_callback = [
                "query" => $_data,
                "error" => ["code" => $_err['api']['code'], "message" => $_err['api']['msg']]
            ];
            return $response->withJson($_callback, 404);
        }            
        // username and password and shopcode, DB no record
        if($_check == 0)
        {
            $this->logger->addInfo("Msg: check: ".$_check);
            $_data = "";
            $_err['api']['code'] = "10011 ";
            $_err['api']['msg'] = "Username or Password Incorrect";
            $_callback = [
                "query" => $_data,
                "error" => ["code" => $_err['api']['code'], "message" => $_err['api']['msg']]
            ];
            return $response->withJson($_callback, 401);
        }
        // checking password 
        if(!hash_equals($password, crypt($_password, $_salt)))
        {
            $this->logger->addInfo("Msg: password salt check");
            // if not match
            $_data = "";
            $_err['api']['code'] = "10011";
            $_err['api']['msg'] = "Username or Password Incorrect";
            $_callback = [
                "query" => $_data,
                "error" => ["code" => $_err['api']['code'], "message" => $_err['api']['msg']]
            ];
            return $response->withJson($_callback, 401);
        }
        // check user status valid
        if($status != 1)
        {
            $this->logger->addInfo("Msg: password salt check");
            $_data = "";
            $_err['api']['code'] = "10002";
            $_err['api']['msg'] = "User Account Inactive";
            $_callback = [
                "query" => $_data,
                "error" => ["code" => $_err['api']['code'], "message" => $_err['api']['msg']]
            ];
            return $response->withJson($_callback, 401);
        }
        // within one day (86400 = one day) , then use old token 
        if($diff <= $_expire && !empty($last_token))
        {
           
            $this->logger->addInfo("Msg: check time diff");
            $db->beginTransaction();
            // last token is exist, return last token
            // get back last token
            $sql = "UPDATE `t_login` SET `status` = 'in', `modify_date` = '".$_now."' WHERE";
            $sql .= "  `username` = '".$_username."' AND `shop_code` = '".$_shopcode."' AND `token` = '".$last_token."';";
            //$this->logger->addInfo($sql);

            $q = $db->prepare($sql);
            $q->execute();
            $db->commit();
            $_err['sql'][] = $q->errorinfo();
            $_err['api']['code'] = "00001";
            $_err['api']['msg'] = "Login Successful";
            $_data = $last_token; // return final result
        }
        // token expire and use new token
        else
        {
            $this->logger->addInfo("Msg: time expire");
            // create new login token
            $_token = md5($_username.$_password.date("Y-m-d H:i:s").$_shopcode);
            $db->beginTransaction();
            // logout last token
            $sql = "UPDATE `t_login` SET `status` = 'OUT', `modify_date` = '".$_now."' WHERE";
            $sql .= " `username` = '".$_username."' AND `shop_code` = '".$_shopcode."' AND `status` = 'in';";
            $this->logger->addInfo($sql);

            $q = $db->prepare($sql);
            $q->execute();
            $_err['sql'][] = $q->errorinfo();

            $sql = "INSERT INTO `t_login` (";
            $sql .= " `username`,";
            $sql .= " `shop_code`,";
            $sql .= " `token`,";
            $sql .= " `status`,";
            $sql .= " `create_date`";
            $sql .= " ) VALUES (";
            $sql .= " '".$_username."',";
            $sql .= " '".$_shopcode."',";
            $sql .= " '".$_token."', ";
            $sql .= " 'IN',";
            $sql .= " '".$_now."'";
            $sql .= " );";
            $this->logger->addInfo($sql);
            
            $q = $db->prepare($sql);
            $q->execute();
            $_err['sql'][] = $q->errorinfo();

            $sql = "UPDATE `t_employee` SET `last_login` = '".$_now."' , `last_token` = '".$_token."' WHERE `employee_code` = '".$employee_code."';";
            $this->logger->addInfo($sql);

            $q = $db->prepare($sql);
            $q->execute();
            $db->commit();
            $_err['sql'][] = $q->errorinfo();
            $_err['api']['code'] = "00001";
            $_err['api']['msg'] = "Login Successful";
            $_data = $_token;

        } //end time expire checking
    
 
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");

        foreach($_err['sql'] as $k => $v)
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
            // return API
            $_callback["query"] = $_data;
            $_callback["error"]["code"] = $_err['api']['code'];
            $_callback["error"]["message"] = $_err['api']['msg'];
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback["query"] = "";
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Data fetch Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    }); // end POST

    /**
     * login GET Request
     * login validation
     * 
     * To check token is valid or not
     * default set token expire as 1 day
     * 
     * Return login token
     */
    $this->get('/[{token}]', function (Request $request, Response $response, array $args) use($_expire){
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        // current time
        $_now = date('Y-m-d H:i:s');
        $_this_token = $args['token'];
        if(!empty($args))
        {
            $this->logger->addInfo("Entry: GET: Login");
            $pdo = new Database();
            $db = $pdo->connect_db();
            $this->logger->addInfo("Msg: DB connected");

            // SQL statement here
            $sql = "SELECT `username`, `status`, `create_date` FROM `t_login` WHERE `token` =  '".$_this_token."'; ";
            // $this->logger->addInfo("SQL: ".$sql);
            $q = $db->prepare($sql);
            $q->execute();
            $_err['sql'][] = $q->errorinfo();
            $_res = $q->fetch();
            if(!empty($_res))
            {
                // extract data
                extract($_res);
                if($status === "in")
                {
                    // Time Expire Checking
                    $_lasttime = strtotime( $create_date );
                    $_curtime = strtotime( $_now );
                    // time difference
                    $_diff = $_curtime - $_lasttime;
                    // within one day, then use old token 
                    if($_diff <= $_expire)
                    {
						$db->beginTransaction();
						$sql = "UPDATE"; 
						$sql .= " `t_login`"; 
						$sql .= " SET ";
						$sql .= " `status` = 'in',"; 
						$sql .= " `modify_date` = '".$_now."'";  
						$sql .= " WHERE `username` = '".$username."' AND `token` = '".$_this_token."';";
                        $q = $db->prepare($sql);
						$q->execute();
						$_err['sql'][] = $q->errorinfo();
						$db->commit();
                        $_dbData = $_this_token;
                        $_err['api']['code'] = "00001";
                        $_err['api']['msg'] = "Use same token";
                    }
                    // last token not exist then renew token
                    
                    else
                    {
                        // create new login token
                        $db->beginTransaction();
                        //$_token = md5($_body['username'].$_body['password'].date("Y-m-d H:i:s").$_body['default_shopcode']);
                        // logout last token
                        $sql = "UPDATE `t_login` SET `status` = 'OUT', `modify_date` = '".$_now."' WHERE `token` = '".$_this_token."'; ";
                        $sql .="UPDATE `t_employee` SET `last_token` = '', `last_login` = '".$_now."' WHERE `username` = '".$username."';";
                        $q = $db->prepare($sql);
                        $q->execute();
                        $_err['sql'][] = $q->errorinfo();
                        
                        $db->commit();
                        $_dbData = "";
                        $_err['api']['code'] = "00001";
                        $_err['api']['msg'] = "Need re-Login";
                    }   
                }
                else
                {
                    $_dbData = "";
                    $_err['api']['code'] = "10001";
                    $_err['api']['msg'] = "Never Login";
                }
            }
            // username and password DB no record
            else
            {
                $_dbData = "";
                $_err['api']['code'] = "10002";
                $_err['api']['msg'] = "Username or Password Incorrect";
            }
        }
        else
        {
            $_dbData = "";
            $_err['api']['code'] = "10003";
            $_err['api']['msg'] = "No token or token not valid";
        }
        // disconnect DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");
        
        foreach($_err['sql'] as $k => $v)
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
            // return API
            $_callback["query"] = $_dbData;
            $_callback["error"]["code"] = $_err['api']['code'];
            $_callback["error"]["message"] = $_err['api']['msg'];
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback["query"] = "";
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Data fetch Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    }); // end 
});