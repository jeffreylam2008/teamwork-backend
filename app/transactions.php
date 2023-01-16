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

    /**
     * Transaction discard
     * Transaction-discard
     * 
     * To remove session id from session generator
     */
    $this->delete('/discard/{session_id}', function(Request $request, Response $response, array $args){
        $_session_id = $args['session_id'];
        $_err = [];
        $_callback = ['query' => "" , 'error' => ["code" => "", "message" => ""]];
        $_result = true;
        $_msg = "";
        
        $this->logger->addInfo("Entry: Delete: Transaction Discard Function, session_id: ".$_session_id);
        $pdo = new Database();
        $db = $pdo->connect_db();
        $this->logger->addInfo("Msg: DB connected");

        // sql statement
        $sql = "DELETE FROM `t_trans_num_generator` WHERE `session_id` = '".$_session_id."';";
        // $this->logger->addInfo("SQL: ".$sql);
        // prepare sql statement
        $q = $db->prepare($sql);
        // execute statement
        $q->execute();
        $_err[] = $q->errorinfo();
        
        $this->logger->addInfo("Msg: DB commit");
        //disconnection DB
        $pdo->disconnect_db();
        $this->logger->addInfo("Msg: DB connection closed");

        foreach($_err as $k => $v)
        {
            if($v[0] != "00000")
            {
                $_result = false;
                $_msg .= $v[1]."-".$v[2]."|";
            }
            else
            {
                $_msg .= "SQL #".$k.": SQL execute OK! | ";
            }
        }
        if($_result)
        {
            $_callback['error']['code'] = "00000";
            $_callback['error']['message'] = "Transaction: - Deleted!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 200);
        }
        else
        {  
            $_callback['error']['code'] = "99999";
            $_callback['error']['message'] = "Delete Fail - Please try again!";
            $this->logger->addInfo("SQL execute ".$_msg);
            return $response->withHeader('Connection', 'close')->withJson($_callback, 404);
        }
    });
    
});