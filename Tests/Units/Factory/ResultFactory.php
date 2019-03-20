<?php

/**
 * This file is part of TestWebService package.
 *
 * @author Jean-Baptiste Nahan <macintoshplus@users.noreply.github.com>
 * @copyright 2017 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\TestWs\Tests\Units\Factory;

use atoum;
use mock;

class ResultFactory extends atoum
{
    public function test()
    {
        $this->assert('noRequest')
            ->if($this->newTestedInstance('localhost'))
            ->then
                ->object($result = $this->testedInstance->makeResult([], [], ''))->isInstanceOf('Mactronique\TestWs\Model\WsQueryResult')
                ->boolean($result->isSuccess())->isFalse
                ->string($result->getServerName())->isEmpty
                ->array($result->getHeader('test'))->isEmpty
                ->string($result->getStats('test', ''))->isEmpty
                ->float($result->getTotalTime())->isEqualTo(0.0)
                ->float($result->getStartedAt())->isEqualTo(0.0)
                ->string($result->getRequestedEnv())->isEqualTo('')
                ->string($result->getHostName())->isEqualTo('localhost')
        ;
    }

    public function testWithRequest()
    {
        $mockRequest = new \mock\Psr\Http\Message\ResponseInterface();
        $this->calling($mockRequest)->getStatusCode = '200';
        $this->calling($mockRequest)->getHeader = function ($header) {
            if ($header == 'Site') {
                return ['SRV1'];
            }
            return ['header '.$header];
        };
        $this->assert('with Request')
            ->if($this->newTestedInstance('localhost'))
            ->then
                ->object($result = $this->testedInstance->makeResult(
                    [
                        'total_time' => 0.2345,
                        'started_at' => 123423.24,
                        'hostname' => 'hostname',
                    ],
                    [
                        'http_code'=>'200',
                        'server_header'=>'Site',
                    ],
                    'test',
                    $mockRequest
                ))->isInstanceOf('Mactronique\TestWs\Model\WsQueryResult')
                ->boolean($result->isSuccess())->isTrue
                ->string($result->getServerName())->isEqualTo('SRV1')
                ->array($result->getHeader('test'))->hasSize(1)->contains('header test')
                ->string($result->getStats('test', ''))->isEmpty
                ->float($result->getTotalTime())->isEqualTo(0.2345)
                ->float($result->getStartedAt())->isEqualTo(123423.24)
                ->string($result->getRequestedEnv())->isEqualTo('test')
                ->string($result->getHostName())->isEqualTo('localhost')
        ;
    }
}
