<?php

namespace App\Fetchers;

/**
 * Interface FetcherInterface
 * @package App\Fetchers
 */
interface FetcherInterface
{
    /**
     * @param string $source
     */
    public function getDataFromSource($source);

    /**
     * @return mixed
     */
    public function importData();
}
