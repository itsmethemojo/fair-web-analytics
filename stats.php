<?php

require_once 'config/define.php';

if(isset($_GET['website'])){
    require_once ROOTPATH.'util/websiteList.php';
    exit;
}


require_once ROOTPATH.'util/completeList.php';

?>


