<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->group('/api/v1/systems/login', function () {
    /**
     * global variable
     * login expire 
     */
    $_expire = 86400;
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

        // var_dump( $body);
        if(!empty($_body["username"]) && !empty($_body['password']) && !empty($_body['shopcode']))
        {
            $_err = [];
            $_salt = "password";
            $_token = "";
            $_callback = [];
            $pdo = new Database();
		    $db = $pdo->connect_db();
            // SQL statement here
            $q = $db->prepare("SELECT * FROM `t_employee` WHERE `username` = '".$_body['username']."' AND `default_shopcode` = '".$_body['shopcode']."'; ");
            $q->execute();
            $_res = $q->fetch();
            $_err['sql'][] = $q->errorinfo();
            $_dbData = "";

            
            if(!empty($_res))
            {
                
                // extract data
                extract($_res);
                // current time
                $_now = date('Y-m-d H:i:s');
                // verify password
                if(hash_equals($password, crypt($_body['password'], $_salt)))
		        {
                    // check user status valid
                    if($status == 1)
                    {
                        // Time Expire Checking
                        $lasttime = strtotime( $last_login );
                        $curtime = strtotime( $_now );
                        // time difference
                        $diff = $curtime - $lasttime;
                        
                        // within one day (86400 = one day) , then use old token 
                        if($diff <= $_expire && !empty($last_token))
                        {
                            $db->beginTransaction();
                            // last token is exist, return last token
                            // get back last token
                            $q = $db->prepare("
                                UPDATE 
									`t_login` 
								SET 
									`status` = 'in', 
									`modify_date` = '".$_now."' 
                                WHERE `username` = '".$_body['username']."' AND `shop_code` = '".$_body['shopcode']."' AND `token` = '".$last_token."';
                            ");
                            $q->execute();
                            $db->commit();
                            $_err['sql'][] = $q->errorinfo();
                            $_err['api']['code'] = "00001";
                            $_err['api']['msg'] = "Login Successful";
                            $_dbData = $last_token;
                        }
                        // token expire and use new token
                        else
                        {
                            // create new login token
                            $_token = md5($_body['username'].$_body['password'].date("Y-m-d H:i:s").$_body['shopcode']);
                            $db->beginTransaction();
                            // logout last token
                            $q = $db->prepare("
                                UPDATE 
                                    `t_login` 
                                SET 
                                    `status` = 'OUT', 
                                    `modify_date` = '".$_now."' 
                                WHERE `username` = '".$_body['username']."' AND `shop_code` = '".$_body['shopcode']."' AND `status` = 'in';
                            ");
                            $q->execute();

                            $_err['sql'][] = $q->errorinfo();

                            $q = $db->prepare("
                                INSERT INTO `t_login` (
									`username`,
									`shop_code`,
									`token`,
									`status`,
									`create_date`
								) VALUES (
									'".$_body['username']."', 
									'".$_body['shopcode']."', 
									'".$_token."', 
									'IN',
									'".$_now."'
                                );
                            ");
                            $q->execute();
                    
                            $_err['sql'][] = $q->errorinfo();

                            $q = $db->prepare("
                                UPDATE 
									`t_employee` 
								SET 
									`last_login` = '".$_now."' ,
									`last_token` = '".$_token."' 
								WHERE `employee_code` = '".$employee_code."';
                               
                            ");
                            $q->execute();
                            $db->commit();
                            $_err['sql'][] = $q->errorinfo();
                            $_err['api']['code'] = "00001";
                            $_err['api']['msg'] = "Login Successful";
                            $_dbData = $_token;

                        } //end time expire checking
                
                        
                    } //end check status
                    else
                    {
                        $_dbData = "";
                        $_err['api']['code'] = "10002";
                        $_err['api']['msg'] = "User Account Disabled";
                    }
                } // end check password 
                // password not match
                else
                {
                    // if not match
                    $_dbData = "";
                    $_err['api']['code'] = "10002";
                    $_err['api']['msg'] = "Password Incorrect";
                } // end password not matach
            }
            // username and password, DB no record
            else
            {
                $_dbData = "";
                $_err['api']['code'] = "100011 ";
                $_err['api']['msg'] = "Username or Password Incorrect";
            }
            $pdo->disconnect_db();
        }
        // username or password empty
        else
        {
            $_dbData = "";
            $_err['api']['code'] = "100012";
            $_err['api']['msg'] = "Username and Password Cannot Be Empty";
        }
        
        // return API
        $_callback = [
            "query" => $_dbData,
            "error" => ["code" => $_err['api']['code'], "message" => $_err['api']['msg']]
        ];
        
        return $response->withJson($_callback, 200);
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
        if(!empty($args))
        {
            $_this_token = $args['token'];
            $_err = [];
            $pdo = new Database();
		    $db = $pdo->connect_db();
            // SQL statement here
            $q = $db->prepare("SELECT `username`, `status`, `create_date` FROM `t_login` WHERE `token` =  '".$_this_token."'; ");
            $q->execute();
            $_err = $q->errorinfo();
            $_res = $q->fetch();
            if(!empty($_res))
            {
                // extract data
                extract($_res);
                // current time
                $_now = date('Y-m-d H:i:s');
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
						$q = $db->prepare(
							"UPDATE 
								`t_login` 
							SET 
								`status` = 'in',
								`modify_date` = '".$_now."' 
							WHERE `username` = '".$username."' AND `token` = '".$_this_token."';"
						);
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
                        $q = $db->prepare("
							UPDATE 
								`t_login` 
							SET 
								`status` = 'OUT', 
								`modify_date` = '".$_now."' 
							WHERE `token` = '".$_this_token."';
							UPDATE 
								`t_employee` 
							SET 
								`last_token` = '', 
								`last_login` = '".$_now."'
							WHERE `username` = '".$username."';
						");
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
        
        // return API
        $_callback = [
            "query" => $_dbData,
            "error" => ["code" => $_err['api']['code'], "message" => $_err['api']['msg']]
        ];
        return $response->withJson($_callback, 200);
    }); // end 
});