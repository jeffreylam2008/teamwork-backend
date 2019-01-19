<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/systems/login', function () {
    /**
     * login GET Request
     * login-post
     * 
     * To post employee record and get token
     * 
     * Return login token
     */
    $this->post('/', function (Request $request, Response $response, array $args) {
        // POST Data here
        // $body = json_decode($request->getBody(), true);
        
        $_body = json_decode($request->getBody(), true);

        // var_dump( $body);
        if(!empty($_body["username"]) && !empty($_body['password']))
        {
            $_err = "";
            $_salt = "password";
            $_token = "";
            $_callback = [];
            $db = connect_db();
            // SQL statement here
            $q = $db->prepare("select * from `t_employee` where `username` =  '".$_body['username']."'; ");
            $q->execute();
            $_err = $q->errorinfo();
            $_res = $q->fetch();
            $_dbData = "";
            // 
            if(!empty($_res))
            {
                // extract data
                extract($_res);
                // current time
                $_now = date('Y-m-d H:i:s');
                // verify password
                if(hash_equals($password, crypt($_body['password'], $_salt)))
		        {
                    // check expire
                    $lasttime = strtotime( $last_login );
                    $curtime = strtotime( $_now );
                    // time difference
                    $diff = $curtime - $lasttime;
                    // within one day, return token 
                    if($diff <= 86400)
                    {
                        // get back last token
                        if(!empty($last_login))
                        {
                            $_dbData = $last_token;
                            $_err['api']['code'] = "00001";
                            $_err['api']['msg'] = "Login Successful";
                        }
                        // last token not exist then renew token
                        else
                        {
                             // create new login token
                            $db->beginTransaction();
                            $_token = md5($_body['username'].$_body['password'].date("Y-m-d H:i:s"));
                            
                            $q = $db->prepare(
                                "update `t_employee` set `last_login` = '".$_now."' ,`last_token` = '".$_token."' WHERE `employee_code` = '".$employee_code."';"
                            );
                            $q->execute();
                            $_err['sql'][] = $q->errorinfo();

                            $q = $db->prepare(
                                "insert into `t_login` (`uid`,`username`,`shop_code`,`token`,`status`,`create_date`) 
                                values ('', '".$_body['username']."', '".$_body['default_shopcode']."', '".$_token."', 'IN' ,'".$_now."');"
                            );
                            $q->execute();
                            $_err['sql'][] = $q->errorinfo();
                            
                            $db->commit();

                            $_dbData = $_token;
                            $_err['api']['code'] = "00001";
                            $_err['api']['msg'] = "Login Successful";
                        }

                    }
                    // token expire and renew
                    else
                    {
                        // create new login token
                        $db->beginTransaction();
                        $_token = md5($_body['username'].$_body['password'].date("Y-m-d H:i:s"));
                        // logout last token
                        $q = $db->prepare(
                            "update `t_login` set `status` = 'OUT', `modify_date` = '".$_now."' WHERE `token` = '".$last_token."';"
                        );
                        $q->execute();
                        $_err['sql'][] = $q->errorinfo();
                        // update new token
                        $q = $db->prepare(
                            "update `t_employee` set `last_login` = '".$_now."' ,`last_token` = '".$_token."' WHERE `employee_code` = '".$employee_code."'; "
                        );
                        $q->execute();
                        $_err['sql'][] = $q->errorinfo();
                        // login new token
                        $q = $db->prepare(
                            "insert into `t_login` (`uid`,`username`,`shop_code`,`token`,`status`,`create_date`) 
                            values ('', '".$_body['username']."', '".$_body['default_shopcode']."', '".$_token."', 'IN' ,'".$_now."');"
                        );
                        $q->execute();
                        $_err['sql'][] = $q->errorinfo();
                        
                        $db->commit();

                        $_dbData = $_token;
                        $_err['api']['code'] = "00001";
                        $_err['api']['msg'] = "Login Successful";
                    }
                }
                // password not match
                else
                {
                    // if not match
                    $_dbData = "";
                    $_err['api']['code'] = "10002";
                    $_err['api']['msg'] = "Password Incorrect";
                }   
            }
            // username and password empty
            else
            {
                $_dbData = "";
                $_err['api']['code'] = "10001";
                $_err['api']['msg'] = "Username or Password Incorrect";
            }
            // return API
            $_callback = [
                "query" => $_dbData,
                "error" => ["code" => $_err['api']['code'], "message" => $_err['api']['msg']]
            ];
            return $response->withJson($_callback, 200);
        }
    });
});