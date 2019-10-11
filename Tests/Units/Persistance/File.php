<?php
/**
 * @copyright 2016-2019 - Jean-Baptiste Nahan
 * Added by : Macintoshplus at 15/06/18 21:48
 */

namespace Mactronique\TestWs\Tests\Units\Persistance;

use atoum;
use mock;

class File extends atoum
{
    public function testSave()
    {

        $obj = new \mock\Mactronique\TestWs\Model\WsQueryResult([], [], 'dev');
        $this->calling($obj)->getServerName = 'srv1';
        $this->calling($obj)->getStats = 'http://test.local/test';
        $this->calling($obj)->getTotalTime = 0.154;
        $this->calling($obj)->getStartedAt = 123.21;
        $this->calling($obj)->getHostName = 'qasrv1';
        $this->calling($obj)->getRequestedEnv = 'dev';
        $this->calling($obj)->isSuccess = true;
        $this->newTestedInstance(['file' => sys_get_temp_dir().'/tws/file.json']);
        $this->assert('save')
            ->variable($this->testedInstance->save([$obj], ''))->isNull;
    }

    public function testWriteError()
    {
        $obj = new \mock\Mactronique\TestWs\Model\WsQueryResult([], [], 'dev');
        $this->function->is_dir = false;
        $this->function->mkdir = false;
        $this->newTestedInstance(['file' => '/tws/file.json']);
        $this->assert('save in error')
            ->exception(function () use ($obj) {
                $this->testedInstance->save([$obj], 'ws1');
            })->isInstanceOf('Mactronique\TestWs\Persistance\PersistanceException')
            ;
    }
}
