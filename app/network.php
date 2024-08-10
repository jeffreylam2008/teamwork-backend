<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app->group('/api/v1/network/', function (){
    $this->get('status/', function (Request $request, Response $response, array $args){
        $this->logger->addInfo("Msg: Network Status Test Result");
        $_callback = ["Msg" => "network connected!","Code"=> 1 ];
        return $response->withJson( $_callback , 200);
    });
});
