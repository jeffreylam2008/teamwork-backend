<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->group('/api/v1/transactions', function () use($app) {
    /**
     * transactions GET Request
     * to verify transaction type
     * @param trans_code transaction number required
     */
    $app->get('/{trans_code}', function (Request $request, Response $response, array $args) {
        $_callback = [];
        $_err = [];
        $_res = [];
        $_trans_code = "";
        $trans_code = $args['trans_code'];
        $pdo = new Database();
        $db = $pdo->connect_db();
        
        $sql = "
        SELECT th.trans_code, th.prefix, th.refer_code
        FROM `t_transaction_h` as th
        WHERE th.trans_code = '".$trans_code."';
        ";

        $q = $db->prepare($sql);
        $q->execute();
        $_err = $q->errorinfo();
        if($q->rowCount() != 0)
        {
            $_res = $q->fetch(PDO::FETCH_ASSOC);
        }
        else
        {
            $_err[0] = '80000';
            $_err[2] = 'Transaction code not found!';
        }
        $pdo->disconnect_db();

        $_callback['query'] = $_res;
        $_callback["error"]["code"] = $_err[0];
        $_callback["error"]["message"] = $_err[2];

        //disconnection DB
        return $response->withJson($_callback, 200);
    });
});