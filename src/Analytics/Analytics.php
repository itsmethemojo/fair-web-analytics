<?php

namespace Itsmethemojo\Analytics;

use Itsmethemojo\Storage\Database;
use Itsmethemojo\Storage\QueryParameters;
use DateTime;
use Exception;

class Analytics
{
    /** @var Database * */
    private $database;

    /** @var mixed * */
    private $stringStore = array();

    public function __construct()
    {
        $this->database = new Database();
    }

    public function getUsersPerDay($domain, $year, $month, $day)
    {

        $domainId = $this->getDomainId($domain);

        $query = <<<SQLQUERY
            SELECT w.url as url, v.visitor as visitor
            FROM #visits v
                JOIN #websites w ON v.website_id = w.id
            WHERE v.date = ?
            AND   w.domain_id = ?;
SQLQUERY;

        $yearAndMonthAndDay = $year . "-" .
            sprintf("%02d", $month) . "-" . sprintf("%02d", $day);
        $params             = new QueryParameters();
        $params->add($yearAndMonthAndDay)->add($domainId);

        $results = $this->database->read(
            array('user-on-day-' . $domain . '-' . $yearAndMonthAndDay),
            $query,
            $params,
            false,
            60 * 60 * 24
        );

        $return = array();
        foreach ($results as $result) {
            $return[$result['visitor']][] = $result['url'];
        }
        return $return;
    }

    public function getVisitsPerDay($domain, $year, $month, $day)
    {

        $domainId = $this->getDomainId($domain);

        $query = <<<SQLQUERY
            SELECT count(*) as count, w.url as url
            FROM #visits v
                JOIN #websites w ON v.website_id = w.id
            WHERE v.date = ?
            AND   w.domain_id = ? group by v.website_id;
SQLQUERY;

        $yearAndMonthAndDay = $year . "-" .
            sprintf("%02d", $month) . "-" . sprintf("%02d", $day);
        $params             = new QueryParameters();
        $params->add($yearAndMonthAndDay)->add($domainId);

        $results = $this->database->read(
            array('visits-on-day-' . $domain . '-' . $yearAndMonthAndDay),
            $query,
            $params,
            false,
            60 * 60 * 24
        );

        $return = array();
        foreach ($results as $result) {
            $return[$result['url']] = intval($result['count']);
        }
        return $return;
    }

    public function getVisitsPerMonth($domain, $year, $month)
    {
        //validate domain
        $domainId = $this->getDomainId($domain);

        $query = <<<SQLQUERY
            SELECT COUNT(*) AS visits,
                   t3.day AS day
            FROM (SELECT ABS(SUBSTRING(t1.date,9,2)) AS DAY
                  FROM #visits t1
                  WHERE SUBSTRING(t1.date,1,7) = ?
                  AND   t1.website_id IN (SELECT t2.id
                                          FROM #websites t2
                                          WHERE t2.domain_id = ?)) t3
            GROUP BY t3.day;
SQLQUERY;

        $monthDayCount = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $monthData     = array();
        for ($i = 1; $i <= $monthDayCount; $i++) {
            $monthData[$i] = 0;
        }

        $yearAndMonth = $year . "-" . sprintf("%02d", $month);
        $params       = new QueryParameters();
        $params->add($yearAndMonth)->add($domainId);

        $results = $this->database->read(
            array('visits-per-day-' . $domain . '-' . $yearAndMonth),
            $query,
            $params,
            false,
            60 * 60 * 24
        );

        foreach ($results as $result) {
            $monthData[intval($result['day'])] = intval($result['visits']);
        }
        return $monthData;
    }

    public function getVisitsPerYear($domain, $year)
    {
        //validate domain
        $domainId = $this->getDomainId($domain);

        $query = <<<SQLQUERY
            SELECT t4.month AS month,
                   COUNT(*) AS visits
            FROM (SELECT t1.website_id,
                         t1.visitor,
                         SUBSTRING(t1.date,1,4) AS YEAR,
                         SUBSTRING(t1.date,6,2) AS MONTH
                  FROM #visits t1
                  WHERE t1.website_id IN (SELECT t2.id
                                          FROM #websites t2
                                          WHERE t2.domain_id = ?)) t4
            WHERE t4.year = ?
            GROUP BY t4.month;
SQLQUERY;

        $yearData = array();
        for ($i = 1; $i < 13; $i++) {
            $yearData[$i] = 0;
        }

        $params = new QueryParameters();
        $params->add($domainId)->add($year);

        $results = $this->database->read(
            array('visits-per-month-' . $domain . '-' . $year),
            $query,
            $params,
            false,
            60 * 60 * 24
        );

        foreach ($results as $result) {
            $yearData[intval($result['month'])] = $result['visits'];
        }
        return $yearData;
    }

