<?php
header("Cache-Control: max-age=900");
if(isset($this->par["jsonData"])){
    header('Content-Type: application/json');
    echo json_encode($this->par["jsonData"]);
}


