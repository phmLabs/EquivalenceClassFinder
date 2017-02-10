<?php

namespace Koalamon\EquivalenceClassCrawler;

class EquivalenceClass
{
    private $name;
    private $urlString;

    /**
     * EquivalenceClass constructor.
     * @param $name
     * @param $urlString
     */
    public function __construct($name, $urlString)
    {
        $this->name = $name;
        $this->urlString = $urlString;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getUrlString()
    {
        return $this->urlString;
    }
}
