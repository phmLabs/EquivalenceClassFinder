<?php

namespace Koalamon\EquivalenceClassCrawler\Finder;

use Koalamon\EquivalenceClassCrawler\EquivalenceClass;
use Psr\Http\Message\UriInterface;

interface Finder
{
    /**
     * @param Uri $uri
     * @return EquivalenceClass[]
     */
    public function find(UriInterface $uri);
}