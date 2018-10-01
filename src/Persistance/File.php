<?php

/**
 * This file is part of TestWebService package.
 *
 * @author Jean-Baptiste Nahan <jbnahan@gmail.com>
 * @copyright 2017 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\TestWs\Persistance;

class File implements PersistanceInterface
{

    /**
     * @var array
     */
    private $config;

    /**
     * File constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $datas
     * @param string $name
     * @throws PersistanceException
     */
    public function save(array $datas, string $name)
    {
        if (empty($name)) {
            $name='TestWs';
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

        if (!is_dir(dirname($this->config['file'])) && !mkdir(dirname($this->config['file']), 0777, true) && !is_dir(dirname($this->config['file']))) {
            throw new PersistanceException("Unable to make dir ".dirname($this->config['file']), 1);
        }
        //echo "Backup into ".$this->config['file']."\n";
        file_put_contents($this->config['file'], json_encode($tags));
    }
}
