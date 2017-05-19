<?php

/**
 * This file is part of TestWebService package.
 *
 * @author Jean-Baptiste Nahan <jbnahan@gmail.com>
 * @copyright 2017 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\TestWs\Factory;

use Mactronique\TestWs\Model\WsQueryResult;
use Psr\Http\Message\ResponseInterface;

class ResultFactory
{
    private $hostname;

    public function __construct($hostname)
    {
        $this->hostname = $hostname;
    }

    public function makeResult(array $stats, array $responseConfig, ResponseInterface $response = null)
    {
        $stats['hostname'] = $this->hostname;
        return new WsQueryResult($stats, $responseConfig, $response);
    }
}
