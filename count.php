<?php
require_once 'config/define.php';

require_once 'util/checkParameter.php';
echo '<html><head></head><body style="margin:0;">';
$count = $trac->getOverallCount($website);
$style= 'width:200px;text-align:right;color:#9F9F9F;font-family:Arial, Helvetica, sans-serif;font-weight:bold;font-size:60px;';
echo '<div style="'.$style.'">'.$count.'</div>';
echo '</body></html>';
exit;

?>