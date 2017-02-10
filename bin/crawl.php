<?php

include_once __DIR__ . '/../vendor/autoload.php';

$urlString = $argv[1];

$url = new \GuzzleHttp\Psr7\Uri($urlString);
$client = new \GuzzleHttp\Client();

$classes = array();

// $fixFinder = new \Koalamon\EquivalenceClassCrawler\Finder\FixedNameFinder($client);
// $classes = $fixFinder->find($url);

$patternFinder = new \Koalamon\EquivalenceClassCrawler\Finder\PatternFinder($client, 200);
$classes = array_merge($classes, $patternFinder->find($url));
/** @var \Koalamon\EquivalenceClassCrawler\EquivalenceClass[] $classes */

echo "\n  Equivalence Classes found: \n  =========================\n";

foreach ($classes as $class) {
    echo "\n    Url:  " . $class->getUrlString();
}

echo "\n\n";