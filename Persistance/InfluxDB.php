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

    /**
     * @var array
     */
    private $config;

    /**
     * @var \InfluxDB\Client
     */
    private $database;

    /**
     * InfluxDB constructor.
     * @param array $config
     * @param \InfluxDB\Database|null $database
     */
    public function __construct(array $config, \InfluxDB\Database $database = null)
    {
        $this->config = $config;
        $this->database = $database;
    }


    /**
     * @param array $datas
     * @param string $name
     * @return mixed|void
     * @throws PersistanceException
     */
    public function save(array $datas, string $name)
    {
        try {
            $database = $this->selectDatabase();
            if (!$database->exists()) {
                throw new PersistanceException("Database does not exists " . $this->config['database'] . "", 1);
            }
            if (empty($name)) {
                $name = 'TestWs';
            }
            $points = [];
            foreach ($datas as $key => $object) {
                $tags = [
                    'srv_name' => $object->getServerName(),
                    'url' => $object->getStats('url'),
                    'hostname' => $object->getHostName(),
                    'env' => $object->getRequestedEnv(),
                ];
                $values = [
                    'total_time' => $object->getTotalTime(),
                    'success' => $object->isSuccess(),
                ];

                $points[] = new \InfluxDB\Point($name, null, $tags, $values,
                    str_replace(".", "", sprintf("%0.03f", $object->getStartedAt())));
            }
            $database->writePoints($points, \InfluxDB\Database::PRECISION_MILLISECONDS);
        } catch (\InfluxDB\Exception $e) {
            throw new PersistanceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function selectDatabase(): \InfluxDB\Database
    {
        if ($this->database !== null) {
            return $this->database;
        }

        if (isset($this->config['dsn'])) {
            return \InfluxDB\Client::fromDSN($this->config['dsn']);
        }

        $client = new \InfluxDB\Client(
            $this->config['host'],
            (!isset($this->config['port'])) ? 8086 : $this->config['port'],
            (!isset($this->config['username'])) ? '' : $this->config['username'],
            (!isset($this->config['password'])) ? '' : $this->config['password'],
            (!isset($this->config['ssl'])) ? false : boolval($this->config['ssl']),
            (!isset($this->config['verifySSL'])) ? false : boolval($this->config['verifySSL']),
            (!isset($this->config['timeout'])) ? 0 : intval($this->config['timeout'])
        );
        return $client->selectDB($this->config['database']);
    }
}
