<?php

class AnalyticsController extends BaseController{    
    
    private static $PARAM_DOMAIN = "domain";
    private static $PARAM_YEAR = "year";
    private static $PARAM_MONTH = "month";
    private static $PARAM_DAY = "day";
    private static $PARAM_USER = "user";


    public function initialize() {
        /*
        $this->javascript = array();
        $this->javascript[] = "script/remark.js";
        $this->javascript[] = "script/jquery-2.1.3.min.js";
        $this->javascript[] = "script/jquery.query-object.js";
        
        $this->css = array();
        $this->css[] = "style/remark.css";
        
        $this->favicon = "img/favicon.ico";
        */
    }
    
    private function getDomain(){
        return $this->readParameter(self::$PARAM_DOMAIN);
    }
    
    private function queryStatistics(){
        
        $domain = $this->getDomain();
        $year = $this->readParameter(self::$PARAM_YEAR);
        if(!$year){
            $year = date("Y");
        }
        $month = $this->readParameter(self::$PARAM_MONTH);
        $day = $this->readParameter(self::$PARAM_DAY);
        $user = $this->readParameter(self::$PARAM_USER);
        $viewParameters = array();
        $viewParameters[self::$PARAM_DAY] = $day;
        $viewParameters[self::$PARAM_MONTH] = $month;
        $viewParameters[self::$PARAM_YEAR] = $year;
        $viewParameters[self::$PARAM_DOMAIN] = $domain;
        $viewParameters[self::$PARAM_USER] = $user;
        
        if($user && $day && $month && $year){
            $viewParameters['statistic'] = "pageActionsOnDay";
            $viewParameters['jsonData'] =  $this->model->getUserForDay($domain,$user,$year,$month,$day);
            return $viewParameters;
        }
        
        if($user && $month && $year){
            $viewParameters['statistic'] = "pageActionsPerDay";
            $viewParameters['jsonData'] =  $this->model->getUserForMonth($domain,$user,$year,$month);
            return $viewParameters;
        }
        
        if($day && $month && $year){
            $viewParameters['statistic'] = "pageActionsOnDay";
            $viewParameters['jsonData'] =  $this->model->getVisitsForDay($domain,$year,$month,$day);
            return $viewParameters;
        }
        
        if($month && $year){
            $viewParameters['statistic'] = "pageActionsPerDay";
            $viewParameters['jsonData'] =  $this->model->getVisitsForMonth($domain,$year,$month);
            return $viewParameters;
        }
        
        if($year){
            $viewParameters['statistic'] = "pageActionsPerMonth";
            $viewParameters['jsonData'] = $this->model->getVisitsForYear($domain,$year);
            return $viewParameters;
        }
        
        return NULL;
    }
    
    public function actionGetPageClicks(){
        $viewParameters = $this->queryStatistics();
        $this->disableContainer();
        $this->view($viewParameters);
    }
    
    public function actionDisplayPageClicks(){
        $viewParameters = $this->queryStatistics();
        $viewParameters["widget"] = $this->readParameter("widget") !== NULL;
        //TODO if widget do something special
        $this->javascript = array();        
        $this->javascript[] = "script/jquery-2.1.3.min.js";
        $this->javascript[] = "vendor/Highcharts/js/highcharts.js";
        $this->javascript[] = "script/displayPageClicks.js";
        
        $this->css = array();        
        $this->css[] = "style/displayPageClicks.css";
        
        $this->view($viewParameters);
        
    }
}
?>
