<?php

/**
 * This file is part of TestWebService package.
 *
 * @author Jean-Baptiste Nahan <jbnahan@gmail.com>
 * @copyright 2017 - Jean-Baptiste Nahan
 * @license MIT
 */

namespace Mactronique\TestWs\Persistance;

interface PersistanceInterface
{
    /**
     * @param array $datas
     * @param string $name
     * @return mixed
     * @throw PersistanceException
     */
    public function save(array $datas, string $name);
}
