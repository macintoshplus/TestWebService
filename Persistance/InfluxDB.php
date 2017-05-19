<?php

/**
 * This file is part of TestWebService package.
 *
 * @author Jean-Baptiste Nahan <jbnahan@gmail.com>
 * @copyright 2017 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\TestWs\Persistance;

class InfluxDB implements PersistanceInterface
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function save(array $datas, $name)
    {
        if (isset($this->config['dsn'])) {
            $database = \InfluxDB\Client::fromDSN($this->config['dsn']);
        } else {
            $client = new \InfluxDB\Client(
                $this->config['host'],
                (!isset($this->config['port']))? 8086:$this->config['port'],
                (!isset($this->config['username']))? '':$this->config['username'],
                (!isset($this->config['password']))? '':$this->config['password'],
                (!isset($this->config['ssl']))? false:boolval($this->config['ssl']),
                (!isset($this->config['verifySSL']))? false:boolval($this->config['verifySSL']),
                (!isset($this->config['timeout']))? 0:intval($this->config['timeout'])
            );
            $database = $client->selectDB($this->config['database']);
        }
        if (!$database->exists()) {
            throw new \Exception("Database does not exists ".$this->config['database']."", 1);
        }
        if (empty($name)) {
            $name='TestWs';
        }
        $points = [];
        foreach ($datas as $key => $object) {
            $tags = [
                'srv_name' => $object->getServerName(),
                'url' => $object->getStats('url'),
                'hostname' => $object->getHostName(),
            ];
            $values = [
                'total_time' => $object->getTotalTime(),
                'success' => $object->isSuccess(),
            ];
            
            $points[] = new \InfluxDB\Point($name, null, $tags, $values, str_replace(".", "", sprintf("%0.06f", $object->getStartedAt())));
        }
        $database->writePoints($points);
    }
}
