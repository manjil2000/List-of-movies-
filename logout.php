<?php require __DIR__.'/includes/bootstrap.php';if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit;}csrf();$_SESSION=[];session_destroy();go('login.php');
