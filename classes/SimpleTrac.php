<?php

require_once 'config/define.php';

class SimpleTrac{
    
    private $dbLink;
    
    public function __construct() {
        $this->dbLink = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_DATABASENAME, DB_PORT);
        if (!$this->dbLink) {
            throw new Exception('Couldn\'t connect to db. Check db config!');
        }
    }

    public function __destruct() {
        if ($this->dbLink) {
            mysqli_close($this->dbLink);
        }
    }
    
    public function getAllowedDomains(){
        
        //TODO memcache
        $query = "SELECT domain FROM ".DB_TABLEPREFIX."domains";
        $stmt = mysqli_prepare($this->dbLink, $query);
        //mysqli_stmt_bind_param($stmt, "s", $website);
        mysqli_stmt_execute($stmt);
        $result = $stmt->get_result();
        $domains = array();
        while ($row = $result->fetch_array(MYSQLI_ASSOC)){
            $domains[] = $row['domain']; 
        }
        mysqli_stmt_close($stmt);
        
        return $domains;
    }
    
    public function saveCall($website,$visitor){
        
        $dateString = date_format(new DateTime(), 'Y-m-d');
        $visitor = $this->transformIP($visitor,$dateString);
        $id = $this->alreadyCreated($website);
        
        if(!is_int($id)){
            $id = $this->create($website);
        }
        
        if(!$this->alreadyCounted($id, $visitor,$dateString)){
            $this->count($id, $visitor,$dateString);
        }
    }
    
    private function alreadyCreated($website){
        
        //TODO use memecache

        $query = "SELECT id FROM ".DB_TABLEPREFIX."websites WHERE url = ? LIMIT 0,1";
        $stmt = mysqli_prepare($this->dbLink, $query);
        mysqli_stmt_bind_param($stmt, "s", $website);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        
        if(isset($id) && $id){
            return $id;
        }
        return NULL;

    }

    private function alreadyCounted($websiteId,$visitor,$dateString){
               
        $query = "SELECT website_id FROM ".DB_TABLEPREFIX."visits WHERE website_id = ? AND visitor = ? AND date = ? LIMIT 0,1";
        error_log($query);
        $stmt = mysqli_prepare($this->dbLink, $query);
        mysqli_stmt_bind_param($stmt, "sss", $websiteId,$visitor,$dateString);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if(isset($id) && $id){
            return $id;
        }
        return NULL;
    }
    
    private function create($website){
        $domainId = $this->getDomainId($website);
        if($domainId==NULL){
            return NULL;
        }
        $query = "INSERT INTO ".DB_TABLEPREFIX."websites (url,domain_id) VALUES (?,?)";
        $stmt = mysqli_prepare($this->dbLink, $query);
        mysqli_stmt_bind_param($stmt, "ss", $website,$domainId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $this->alreadyCreated($website);
    }
    
    private function count($websiteId,$visitor,$dateString){
        //TODO kill count cache and create new
        $query = "INSERT INTO ".DB_TABLEPREFIX."visits (website_id, visitor, date) VALUES (?,?,?)";
        $stmt = mysqli_prepare($this->dbLink, $query);
        mysqli_stmt_bind_param($stmt, "sss", $websiteId,$visitor,$dateString);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    private function transformIP($ip,$date){
        return md5($date.$ip.DB_IPHASHSALT);
    }
    
    private function getDomainId($website){
        //TODO use memecache
        $url = parse_url($website);
        if(!isset($url['host'])){
            return NULL;
        }
        $query = "SELECT id FROM ".DB_TABLEPREFIX."domains WHERE domain = ? LIMIT 0,1";
        $stmt = mysqli_prepare($this->dbLink, $query);
        mysqli_stmt_bind_param($stmt, "s", $url['host']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        
        if(isset($id) && $id){
            return $id;
        }
        return NULL;
    }
    
    public function getOverallCount($website){
        $websiteId = $this->alreadyCreated($website);
        
        $query = "SELECT count(*) FROM ".DB_TABLEPREFIX."visits WHERE website_id = ? LIMIT 0,1";
        $stmt = mysqli_prepare($this->dbLink, $query);
        mysqli_stmt_bind_param($stmt, "s", $websiteId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        
        if(isset($id) && $id){
            return $id;
        }
        return 0;
    }
    
    public function getCountList(){
        //TODO memcache
        $query = "SELECT count(*) as count, url FROM ".DB_TABLEPREFIX."websites JOIN ".DB_TABLEPREFIX."visits on ".DB_TABLEPREFIX."websites.id = ".DB_TABLEPREFIX."visits.website_id GROUP BY id ORDER BY count(*) DESC";
        $stmt = mysqli_prepare($this->dbLink, $query);
        mysqli_stmt_execute($stmt);
        $result = $stmt->get_result();
        $websites = array();
        while ($row = $result->fetch_array(MYSQLI_ASSOC)){
            $websites[]=$row;
        }
        mysqli_stmt_close($stmt);
        
        return $websites;
    }

    public function getWebsiteClicks($website){
        $websiteId = $this->alreadyCreated($website);
        $query = "SELECT count(*) as count, date FROM ".DB_TABLEPREFIX."visits WHERE website_id = ? GROUP BY date ORDER BY date DESC";
        $stmt = mysqli_prepare($this->dbLink, $query);
        mysqli_stmt_bind_param($stmt, "s", $websiteId);
        mysqli_stmt_execute($stmt);
        $result = $stmt->get_result();
        $dates = array();
        while ($row = $result->fetch_array(MYSQLI_ASSOC)){
            $dates[]=$row;
        }
        mysqli_stmt_close($stmt);
        
        return $dates;
    }
}

?>
