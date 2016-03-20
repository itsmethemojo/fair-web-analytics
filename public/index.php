<?php

header("Cache-Control: public, max-age=0, no-cache");
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

require __DIR__ .'/../vendor/autoload.php';

$app = new \Slim\App();
$app->add(new RKA\Middleware\IpAddress(true));
$app->get(
    '/pixel/',
    function ($request, $response, $args) {
        if (array_key_exists('HTTP_REFERER', $request->getServerParams())) {
            $analytics = new Itsmethemojo\Analytics\Analytics();
            $analytics->saveClick(
                $request->getAttribute('ip_address'),
                $request->getServerParams()['HTTP_REFERER']
            );
        }
        
        return $response->withRedirect(
            explode(
                '/pixel/',
                $request->getServerParams()['REQUEST_URI'],
                2
            )[0].'/images/pixel.png'
        );
    }
);

$app->get(
    '/count/',
    function ($request, $response, $args) {
        if (array_key_exists('HTTP_REFERER', $request->getServerParams())) {
            $analytics = new Itsmethemojo\Analytics\Analytics();
            $data = $analytics->getTotalVisits(
                $request->getServerParams()['HTTP_REFERER']
            );
            return $response->withJson($data);
        }
        //TODO error message in response?
        return $response->withStatus(400)->withJson(
            array(
                'error' => 'request expects referer'
            )
        );
    }
);

$app->get(
    '/statistics/visits/{domain}/{year}/',
    function ($request, $response, $args) {
        $analytics = new Itsmethemojo\Analytics\Analytics();
        $data = $analytics->getVisitsPerYear(
            $args['domain'],
            $args['year']
        );
        return $response->withJson($data);
    }
);

$app->get(
    '/statistics/visits/{domain}/{year}/{month}/',
    function ($request, $response, $args) {
        $analytics = new Itsmethemojo\Analytics\Analytics();
        $data = $analytics->getVisitsPerMonth(
            $args['domain'],
            $args['year'],
            $args['month']
        );
        return $response->withJson($data);
    }
);

$app->get(
    '/statistics/visits/{domain}/{year}/{month}/{day}/',
    function ($request, $response, $args) {
        $analytics = new Itsmethemojo\Analytics\Analytics();
        $data = $analytics->getVisitsPerDay(
            $args['domain'],
            $args['year'],
            $args['month'],
            $args['day']
        );
        return $response->withJson($data);
    }
);

$app->get(
    '/statistics/users/{domain}/{year}/{month}/{day}/',
    function ($request, $response, $args) {
        $analytics = new Itsmethemojo\Analytics\Analytics();
        $data = $analytics->getUsersPerDay(
            $args['domain'],
            $args['year'],
            $args['month'],
            $args['day']
        );
        return $response->withJson($data);
    }
);

$app->run();
