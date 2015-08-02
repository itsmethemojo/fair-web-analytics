<?php


error_reporting(E_ALL);

if (file_exists('vendor/loginService.php')) {
    include 'vendor/loginService.php';
}

include 'vendor/mvc-core/autoloader.php';
include 'autoloader.php';

$analytics = $controller;


if(isset($_GET["action"])){
    switch ($_GET["action"]){
        case "getPageClicks":
            $analytics->actionGetPageClicks();
            break;
        case "displayPageClicks":
            $analytics->actionDisplayPageClicks();
            break;
    }
}



?>

action not implemented