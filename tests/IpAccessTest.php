<?php

class IpAccessTest extends SapphireTest
{
    
    protected $allowedIps = array(
        '192.168.1.101',
        '192.168.1.100-200',
        '192.168.1.0/24',
        '192.168.1.*'
    );
    
    public function testHasAccess()
    {
        $obj = new IpAccess('192.168.1.101');
        $obj->allowedIps = array();
        $this->assertEquals($obj->hasAccess(), 'allowed');
        
        $obj->allowedIps = $this->allowedIps;
        
        $obj->setIp('192.168.1.101');
        $this->assertEquals($obj->hasAccess(), '192.168.1.101');
        
        $obj->setIp('192.168.1.102');
        $this->assertEquals($obj->hasAccess(), '192.168.1.100-200');
        
        $obj->setIp('192.168.1.201');
        $this->assertEquals($obj->hasAccess(), '192.168.1.0/24');
        
        $obj->setIp('192.168.1.257');
        $this->assertEquals($obj->hasAccess(), '192.168.1.*');
        
        $obj->setIp('192.168.2.101');
        $this->assertEmpty($obj->hasAccess());
    }
    
    public function testMatchExact()
    {
        $obj = new IpAccess('192.168.1.101');
        $obj->allowedIps = array('192.168.1.101');
        $this->assertEquals($obj->matchExact(), '192.168.1.101');
        
        $obj->setIp('192.168.1.102');
        $this->assertEmpty($obj->matchExact());
    }
    
    public function testMatchCIDR()
    {
        $obj = new IpAccess('192.168.1.101');
        $obj->allowedIps = array('192.168.1.0/24');
        $this->assertEquals($obj->matchCIDR(), '192.168.1.0/24');
        
        $obj->setIp('192.168.1.257');
        $this->assertEmpty($obj->matchCIDR());
        
        $obj->setIp('192.168.2.101');
        $this->assertEmpty($obj->matchCIDR());
    }
    
    public function testMatchRange()
    {
        $obj = new IpAccess('192.168.1.101');
        $obj->allowedIps = array('192.168.1.100-200');
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
        $obj = new IpAccess('192.168.1.101');
        $obj->allowedIps = array('192.168.1.*');
        $this->assertEquals($obj->matchWildCard(), '192.168.1.*');
        
        $obj->setIp('192.168.2.101');
        $this->assertEmpty($obj->matchWildCard());
        
        $obj->setIp('190.168.1.101');
        $this->assertEmpty($obj->matchWildCard());

        $obj = new IpAccess('192.168.2.2');
        $obj->allowedIps = array('192.168.*');
        $this->assertEquals($obj->matchWildCard(), '192.168.*');

        $obj->allowedIps = array('192.167.*');
        $this->assertNull($obj->matchWildCard());

        $obj->allowedIps = array('192.*');
        $this->assertEquals($obj->matchWildCard(), '192.*');

        $obj->allowedIps = array('10.*');
        $this->assertNull($obj->matchWildCard());
    }
}
