<?php

namespace Koalamon\EquivalenceClassCrawler\Finder;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Koalamon\EquivalenceClassCrawler\EquivalenceClass;
use Psr\Http\Message\UriInterface;

class FixedNameFinder implements Finder
{
    private $client;

    private $fixedNames = [
        'Sitemap' => 'sitemap.xml',
        'robots.txt' => 'robots.txt',
        'Favicon' => 'favicon.ico'
    ];

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function find(UriInterface $uri)
    {
        $classes = array();

        foreach ($this->fixedNames as $key => $fixedName) {
            $fixedFileUrlString = $uri->getScheme() . '://' . $uri->getHost() . '/' . $fixedName;

            echo "checking : " . $fixedFileUrlString . "\n";

            try {
                $repsonse = $this->client->get($fixedFileUrlString);
                if ($repsonse->getStatusCode() == 200) {
                    $classes[] = new EquivalenceClass($key, $fixedFileUrlString);

                }
            } catch (ClientException $e) {

            }
        }

        return $classes;
    }
}