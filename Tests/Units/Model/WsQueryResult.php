<?php

/**
 * This file is part of TestWebService package.
 *
 * @author Jean-Baptiste Nahan <jbnahan@gmail.com>
 * @copyright 2017 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\TestWs\Tests\Units\Model;

use atoum;
use mock;

class WsQueryResult extends atoum
{
    public function test()
    {
        $this->assert('noRequest')
            ->if($this->newTestedInstance([], []))
            ->then
                ->boolean($this->testedInstance->isSuccess())->isFalse
                ->string($this->testedInstance->getServerName())->isEmpty
                ->array($this->testedInstance->getHeader('test'))->isEmpty
                ->string($this->testedInstance->getStats('test', ''))->isEmpty
                ->float($this->testedInstance->getTotalTime())->isEqualTo(0.0)
                ->float($this->testedInstance->getStartedAt())->isEqualTo(0.0)
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
            ->if($this->newTestedInstance(
                [
                    'total_time' => 0.2345,
                    'started_at' => 123423.24,
                ],
                [
                    'http_code'=>'200',
                    'server_header'=>'Site',
                ],
                $mockRequest
            ))
            ->then
                ->boolean($this->testedInstance->isSuccess())->isTrue
                ->string($this->testedInstance->getServerName())->isEqualTo('SRV1')
                ->array($this->testedInstance->getHeader('test'))->hasSize(1)->contains('header test')
                ->string($this->testedInstance->getStats('test', ''))->isEmpty
                ->float($this->testedInstance->getTotalTime())->isEqualTo(0.2345)
                ->float($this->testedInstance->getStartedAt())->isEqualTo(123423.24)
        ;
    }
}
