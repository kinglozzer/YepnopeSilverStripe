<?php

class YepnopeTestObjectTest extends SapphireTest
{

    protected $test;

    public function setUp()
    {
        $this->test = new YepnopeTestObject(
            'id-1',
            'test-1',
            'yep-1',
            'nope-1',
            'load-1',
            'callback-1',
            'complete-1'
        );
    }
    
    public function testGetters()
    {
        $this->assertEquals('id-1', $this->test->getID());
        $this->assertEquals('test-1', $this->test->getTest());
        $this->assertEquals('yep-1', $this->test->getYep());
        $this->assertEquals('nope-1', $this->test->getNope());
        $this->assertEquals('load-1', $this->test->getLoad());
        $this->assertEquals('callback-1', $this->test->getCallback());
        $this->assertEquals('complete-1', $this->test->getComplete());
    }

    public function testSetters()
    {
        $this->test->setID('id-2');
        $this->test->setTest('test-2');
        $this->test->setYep('yep-2');
        $this->test->setNope('nope-2');
        $this->test->setLoad('load-2');
        $this->test->setCallback('callback-2');
        $this->test->setComplete('complete-2');

        $this->assertEquals('id-2', $this->test->getID());
        $this->assertEquals('test-2', $this->test->getTest());
        $this->assertEquals('yep-2', $this->test->getYep());
        $this->assertEquals('nope-2', $this->test->getNope());
        $this->assertEquals('load-2', $this->test->getLoad());
        $this->assertEquals('callback-2', $this->test->getCallback());
        $this->assertEquals('complete-2', $this->test->getComplete());
    }
}
