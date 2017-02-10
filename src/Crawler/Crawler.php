<?php

namespace Koalamon\EquivalenceClassCrawler\Crawler;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use whm\Html\Document;

class Crawler
{
    private $client;
    private $startUri;
    private $depth;

    private $nonHtmlSuffix = ['jpg', 'png', 'xml', 'bmp', 'ico', 'css', 'js'];

    /**
     * @var Uri
     */
    private $elementQueue = array();

    private $visited = array();

    public function __construct(Client $client, UriInterface $startUri, $depth = 10)
    {
        $this->client = $client;
        $this->startUri = $startUri;
        $this->depth = $depth;

        $this->elementQueue[] = $startUri;
    }

    public function isPotentialHtml(UriInterface $uri)
    {
        $urlString = (string)$uri;

        foreach ($this->nonHtmlSuffix as $suffix) {
            if (strpos($urlString, '.' . $suffix) !== false) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return Uri
     */
    public function next()
    {
        $nextUri = array_pop($this->elementQueue);

        if ($this->depth == 0 || $nextUri === false || $nextUri === null) {
            return false;
        }

        if (!in_array((string)$nextUri, $this->visited)) {
            $this->depth--;
            $this->visited[] = (string)$nextUri;

            if ($this->isPotentialHtml($nextUri)) {
                try {
                    $nextUriResponse = $this->client->get($nextUri);
                    if (strpos($nextUriResponse->getHeaderLine('content-type'), 'text/html') !== false) {

                        $htmlDocument = new Document((string)$nextUriResponse->getBody(), true);

                        $dependencies = $htmlDocument->getDependencies($nextUri);

                        foreach ($dependencies as $dependency) {
                            if ($dependency->getHost() == $this->startUri->getHost()) {
                                $this->elementQueue[] = $dependency;
                            }
                        }
                    }
                } catch (\Exception $exception) {

                }
            }

            return $nextUri;
        } else {
            return $this->next();
        }
    }
}