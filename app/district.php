<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->group('/api/v1/systems/district', function () {
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
            "SELECT * FROM `t_district`;");
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
});