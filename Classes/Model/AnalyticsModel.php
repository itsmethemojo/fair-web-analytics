
<?php


class AnalyticsModel extends BaseModel{
    
    private function initializeTables(){
        $this->visitsTable = $this->tablePrefix."visits";
        $this->websitesTable = $this->tablePrefix."websites";
        $this->domainsTable = $this->tablePrefix."domains";
    }
    
    public function getVisitsPerMonth($domain,$year){
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
    
    public function getVisitsPerDay($domain,$year,$month){
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
            $monthData[intval($entry['day'])] = $entry['visits'];
        }
        return $monthData;
        
        
    }
    
    public function getClicksForDay($domain,$year,$month,$day){
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

}
