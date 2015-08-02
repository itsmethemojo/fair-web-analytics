
<?php


class AnalyticsModel extends BaseModel{
    
    private function initializeTables(){
        if(isset($this->visitsTable)){
            return;
        }
        $this->visitsTable = $this->tablePrefix."visits";
        $this->websitesTable = $this->tablePrefix."websites";
        $this->domainsTable = $this->tablePrefix."domains";
    }
    
    public function getVisitsForYear($domain,$year){
        $this->initializeTables();
        $query = <<<SQLQUERY
            SELECT t4.month AS month,
                   COUNT(*) AS visits
            FROM (SELECT t1.website_id,
                         t1.visitor,
                         SUBSTRING(t1.date,1,4) AS YEAR,
                         SUBSTRING(t1.date,6,2) AS MONTH
                  FROM $this->visitsTable t1
                  WHERE t1.website_id IN (SELECT t2.id
                                          FROM $this->websitesTable t2
                                          WHERE t2.domain_id = (SELECT t3.id
                                                                FROM $this->domainsTable t3
                                                                WHERE t3.domain = ? LIMIT 1))) t4
            WHERE t4.year = ?
            GROUP BY t4.month;
SQLQUERY;
        $yearData = array();
        for($i=1;$i<13;$i++){
            $yearData[$i] = 0;
        }
        $queriedYearData = $this->queryDatabase($query, array($domain,$year));
        foreach($queriedYearData as $entry){
            $yearData[intval($entry['month'])] = $entry['visits'];
        }
        return $yearData;
    }
    
    public function getVisitsForMonth($domain,$year,$month){
        $this->initializeTables();
        $query = <<<SQLQUERY
            SELECT COUNT(*) AS visits,
                   t4.day AS day
            FROM (SELECT ABS(SUBSTRING(t1.date,9,2)) AS DAY
                  FROM $this->visitsTable t1
                  WHERE SUBSTRING(t1.date,1,7) = ?
                  AND   t1.website_id IN (SELECT t2.id
                                          FROM $this->websitesTable t2
                                          WHERE t2.domain_id = (SELECT t3.id
                                                                FROM $this->domainsTable t3
                                                                WHERE t3.domain = ? LIMIT 1))) t4
            GROUP BY t4.day;
SQLQUERY;
        $monthDayCount =  cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $monthData = array();
        for($i=1;$i<=$monthDayCount;$i++){
            $monthData[$i] = 0;
        }
        //TODO add missing day entries
        $queriedMonthData = $this->queryDatabase($query, array($year."-".sprintf("%02d", $month),$domain));
        foreach($queriedMonthData as $entry){
            $monthData[intval($entry['day'])] = intval($entry['visits']);
        }
        return $monthData;
        
        
    }
    
    public function getVisitsForDay($domain,$year,$month,$day){
        $this->initializeTables();
        $query = <<<SQLQUERY
            SELECT count(*) as clicks, w.url as url
            FROM $this->visitsTable v
              JOIN $this->websitesTable w ON v.website_id = w.id
            WHERE v.date = ?
            AND   w.domain_id = (SELECT d.id
                                 FROM $this->domainsTable d
                                 WHERE d.domain = ? LIMIT 1) group by v.website_id;
SQLQUERY;
        return $this->queryDatabase($query, array($year."-".sprintf("%02d", $month)."-".sprintf("%02d", $day),$domain));
        
    }
    
    public function getUserForMonth($domain,$user,$year,$month){
        $this->initializeTables();
        $query = <<<SQLQUERY
            SELECT t5.day AS day,
                   COUNT(*) AS visitors
            FROM (SELECT t4.day AS DAY,
                         t4.visitor AS visitor
                  FROM (SELECT ABS(SUBSTRING(t1.date,9,2)) AS DAY,
                               t1.visitor AS visitor,
                               t1.website_id AS website_id
                        FROM $this->visitsTable t1
                        WHERE SUBSTRING(t1.date,1,7) = ?
                        AND   website_id IN (SELECT t2.id
                                             FROM $this->websitesTable t2
                                             WHERE t2.domain_id = (SELECT t3.id
                                                                   FROM $this->domainsTable t3
                                                                   WHERE t3.domain = ? LIMIT 1))) t4
                  GROUP BY t4.day,
                           t4.visitor) t5
            GROUP BY day;
SQLQUERY;
        $monthDayCount =  cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $monthData = array();
        for($i=1;$i<=$monthDayCount;$i++){
            $monthData[$i] = 0;
        }
        //TODO add missing day entries
        $queriedMonthData = $this->queryDatabase($query, array($year."-".sprintf("%02d", $month),$domain));
        foreach($queriedMonthData as $entry){
            $monthData[intval($entry['day'])] = intval($entry['visitors']);
        }
        return $monthData;
    }
    
