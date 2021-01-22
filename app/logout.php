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
           
        }
         // end Patch
    });
});