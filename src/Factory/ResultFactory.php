<?php

/**
 * This file is part of TestWebService package.
 *
 * @author Jean-Baptiste Nahan <macintoshplus@users.noreply.github.com>
 * @copyright 2016-2019 - Jean-Baptiste Nahan
 * @license MIT
 */

namespace Mactronique\TestWs\Factory;

use Mactronique\TestWs\Model\WsQueryResult;
use Psr\Http\Message\ResponseInterface;

class ResultFactory
{
    /**
     * @var string
     */
    private $hostname;

    /**
     * ResultFactory constructor.
     * @param $hostname
     */
    public function __construct($hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * @param array $stats
     * @param array $responseConfig
     * @param string $requestedEnv
     * @param ResponseInterface|null $response
     * @return WsQueryResult
     */
    public function makeResult(
        array $stats,
        array $responseConfig,
        string $requestedEnv,
        ResponseInterface $response = null
    ) {
        $stats['hostname'] = $this->hostname;
        return new WsQueryResult($stats, $responseConfig, $requestedEnv, $response);
    }
}
