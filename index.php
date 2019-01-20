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
            "message" => "param not define" 
        ];
        return $c['response']->withJson($data,404);
    };
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
 * Employee API
 */
require './app/login.php';

/** 
 * Menu 
 */
 require './app/menu.php';

/**
 * Shop API
 */
require './app/shops.php';

$app->run();
