<?php

$name = isset($otherPicture) ? $otherPicture : 'img/000000-0.png';
$fp = fopen($name, 'rb');
error_log($name);
// send the right headers
header("Content-Type: image/png");
header("Content-Length: " . filesize($name));

// dump the picture and stop the script
fpassthru($fp);

?>

