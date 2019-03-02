<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->group('/api/v1/systems/employee', function () {
    /**
     * employee GET request
     * employee-get
     * 
     * To get all employee information 
     */
    $this->get('/', function (Request $request, Response $response, array $args) {
        $_err = "";
        $db = connect_db();
        $q = $db->prepare("select `employee_code`, `username`, `default_shopcode`, `access_level`, `role`, `status` from `t_employee`;");
        $q->execute();
        $_err = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($res))
        {
            foreach ($res as $key => $val) {
                $dbData[] = $val;
            }
            $callback = [
                "query" => $dbData,
                "error" => ["code" => $_err[0], "message" => $_err[2]]
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
        $_err = "";
        $db = connect_db();
        $q = $db->prepare("select `employee_code`, `username`, `default_shopcode`, `access_level`, `role`, `status` from `t_employee` where `username` = '".$_username."';");
        $q->execute();
        $_err = $q->errorinfo();
        $res = $q->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($res))
        {
            foreach ($res as $key => $val) {
                $dbData[] = $val;
            }
            $callback = [
                "query" => $dbData,
                "error" => ["code" => $_err[0], "message" => $_err[2]]
            ];
            return $response->withJson($callback, 200);
        }
    });
});