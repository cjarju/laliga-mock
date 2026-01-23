<?php
require_once __DIR__ . '/../config.php'; 
require_once BASE_PATH . '/phps/php_functions.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    $_SESSION['request_url'] = curPageURL();
    redirect ('/signin.php');

}
?>