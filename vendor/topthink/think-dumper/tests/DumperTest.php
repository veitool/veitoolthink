<?php

namespace think\tests\dumper;

use PHPUnit\Framework\TestCase;
use think\dumper\Dumper;

class DumperTest extends TestCase
{
    public function testDump()
    {
        Dumper::dump('aa', 'cc');
        dump(['aa' => 'bbb', 'cc' => 'dd']);
        dump($this);
    }
}
