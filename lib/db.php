<?php
function connect_db() 
{
	$dsn = 'mysql:dbname=teamwork;host=localhost;charset=utf8';
	$user = 'dbadmin';
	$pass = 'dbadmin';

	//mysql_set_charset('utf8',$connection);
	//$connection->set_charset("utf8");
	/* check connection */
	try {
		$connection = new PDO($dsn, $user, $pass);
		$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		//$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		echo 'Connection failed: ' . $e->getMessage();
	}
	
	return $connection;
}