<?php

require_once 'config/define.php';

if(!isset($_GET['website'])){
    header("Expires: Sat, 26 Jul 2080 05:00:00 GMT");
    $otherPicture = 'MissingWebsiteParameter.png';
    require_once 'util/printPicture.php';
    exit;
}

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

$website = $_GET['website'];


require_once 'classes/SimpleTrac.php';

$url = parse_url($website);

if(!isset($url['host'])){
    header("Expires: Sat, 26 Jul 2080 05:00:00 GMT");
    if(DEBUG){
        $otherPicture = 'img/MissingWebsiteParameter.png';
    }
    require_once 'util/printPicture.php';
    exit;
}

$trac = new SimpleTrac();
$domains = $trac->getAllowedDomains();


if(!in_array($url['host'], $domains)){
    header("Expires: Sat, 26 Jul 2080 05:00:00 GMT");
    if(DEBUG){
        $otherPicture = 'img/WebsiteNotAllowed.png';
    }
    require_once 'util/printPicture.php';
    exit;
}

?>
