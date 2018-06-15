<?php
/**
 * @copyright Macintoshplus (c) 2018
 * Added by : Macintoshplus at 15/06/18 22:19
 */

namespace Mactronique\TestWs\Tests\Units\Persistance;

use atoum;

class StorageManager extends atoum
{
    public function testMemory()
    {
        $obj = new \mock\Mactronique\TestWs\Model\WsQueryResult([], [], 'dev');
        $this->calling($obj)->getServerName = 'srv1';
        $this->calling($obj)->getStats = 'http://test.local/test';
        $this->calling($obj)->getTotalTime = 0.154;
        $this->calling($obj)->getStartedAt = 123.21;
        $this->calling($obj)->getHostName = 'qasrv1';
        $this->calling($obj)->getRequestedEnv = 'dev';
        $this->calling($obj)->isSuccess = true;

        $this->newTestedInstance(['type' => 'Memory', 'config' => []]);
        $this->assert('save')
            ->variable($this->testedInstance->save([$obj], 'ws1'))->isNull
        ;
    }

    public function testErrorInit()
    {
        $this->assert('error')
            ->exception(function () {
                $this->newTestedInstance([]);
            })->isInstanceOf('Mactronique\TestWs\Persistance\PersistanceConfigurationException')
            ->hasMessage('The storage type is not set !')
        ;
        $this->assert('error')
            ->exception(function () {
                $this->newTestedInstance(['type'=>'']);
            })->isInstanceOf('Mactronique\TestWs\Persistance\PersistanceConfigurationException')
            ->hasMessage('The storage config is not set !')
        ;
    }

    public function testErrorType()
    {
        $this->newTestedInstance(['type'=>'', 'config'=>'']);
        $this->assert('error')
            ->exception(function () {
                $this->testedInstance->save([], '');
            })->isInstanceOf('Mactronique\TestWs\Persistance\PersistanceException')
            ->hasMessage('The class Mactronique\\TestWs\\Persistance\\ is not found')
        ;
    }
}