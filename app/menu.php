<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/systems/menu', function () {
    /**
     * Menu get request
     * menu-get-sidebar
     * 
     * To get sidebar menu
     */
    $this->get('/side', function (Request $request, Response $response, array $args) {
        $data = [
            ["order" => 0, "id" => 2, "parent_id" => "", "name" => "Dushboard", "isParent" => "", "slug"=>"dushboard", "param" => "dushboard/index"],
            ["order" => 0, "id" => 5, "parent_id" => "", "name" => "Customers", "isParent" => "", "slug"=>"customers", "param" => "customers/index"],
            ["order" => 0, "id" => 3, "parent_id" => "", "name" => "Products", "isParent" => "", "slug"=>"", "param" => "products/index"],
            ["order" => 0, "id" => 4, "parent_id" => "", "name" => "Inventories", "isParent" => "", "slug"=>"", "param" => "inventories/index"],
            ["order" => 0, "id" => 23, "parent_id" => 3, "name" => "Items", "isParent" => "", "slug"=>"products/items", "param" => "items/index"],		
            ["order" => 0, "id" => 54, "parent_id" => 3, "name" => "Categories", "isParent" => "", "slug"=>"products/categories", "param" => "categories/index"],
            ["order" => 0, "id" => 65, "parent_id" => 4, "name" => "Invoices", "isParent" => "", "slug"=>"invoices", "param" => "invoices"],
            ["order" => 0, "id" => 32, "parent_id" => 65, "name" => "Create", "isParent" => "", "slug"=>"invoices/donew", "param" => "invoices/create"],
            ["order" => 0, "id" => 22, "parent_id" => "", "name" => "Administration", "isParent" => "", "slug"=>"", "param" => "administration"],
            ["order" => 0, "id" => 71, "parent_id" => 22, "name" => "Settings", "isParent" => "", "slug"=>"administration/settings", "param" => "administration/settings"],
            ["order" => 0, "id" => 35, "parent_id" => 71, "name" => "Shop", "isParent" => "", "slug"=>"administration/shops", "param" => "shops/index"],
            ["order" => 0, "id" => 6, "parent_id" => 71, "name" => "Employees", "isParent" => "", "slug"=>"administration/employees", "param" => "employees/index"],
            ["order" => 0, "id" => 33, "parent_id" => 4, "name" => "Quotations", "isParent" => "", "slug"=>"quotations", "param" => "quotations"],
            ["order" => 0, "id" => 26, "parent_id" => 65, "name" => "List", "isParent" => "", "slug"=>"invoices/list", "param" => "invoices/invlist"],
            ["order" => 0, "id" => 333, "parent_id" => 33, "name" => "Create", "isParent" => "", "slug"=>"quotations/donew", "param" => "quotations/create"],
            ["order" => 0, "id" => 332, "parent_id" => 33, "name" => "List", "isParent" => "", "slug"=>"quotations/list", "param" => "quotations/qualist"],
            ["order" => 0, "id" => 43, "parent_id" => 71, "name" => "Payment Method", "isParent" => "", "slug"=>"administration/payments", "param" => "payments/index"],
        ];
        return $response->withJson($data, 200);
    });
    $this->get('/top', function (Request $request, Response $response, array $args) {
        $data = [
            "message" => "this is menu top API"
        ];
        return $response->withJson($data, 200);
    });
});