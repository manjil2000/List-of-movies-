<?php require __DIR__.'/includes/bootstrap.php';go(empty($_SESSION['uid'])?'login.php':'list-movies.php');
