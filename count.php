<?php

require_once 'util/checkParameter.php';

$count = $trac->getOverallCount($website);
echo $count;
exit;

?>