    public function getTotalVisits($url)
    {
        $query = 'SELECT count(*) as count, url FROM #websites ' .
            'JOIN #visits on #websites.id = #visits.website_id ' .
            'GROUP BY id ORDER BY count(*) DESC';

        $params = new QueryParameters();
        $params->add($this->getDomainIdForUrl($url));



        $results = $this->database->read(
            array('total-visits-'),
            $query,
            $params,
            false,
            60 * 10
        );

        $urlPath = $this->getExtractedPath($url);
        foreach ($results as $result) {
            if ($result['url'] === $urlPath) {
                return $result;
            }
        }

        return array('count' => 0, 'url' => $urlPath);
    }

    public function saveClick($ip, $url)
    {

        $params = new QueryParameters();

        $websiteId  = $this->getIdForUrl($url);
        $hash       = $this->getHashedVisitor($ip);
        $dateString = $this->getDateString();

        $visits = $this->database->getFromStore('visits-' . $dateString);

        if (!$visits) {
            $visits = array();
        }

        if (array_key_exists($websiteId . '-' . $hash, $visits)) {
            return;
        }

        $params->add($websiteId)
            ->add($hash)
            ->add($dateString);

        //do not automaticly store query
        $results = $this->database->read(
            array(),
            'SELECT website_id FROM #visits WHERE website_id = ? AND visitor = ? AND date = ?',
            $params,
            true
        );

        if (count($results) === 1) {
            //nothing to save
            // this should never happen

            $visits[$websiteId . '-' . $hash] = 1;
            $this->database->putInStore(
                'visits-' . $dateString,
                $visits,
                24 * 60 * 60
            );
            return;
        }

        if (count($results) > 1) {
            //shouldn't happen either
            throw new Exception("multiple unique visits. WTF");
        }

        $this->database->modify(
            array(),
            'INSERT INTO #visits (website_id, visitor, date) VALUES (?,?,?)',
            $params,
            true
        );

        $visits[$websiteId . '-' . $hash] = 1;
        $this->database->putInStore(
            'visits-' . $dateString,
            $visits,
            24 * 60 * 60
        );
    }

    //-------------------------
    //helpers

    private function getDateString()
    {
        if (!isset($this->stringStore['dateString'])) {
            //TODO check for newer function
            $this->stringStore['dateString'] = date_format(
                new DateTime(),
                'Y-m-d'
            );
        }
        return $this->stringStore['dateString'];
    }

    private function getHashedVisitor($ip)
    {
        if (!isset($this->stringStore['hashedVisitor-' . $ip])) {
            //TODO check to include salt
            $this->stringStore['hashedVisitor-' . $ip] = md5($this->getDateString() . $ip);
        }
        return $this->stringStore['hashedVisitor-' . $ip];
    }

    private function getExtractedDomain($url)
    {
        $urlData = parse_url($url);
        if (!isset($urlData['host'])) {
            throw new Exception("no valid URL: " . $url);
        }
        return $urlData['host'];
    }

    private function getExtractedPath($url)
    {
        $urlData = parse_url($url);
        if (!isset($urlData['path'])) {
            throw new Exception("no valid URL: " . $url);
        }
        return $urlData['path'];
    }

    private function getAllowedDomains()
    {
        $results = $this->database->read(
            array('domains'),
            'SELECT id, domain FROM #domains'
        );
        $domains = array();
        foreach ($results as $result) {
            $domains[] = $result['domain'];
        }
        return $domains;
    }

    private function getDomainId($domain)
    {
        $results = $this->database->read(
            array('domains'),
            'SELECT id, domain FROM #domains'
        );
        foreach ($results as $result) {
            if ($result['domain'] === $domain) {
                return $result['id'];
            }
        }
        throw new Exception("domain not allowed: " . $domain);
    }

    private function getDomainIdForUrl($url)
    {
        return $this->getDomainId($this->getExtractedDomain($url));
    }

    private function getIdForUrl($url)
    {
        $extractedDomain = $this->getExtractedDomain($url);
        $urlPath         = $this->getExtractedPath($url);

        $params = new QueryParameters();
        $params->add($urlPath)->add($this->getDomainId($extractedDomain));

        $results = $this->database->read(
            array('id-' . $urlPath),
            'SELECT id FROM #websites WHERE url = ? and domain_id = ?',
            $params
        );
        if (count($results) === 1) {
            return $results[0]['id'];
        }

        $this->database->modify(
            array('id-' . $urlPath),
            'INSERT INTO #websites (url,domain_id) VALUES (?,?)',
            $params
        );

        $results = $this->database->read(
            array('id-' . $urlPath),
            'SELECT id FROM #websites WHERE url = ? and domain_id = ?',
            $params
        );
        if (count($results) === 1) {
            return $results[0]['id'];
        }

        throw new Exception("cannot be saved. why?. dunno");
    }
}