    public function getUserForDay($domain,$user,$year,$month,$day){
        $this->initializeTables();
        $query = <<<SQLQUERY
            SELECT w.url as url, v.visitor as visitor
            FROM $this->visitsTable v
              JOIN $this->websitesTable w ON v.website_id = w.id
            WHERE v.date = ?
            AND   w.domain_id = (SELECT d.id
                                 FROM $this->domainsTable d
                                 WHERE d.domain = ? LIMIT 1);
SQLQUERY;
        $result = $this->queryDatabase($query, array($year."-".sprintf("%02d", $month)."-".sprintf("%02d", $day),$domain));
        
        
        if(!isset($result[0])){
            return array();
        }
        
        $visitors = array();
        foreach($result as $visit){
            if(!isset($visitors[$visit["visitor"]])){
                $visitors[$visit["visitor"]] = ["url" => $visit["url"],"clicks" => 1];
            }
            else{
                $visitors[$visit["visitor"]]["url"] .= "<br/>".$visit["url"];
                $visitors[$visit["visitor"]]["clicks"] ++;
            }
        }
        $returnData = array();
        foreach($visitors as $visitor){
            $returnData[] = $visitor;
        }
        return $returnData;
        
    }
    
    public function getWebsiteCount($url){
        if(!$this->urlIsValid() || !$this->domainIsValid($url,TRUE)){
            return 0;
        }
        $this->initializeTables();
        $query = <<<SQLQUERY
            SELECT COUNT(*) AS clicks
            FROM $this->visitsTable v
            WHERE v.website_id = (SELECT w.id
                                  FROM $this->websitesTable w
                                  WHERE w.url = ?);
SQLQUERY;
        $result = $this->queryDatabase($query, array($url));
        if(isset($result[0]) && isset($result[0]["clicks"])){
            return $result[0]["clicks"];
        }
        return 0;
    }
    
    public function getDomainCount($url){
        if(!$this->urlIsValid() || !$this->domainIsValid($url,TRUE)){
            return 0;
        }
        $domain = $this->extractDomain($url);
        $this->initializeTables();
        $query = <<<SQLQUERY
            SELECT COUNT(*) AS clicks
            FROM trac_visits v
            WHERE v.website_id IN (SELECT w.id
                                   FROM trac_websites w
                                     JOIN trac_domains d ON w.domain_id = d.id
                                   WHERE d.domain = ?);
SQLQUERY;
        
        $result = $this->queryDatabase($query, array($domain));
        if(isset($result[0]) && isset($result[0]["clicks"])){
            return $result[0]["clicks"];
        }
        return 0;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    private function extractDomain($url){
        $urlObj = parse_url($url);
        if(!isset($urlObj['host'])){
            return NULL;
        }
        return $urlObj['host'];
    }
    
    private function domainIsValid($domain,$parseFromUrl=FALSE){
        if($parseFromUrl){
            $domain = $this->extractDomain($domain);
            if($domain === NULL){
                return false;
            }
        }
        
        $this->initializeTables();
        $query = <<<SQLQUERY
            SELECT COUNT(*) AS domains
            FROM $this->domainsTable d
            WHERE d.domain = ?;
SQLQUERY;
        
        $domainResult = $this->queryDatabase($query, array($domain));
        if(isset($domainResult[0]) && isset($domainResult[0]["domains"]) && $domainResult[0]["domains"]==="1"){
            return true;
        }
        return false;
    }
    
    private function urlIsValid($url){
        return $this->extractDomain($url) === NULL;
    }

}
