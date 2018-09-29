<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once './../../../vendor/autoload.php';
require_once './../../../lib/db.php';
$c = new \Slim\Container(); //Create Your container

//Override the default Not Found Handler
$c['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $data = [
            "message" => "param not define"
        ];
        return $c['response']
            ->withJson($data,404);
    };
};

$app = new \Slim\App($c);
$app->get('/side', function (Request $request, Response $response, array $args) {
    $data = [
        ["order" => 2, "id" => 1, "parent_id" => "", "name" => "login", "isParent" => "", "slug"=>"login"],
        ["order" => 1, "id" => 2, "parent_id" => "", "name" => "Dushboard", "isParent" => "", "slug"=>"dushboard"],
        ["order" => 3, "id" => 3, "parent_id" => "", "name" => "Product", "isParent" => "", "slug"=>""],
        ["order" => 3, "id" => 4, "parent_id" => "", "name" => "Inventory", "isParent" => "", "slug"=>""],
        ["order" => 3, "id" => 23, "parent_id" => 3, "name" => "Items", "isParent" => "", "slug"=>"products/items"],		
        ["order" => 1, "id" => 54, "parent_id" => 3, "name" => "Categories", "isParent" => "", "slug"=>"products/categories"],
        ["order" => 1, "id" => 65, "parent_id" => 4, "name" => "Invoices", "isParent" => "", "slug"=>"invoices"],
        ["order" => 1, "id" => 32, "parent_id" => 65, "name" => "Create", "isParent" => "", "slug"=>"invoices/donew"],
        ["order" => 2, "id" => 22, "parent_id" => "", "name" => "Administration", "isParent" => "", "slug"=>""],
        ["order" => 1, "id" => 71, "parent_id" => 22, "name" => "Settings", "isParent" => "", "slug"=>"administration/settings"],
        ["order" => 1, "id" => 555, "parent_id" => 44, "name" => "test item 2", "isParent" => "", "slug"=>"test/test/test_item2"],
        ["order" => 1, "id" => 44, "parent_id" => 71, "name" => "test item1", "isParent" => "", "slug"=>"test/test_item1"],
        ["order" => 1, "id" => 26, "parent_id" => 65, "name" => "List", "isParent" => "", "slug"=>"invoices/list"]
    ];
    return $response->withJson($data, 200);
});
$app->get('/top', function (Request $request, Response $response, array $args) {
    $data = [
        "meessage" => "this is menu top API"
    ];
    return $response->withJson($data, 200);
});
$app->run();
