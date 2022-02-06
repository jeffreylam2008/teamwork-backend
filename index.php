<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once './vendor/autoload.php';
require_once './lib/db.php';

$c = new \Slim\Container(); //Create Your container

//Override the default Not Found Handler
$c['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $data = [
            "error" => [
                "code" => "44444",
                "message" => "param not define" 
            ]
        ];
        return $c['response']->withJson($data,404);
    };
};
$c = [
    'settings' => [
        'displayErrorDetails' => true
    ],
];
$c['logger'] = function($c) {
    $logger = new \Monolog\Logger('log');
    $file_handler = new \Monolog\Handler\StreamHandler('logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

$app = new \Slim\App($c);

/**
 * Categories API
 */
require './app/categories.php';

/**
 * Items API
 */
require './app/items.php';

/**
 * Customer API
 */
require './app/customers.php';

/**
 * Suppliers API
 */
require './app/suppliers.php';

/**
 * Quotation API
 */
require './app/quotations.php';
    
/**
 * Invoices API
 */
require './app/invoices.php';

/**
 * Payment API
 */
 require './app/payments.php';

/**
 * Login API
 */
require './app/login.php';

/**
 * Logout API
 */
require './app/logout.php';

/** 
 * Menu 
 */
require './app/menu.php';

/**
 * Shop API
 */
require './app/shops.php';

/**
 * employee API
 */
require './app/employee.php';

/**
 * district API
 */
require './app/district.php';

/**
 * Stocks
 */
 require './app/stocks.php';
 
/**
 * Stocks
 */
 require './app/transactions.php';
 
/**
 * purchases
 */
require './app/purchases.php';

/**
 * test
 */
require './app/systems.php';

/**
 * test
 */
require './app/test.php';



$app->run();
