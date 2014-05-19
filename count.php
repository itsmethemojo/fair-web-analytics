<?php
require_once 'config/define.php';

require_once 'util/checkParameter.php';

$count = $trac->getOverallCount($website);
$style= 'padding-right:8px;padding-left:8px;background-color:#9F9F9F;color:white;font-family:Arial, Helvetica, sans-serif;font-weight:bold;font-size:50px;';
echo '<span style="'.$style.'">'.$count.'</span>';
exit;

?>