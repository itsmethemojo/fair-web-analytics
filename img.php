<?php
require_once 'config/define.php';

require_once ROOTPATH.'util/checkParameter.php';

$trac->saveCall($website, $ip);
require_once ROOTPATH.'util/printPicture.php';
exit;

?>