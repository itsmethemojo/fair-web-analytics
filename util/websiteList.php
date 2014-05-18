<?php

$website = $_GET['website'];
$url = parse_url($website);

if(!isset($url['host'])){
    echo 'no valid Website';
    exit;
}

require_once ROOTPATH.'classes/SimpleTrac.php';

$trac = new SimpleTrac();
$domains = $trac->getAllowedDomains();


if(!in_array($url['host'], $domains)){
    echo 'Website not monitored';
    exit;
}


$dates = $trac->getWebsiteClicks($website);
?>
<table>
<?php
    
foreach ($dates as $date){
?>
    <tr>
        <td class="count"><?php echo $date['count'] ?></td>
        <td class="date"><?php echo $date['date'] ?></td>
    </tr>
<?php
}
?>

</table>