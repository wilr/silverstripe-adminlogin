<?php

/**
 * Class IpAccessTest
 *
 * @mixin PHPUnit_Framework_TestCase
 */
class IpAccessTest extends SapphireTest
{

    protected $allowedIps = array(
        '192.168.1.101',
        '192.168.1.100-200',
        '192.168.1.0/24',
        '192.168.1.*'
    );

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testSettingAllowedIpsIsDeprecated()
    {
        $obj             = new IpAccess('192.168.1.101');
        $obj->allowedIps = $this->allowedIps;

        $obj->getAllowedIps();
    }

    public function testAllowWhenDisabled()
    {
        Config::inst()->update('IpAccess', 'enabled', false);
        Config::inst()->update('IpAccess', 'allowed_ips', $this->allowedIps);

        $obj = new IpAccess('192.168.2.101');
        $this->assertTrue($obj->hasAccess());

        Config::inst()->remove('IpAccess', 'enabled');
        Config::inst()->update('IpAccess', 'enabled', true);
        $this->assertFalse($obj->hasAccess());
    }

    public function testAllowedIpsIsEmpty()
    {
        Config::inst()->update('IpAccess', 'enabled', true);
        Config::inst()->update('IpAccess', 'allowed_ips', array());
        $obj = new IpAccess('192.168.1.101');
        $this->assertEmpty($obj->getAllowedIps());
    }

    public function testHasAccess()
    {
        Config::inst()->update('IpAccess', 'enabled', true);
        Config::inst()->update('IpAccess', 'allowed_ips', array());
        $obj = new IpAccess('192.168.1.101');

        $this->assertTrue($obj->hasAccess());

        Config::inst()->update('IpAccess', 'allowed_ips', $this->allowedIps);

        $obj->setIp('192.168.1.101');
        $this->assertTrue($obj->hasAccess());

        $obj->setIp('192.168.1.102');
        $this->assertTrue($obj->hasAccess());

        $obj->setIp('192.168.1.201');
        $this->assertTrue($obj->hasAccess());

        $obj->setIp('192.168.1.257');
        $this->assertTrue($obj->hasAccess());

        $obj->setIp('192.168.2.101');
        $this->assertFalse($obj->hasAccess());
    }

    public function testMatchExact()
    {
        Config::inst()->update('IpAccess', 'enabled', true);
        Config::inst()->update('IpAccess', 'allowed_ips', array('192.168.1.101'));

        $obj = new IpAccess('192.168.1.101');
        $this->assertEquals($obj->matchExact(), '192.168.1.101');

        $obj->setIp('192.168.1.102');
        $this->assertEmpty($obj->matchExact());
    }

    public function testMatchCIDR()
    {
        Config::inst()->update('IpAccess', 'enabled', true);
        Config::inst()->update('IpAccess', 'allowed_ips', array('192.168.1.0/24'));

        $obj = new IpAccess('192.168.1.101');

        $this->assertEquals($obj->matchCIDR(), '192.168.1.0/24');

        $obj->setIp('192.168.1.257');
        $this->assertEmpty($obj->matchCIDR());

        $obj->setIp('192.168.2.101');
        $this->assertEmpty($obj->matchCIDR());
    }

    public function testMatchRange()
    {
        Config::inst()->update('IpAccess', 'enabled', true);
        Config::inst()->update('IpAccess', 'allowed_ips', array('192.168.1.100-200'));

        $obj = new IpAccess('192.168.1.101');

        $this->assertEquals($obj->matchRange(), '192.168.1.100-200');

        $obj->setIp('192.168.1.201');
        $this->assertEmpty($obj->matchRange());

        $obj->setIp('192.168.2.201');
        $this->assertEmpty($obj->matchRange());

        $obj->setIp('192.168.1.99');
        $this->assertEmpty($obj->matchRange());
    }

    public function testMatchWildCard()
    {
        Config::inst()->update('IpAccess', 'enabled', true);
        Config::inst()->update('IpAccess', 'allowed_ips', array('192.168.1.*'));

        $obj = new IpAccess('192.168.1.101');

        $this->assertEquals($obj->matchWildCard(), '192.168.1.*');

        $obj->setIp('192.168.2.101');
        $this->assertEmpty($obj->matchWildCard());

        $obj->setIp('190.168.1.101');
        $this->assertEmpty($obj->matchWildCard());

        Config::inst()->remove('IpAccess', 'allowed_ips');
        Config::inst()->update('IpAccess', 'allowed_ips', array('192.168.*'));
        $obj = new IpAccess('192.168.2.2');
        $this->assertEquals($obj->matchWildCard(), '192.168.*');

        Config::inst()->remove('IpAccess', 'allowed_ips');
        Config::inst()->update('IpAccess', 'allowed_ips', array('192.167.*'));
        $this->assertEmpty($obj->matchWildCard());

        Config::inst()->remove('IpAccess', 'allowed_ips');
        Config::inst()->update('IpAccess', 'allowed_ips', array('192.*'));
        $this->assertEquals($obj->matchWildCard(), '192.*');

        Config::inst()->remove('IpAccess', 'allowed_ips');
        Config::inst()->update('IpAccess', 'allowed_ips', array('10.*'));
        $this->assertEmpty($obj->matchWildCard());
    }
}
