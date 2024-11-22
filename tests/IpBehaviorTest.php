<?php

namespace shyevsa\ipbehavior\test;

use shyevsa\ipbehavior\IpBehavior;
use PHPUnit\Framework\TestCase;

class IpBehaviorTest extends TestCase
{
    public function testBlob2ip() {
        $this->assertEquals('127.0.0.1', IpBehavior::blob2ip("\x7f\x00\x00\x01"));
        $this->assertEquals('::1', IpBehavior::blob2ip("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01"));
    }

    public function testIp2blob() {
        $this->assertEquals("\x7f\x00\x00\x01", IpBehavior::ip2blob('127.0.0.1'));
        $this->assertEquals("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01", IpBehavior::ip2blob('::1'));
    }
}
