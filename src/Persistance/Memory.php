<?php

/**
 * This file is part of TestWebService package.
 *
 * @author Jean-Baptiste Nahan <macintoshplus@users.noreply.github.com>
 * @copyright 2016-2019 - Jean-Baptiste Nahan
 * @license MIT
 */

namespace Mactronique\TestWs\Persistance;

class Memory implements PersistanceInterface
{

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $datas;

    /**
     * File constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->datas = [];
    }

    /**
     * @param array $datas
     * @param string $name
     * @throws PersistanceException
     */
    public function save(array $datas, string $name)
    {
        if (empty($name)) {
            $name = 'TestWs';
        }
        $tags = [];
        foreach ($datas as $object) {
            $tags[] = [
                'srv_name' => $object->getServerName(),
                'url' => $object->getStats('url'),
                'total_time' => $object->getTotalTime(),
                'success' => $object->isSuccess(),
                'started_at' => $object->getStartedAt(),
                'hostname' => $object->getHostName(),
                'env' => $object->getRequestedEnv(),
            ];
        }

        $this->datas[$name] = $tags;
    }
}
