<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->group('/api/v1/systems/logout', function () {
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
    $this->patch('/[{token}]', function (Request $request, Response $response, array $args) use($_expire) {
        if(!empty($args))
        {
            $_this_token = $args['token'];
            // POST Data here
            // $body = json_decode($request->getBody(), true);

            $pdo = new Database();
            $db = $pdo->connect_db();
            $_now = date('Y-m-d H:i:s');
            // // POST Data here
            $body = json_decode($request->getBody(), true);
            $db->beginTransaction();
            $q = $db->prepare("UPDATE `t_login` SET `status` = 'out', `modify_date` = '".$_now."' WHERE `token` = '".$_this_token."';");
            $q->execute();
            // $_err['sql'][] = $q->errorinfo();
            // $q = $db->prepare("UPDATE `t_employee` SET `last_login` = '0000-00-00 00:00:00', `last_token` = '', `modify_date` = '".$_now."' WHERE `token` = '".$_this_token."';");
            // $q->execute();
            $_err['sql'][] = $q->errorinfo();

            $db->commit();
            //disconnection DB
            $pdo->disconnect_db();
            
            $_err['api']['code'] = "00000";
            $_err['api']['msg'] = "Logout Completed";
            $_callback = [
                "query" => "",
                "error" => ["code" => $_err['api']['code'], "message" => $_err['api']['msg']]
            ];
            return $response->withJson($_callback, 200);

            // var_dump( $body);
            // if(!empty($_body["username"]) && !empty($_body['password']) && !empty($_body['shopcode']))
            // {
            //     $_err = [];
            //     $_salt = "password";
            //     $_token = "";
            //     $_callback = [];
            //     $pdo = new Database();
            //     $db = $pdo->connect_db();
            //     // SQL statement here
            //     $q = $db->prepare("select * from `t_employee` where `username` =  '".$_body['username']."' AND `default_shopcode` = '".$_body['shopcode']."'; ");
            //     $q->execute();
            //     $_res = $q->fetch();
            //     $_err = $q->errorinfo();
            //     $_dbData = "";

                
            //     if(!empty($_res))
            //     {
            //         // extract data
            //         extract($_res);
            //         // current time
            //         $_now = date('Y-m-d H:i:s');
            //         // verify password
            //         if(hash_equals($password, crypt($_body['password'], $_salt)))
            //         {
            //             // check user status valid
            //             if($status == 1)
            //             {
            //                 // Time Expire Checking
            //                 $lasttime = strtotime( $last_login );
            //                 $curtime = strtotime( $_now );
            //                 // time difference
            //                 $diff = $curtime - $lasttime;
            //                 // within one day (86400 = one day) , then use old token 
            //                 if($diff <= $_expire && !empty($last_token))
            //                 {
            //                     // last token is exist, return last token
            //                     // get back last token
            //                     $db->beginTransaction();
            //                     $q = $db->prepare(
            //                         "update `t_login` set `status` = 'in', `modify_date` = '".$_now."' WHERE `username` = '".$_body['username']."' AND `shop_code` = '".$_body['shopcode']."' AND `token` = '".$last_token."';"
            //                     );
            //                     $q->execute();
            //                     $_err['sql'][] = $q->errorinfo();
            //                     $db->commit();
            //                     $_dbData = $last_token;
            //                     $_err['api']['code'] = "00001";
            //                     $_err['api']['msg'] = "Login Successful";
            //                 }
            //                 // token expire and use new token
            //                 else
            //                 {
            //                     // create new login token
            //                     $db->beginTransaction();
            //                     $_token = md5($_body['username'].$_body['password'].date("Y-m-d H:i:s").$_body['shopcode']);
            //                     // logout last token
            //                     $q = $db->prepare(
            //                         "update `t_login` set `status` = 'OUT', `modify_date` = '".$_now."' WHERE `username` = '".$_body['username']."' AND `shop_code` = '".$_body['shopcode']."' AND `status` = 'in';"
            //                     );
            //                     $q->execute();
            //                     $_err['sql'][] = $q->errorinfo();
            //                     // update new token
            //                     $q = $db->prepare(
            //                         "update `t_employee` set `last_login` = '".$_now."' ,`last_token` = '".$_token."' WHERE `employee_code` = '".$employee_code."'; "
            //                     );
            //                     $q->execute();
            //                     $_err['sql'][] = $q->errorinfo();

            //                     // login new token
            //                     $q = $db->prepare(
            //                         "insert into `t_login` (`username`,`shop_code`,`token`,`status`,`create_date`,`modify_date`) values ('".$_body['username']."', '".$_body['shopcode']."', '".$_token."', 'IN' ,'".$_now."','0000-00-00 00:00:00');"
            //                     );
                                
            //                     $q->execute();
            //                     $_err['sql'][] = $q->errorinfo();
                                
            //                     $db->commit();

            //                     $_dbData = $_token;
            //                     $_err['api']['code'] = "00001";
            //                     $_err['api']['msg'] = "Login Successful";
                                
            //                 } //end time expire checking   
            //             } //end check status
            //             else
            //             {
            //                 $_dbData = "";
            //                 $_err['api']['code'] = "10002";
            //                 $_err['api']['msg'] = "User Account Disabled";
            //             }
            //         } // end check password 
            //         // password not match
            //         else
            //         {
            //             // if not match
            //             $_dbData = "";
            //             $_err['api']['code'] = "10002";
            //             $_err['api']['msg'] = "Password Incorrect";
            //         } // end password not matach
            //     }
            //     // username and password, DB no record
            //     else
            //     {
            //         $_dbData = "";
            //         $_err['api']['code'] = "100011 ";
            //         $_err['api']['msg'] = "Username or Password Incorrect";
            //     }
            // }
            // // username or password empty
            // else
            // {
            //     $_dbData = "";
            //     $_err['api']['code'] = "100012";
            //     $_err['api']['msg'] = "Username and Password Cannot Be Empty";
            // }
            // // return API
            // $_callback = [
            //     "query" => $_dbData,
            //     "error" => ["code" => $_err['api']['code'], "message" => $_err['api']['msg']]
            // ];
            
           
        }
         // end Patch
    });
});