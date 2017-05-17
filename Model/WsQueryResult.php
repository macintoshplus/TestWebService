<?php

/**
 * This file is part of TestWebService package.
 *
 * @author Jean-Baptiste Nahan <jbnahan@gmail.com>
 * @copyright 2017 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\TestWs\Model;

use Psr\Http\Message\ResponseInterface;

class WsQueryResult
{
    private $stats;
    private $response;
    private $responseConfig;

    public function __construct(array $stats, array $responseConfig, ResponseInterface $response = null)
    {
        $this->stats = $stats;
        $this->response = $response;
        $this->responseConfig = $responseConfig;
    }

    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->response !== null && $this->response->getStatusCode() == $this->responseConfig['http_code'];
    }

    /**
     * @return string
     */
    public function getServerName()
    {
        $header = ($this->response === null)? []:$this->response->getHeader($this->responseConfig['server_header']);
        return (empty($header)) ? '':$header[0];
    }

    /**
     * @param string $name The header name
     * @return array
     */
    public function getHeader($name)
    {
        return ($this->response === null)? []:$this->response->getHeader($name);
    }

    /**
     * @param string $name The name of key for get value in stats array.
     * @param string $default The value if keys value does not exists.
     * @return mixed
     */
    public function getStats($name, $default = '')
    {
        return isset($this->stats[$name])? $this->stats[$name]:$default;
    }

    /**
     * @return float
     */
    public function getTotalTime()
    {
        return (is_array($this->stats) && isset($this->stats['total_time']))? floatval($this->stats['total_time']):0.0;
    }

    /**
     * @return float
     */
    public function getStartedAt()
    {
        return (is_array($this->stats) && isset($this->stats['started_at']))? floatval($this->stats['started_at']):0.0;
    }
}
