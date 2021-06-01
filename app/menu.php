<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/systems/menu', function () {
    $_callback = [];
    /**
     * Menu get request
     * menu-get-sidebar
     * 
     * To get sidebar menu
     */
    $this->get('/side', function (Request $request, Response $response, array $args) {
        $sql = "";
        $_param = $request->getQueryParams();
        if(empty($_param["l"])) $_param["l"] = "";
        switch($_param["l"])
        {
            case "en-us":
                $sql = "SELECT m_order as `order`, `id`, `parent_id`, lang2 as `name`, slug, `param` FROM `t_menu`;";
                break;
            case "zh-hk":
                $sql = "SELECT m_order as `order`, `id`, `parent_id`, lang1 as `name`, slug, `param` FROM `t_menu`;";
                break;
        }
        $_callback = [];
        $_err = [];
        $_query = [];
        $pdo = new Database();
        $db = $pdo->connect_db();

        $q = $db->prepare($sql);

        $q->execute();
        $_err = $q->errorinfo();
        $data = $q->fetchAll(PDO::FETCH_ASSOC);
        // $data = [
        //     ["order" => 0, "id" => 2, "parent_id" => "", "name" => "Dushboard", "slug"=>"dushboard", "param" => "dushboard/index"],
        //     ["order" => 0, "id" => 5, "parent_id" => "", "name" => "Customers", "slug"=>"customers", "param" => "customers/index"],
        //     ["order" => 0, "id" => 7, "parent_id" => "", "name" => "Suppliers", "slug"=>"suppliers", "param" => "suppliers/index"],
        //     ["order" => 0, "id" => 3, "parent_id" => "", "name" => "Products", "slug"=>"", "param" => "products/index"],
        //     ["order" => 0, "id" => 4, "parent_id" => "", "name" => "Inventories", "slug"=>"", "param" => "inventories/index"],
        //     ["order" => 0, "id" => 8, "parent_id" => "", "name" => "Purchases", "slug"=>"", "param" => "purchases/index"],
        //     ["order" => 0, "id" => 9, "parent_id" => "", "name" => "Warehouse", "slug"=>"", "param" => "stock/index"],
        //     ["order" => 0, "id" => 23, "parent_id" => 3, "name" => "Items", "slug"=>"products/items", "param" => "items/index"],		
        //     ["order" => 0, "id" => 54, "parent_id" => 3, "name" => "Categories", "slug"=>"products/categories", "param" => "categories/index"],
        //     ["order" => 0, "id" => 65, "parent_id" => 4, "name" => "Invoices", "slug"=>"invoices", "param" => "invoices"],
        //     ["order" => 0, "id" => 32, "parent_id" => 65, "name" => "Create", "slug"=>"invoices/donew", "param" => "invoices/create"],
        //     ["order" => 0, "id" => 22, "parent_id" => "", "name" => "Administration", "slug"=>"", "param" => "administration"],
        //     ["order" => 0, "id" => 71, "parent_id" => 22, "name" => "Settings", "slug"=>"administration/settings", "param" => "administration/settings"],
        //     ["order" => 0, "id" => 35, "parent_id" => 71, "name" => "Shop", "slug"=>"administration/shops", "param" => "shops/index"],
        //     ["order" => 0, "id" => 6, "parent_id" => 71, "name" => "Employees", "slug"=>"administration/employees", "param" => "employees/index"],
        //     ["order" => 0, "id" => 33, "parent_id" => 4, "name" => "Quotations", "slug"=>"quotations", "param" => "quotations"],
        //     ["order" => 0, "id" => 26, "parent_id" => 65, "name" => "List", "slug"=>"invoices/list", "param" => "invoices/invlist"],
        //     ["order" => 0, "id" => 333, "parent_id" => 33, "name" => "Create", "slug"=>"quotations/donew", "param" => "quotations/create"],
        //     ["order" => 0, "id" => 332, "parent_id" => 33, "name" => "List", "slug"=>"quotations/list", "param" => "quotations/qualist"],
        //     ["order" => 0, "id" => 43, "parent_id" => 71, "name" => "Payment Method", "slug"=>"administration/payments/method", "param" => "payments/paymentmethod"],
        //     ["order" => 0, "id" => 45, "parent_id" => 71, "name" => "Payment Term", "slug"=>"administration/payments/term", "param" => "payments/paymentterm"],
        //     ["order" => 0, "id" => 57, "parent_id" => 8, "name" => "Purchases Order", "slug"=>"purchases/order", "param" => "purchases/index"],
        //     ["order" => 0, "id" => 58, "parent_id" => 57, "name" => "Create", "slug"=>"purchases/order/donew", "param" => "purchases/create"],
        //     ["order" => 0, "id" => 59, "parent_id" => 57, "name" => "List", "slug"=>"purchases/order", "param" => "purchases/index"],
        //     ["order" => 0, "id" => 68, "parent_id" => 9, "name" => "Stock", "slug"=>"stocks", "param" => "stocks/index"],
        //     ["order" => 0, "id" => 10, "parent_id" => "", "name" => "Report", "slug"=>"reports", "param" => "reports/index"],
        //     ["order" => 0, "id" => 222, "parent_id" =>22 , "name" => "System Backup", "slug"=>"sysbak", "param" => "sysbak/index"],
        //     ["order" => 0, "id" => 44, "parent_id" =>222 , "name" => "Import/Export", "slug"=>"systems/backup", "param" => "systems/index"]
        // ];
        $_callback = [
            "query" => $data,
            "error" => ["code" => $_err[0], "message" => $_err[1]." ".$_err[2]]
        ];
        // $_callback["query"] = $data;
        // $_callback["error"] = ["code" => "00000", "message" => "Menu Loaded"];

        return $response->withJson($_callback, 200);
    });

});