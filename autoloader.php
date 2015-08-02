<?php

include_once __DIR__.DIRECTORY_SEPARATOR.'Configuration'.DIRECTORY_SEPARATOR.'settings.php';
include_once __DIR__.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'Controller'.DIRECTORY_SEPARATOR.'AnalyticsController.php';
$controller = new AnalyticsController($dbConfig,__DIR__.DIRECTORY_SEPARATOR);
