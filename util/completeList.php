<?php

require_once ROOTPATH.'classes/SimpleTrac.php';
$trac = new SimpleTrac();
$websites = $trac->getCountList();
?>
<table>
<?php
    
foreach ($websites as $website){
?>
    <tr>
        <td class="count"><?php echo $website['count'] ?></td>
        <td class="url"><a href="stats.php?website=<?php echo $website['url'] ?>"><?php echo $website['url'] ?></a></td>
    </tr>
<?php
}
?>

</table>
