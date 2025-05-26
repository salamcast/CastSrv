<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'REST.class.php';

$rest = new RESTphulSrv();

echo '<pre>' . print_r($rest, true) . '</pre>';