<?php
require_once 'config/define.php';

require_once 'util/checkParameter.php';

$count = $trac->getOverallCountForWebsite($website);
$overallCount = $trac->getOverallCountForDomain($website);
?>
<html>
    <head></head>
    <body style="margin:0;">
        <div style="width:200px;text-align:right;color:#9F9F9F;font-family:Arial, Helvetica, sans-serif;font-weight:bold;">
            <span style="font-size:40px;"><?php echo $count; ?>/</span>
            <span style="font-size:60px;"><?php echo $overallCount; ?></span>
        </div>
    </body>
</html>
<?php