<?php

namespace Koalamon\EquivalenceClassCrawler\Finder;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Koalamon\EquivalenceClassCrawler\Crawler\Crawler;
use Koalamon\EquivalenceClassCrawler\EquivalenceClass;
use Psr\Http\Message\UriInterface;

class PatternFinder implements Finder
{
    const URL_KEY = 'URLS';

    private $client;
    private $depth;

    private $translationTable = array();

    public function __construct(Client $client, $depth = 100)
    {
        $this->client = $client;
        $this->depth = $depth;
    }

    /**
     * http://stackoverflow.com/questions/28285019/php-transform-associative-array-of-string-in-tree-structure
     *
     * @param $entry
     * @param $depth
     * @param $current
     */
    private function processPath($entry, $depth, &$current)
    {
        if ($depth < count($entry)) {
            $key = $entry[$depth];

            if (!isset($current[$key])) {
                if ($depth == count($entry) - 1) {
                    $current[self::URL_KEY][] = '/' . $key;
                    return;
                } else {
                    $current[$key] = null;
                }
            }
            $this->processPath($entry, $depth + 1, $current[$key]);
        }
    }

    private function normalizePath($path)
    {
        if ($path == "") {
            $this->translationTable['/#HOMEPAGE#/'] = "";
            return '/#HOMEPAGE#/';
        }

        $normalizedPath = preg_replace('^[0-9]+x[0-9]+^', '#IMAGEFORMAT#', $path);
        $normalizedPath = preg_replace("^[0-9]{4}/(0[1-9]|1[0-2])/(0[1-9]|[1-2][0-9]|3[0-1])^", '#DATE#', $normalizedPath);
        $normalizedPath = preg_replace("^/[a-zA-Z0-9]{2}/[a-zA-Z0-9]{2}/^", '/#UPLOAD_2#/', $normalizedPath);
        $normalizedPath = preg_replace("^/[a-zA-Z0-9]{3}/[a-zA-Z0-9]{3}/^", '/#UPLOAD_3#/', $normalizedPath);

        $normalizedPath = preg_replace("^/[0-9]+/^", '/#ID#/', $normalizedPath);

        $normalizedPath = preg_replace("#^/[0-9a-zA-Z\-\_]+/[0-9a-zA-Z\-\_]+/$#", '/#DIRECTORY_2#/', $normalizedPath);

        $this->translationTable[$normalizedPath] = $path;

        return $normalizedPath;
    }

    private function filterTree(array $tree, $classes = array(), $path = "")
    {
        if (array_key_exists(self::URL_KEY, $tree)) {
            // $classes[] = new EquivalenceClass('', $this->translationTable[$path . $tree[self::URL_KEY][0]]);
            $classes[] = new EquivalenceClass('', $path . $tree[self::URL_KEY][0]);
            unset($tree[self::URL_KEY]);
        }

        if (is_array($tree)) {
            foreach ($tree as $key => $treeElement) {
                if ($key != self::URL_KEY) {
                    $classes = $this->filterTree($treeElement, $classes, $path . '/' . $key);
                }
            }
        }

        return $classes;
    }

    public function find(UriInterface $uri)
    {
        $crawler = new Crawler($this->client, $uri, $this->depth);

        $tree = array();

        while ($nextUri = $crawler->next()) {
            $path = $nextUri->getPath();
            $normalizedPath = $this->normalizePath($path);
            $pathElements = explode('/', substr($normalizedPath, 1));
            $this->processPath($pathElements, 0, $tree);
        }

        $classes = $this->filterTree($tree);

        return $classes;
    }
}