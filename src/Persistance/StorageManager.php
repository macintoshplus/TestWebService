<?php

/**
 * This file is part of TestWebService package.
 *
 * @author Jean-Baptiste Nahan <jbnahan@gmail.com>
 * @copyright 2017 - Jean-Baptiste Nahan
 * @license MIT
 */

namespace Mactronique\TestWs\Persistance;

class StorageManager implements PersistanceInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * StorageManager constructor.
     * @param array $config
     * @throws PersistanceConfigurationException
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        if (!isset($config['type'])) {
            throw new PersistanceConfigurationException("The storage type is not set !", 1);
        }
        if (!isset($config['config'])) {
            throw new PersistanceConfigurationException("The storage config is not set !", 1);
        }
    }

    /**
     * @param array $datas
     * @param string $name
     * @return mixed
     * @throws PersistanceException
     */
    public function save(array $datas, string $name)
    {
        $handler = sprintf('Mactronique\TestWs\Persistance\%s', $this->config['type']);
        if (!class_exists($handler)) {
            throw new PersistanceException("The class " . $handler . " is not found", 1);
        }

        $handler = new $handler($this->config['config']);
        return $handler->save($datas, $name);
    }
}
