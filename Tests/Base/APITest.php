<?php

namespace Ibrows\EasySysBundle\Tests\Base;

use Ibrows\EasySysBundle\API\Contact;

class APITest extends \PHPUnit_Framework_TestCase
{


    public function testContactAPI()
    {

        $api = $this->getContactApi();
        $this->assertTrue(method_exists($api, 'call'));
        $this->assertTrue(method_exists($api, 'show'));
        $this->assertTrue(method_exists($api, 'search'));
        $this->assertTrue(method_exists($api, 'create'));
        $this->assertTrue(method_exists($api, 'update'));
        $this->assertTrue(method_exists($api, 'delete'));


    }

    public function testContactShow()
    {
        $api = $this->getContactApi();
        $this->assertTrue(method_exists($api, 'show'), 'method show dont exists');
        $contact = $api->show(1);
        $this->assertTrue(is_object($contact));
        $this->assertInstanceOf('Ibrows\EasySysBundle\Model\Contact', $contact);
    }

    public function testContactSearch()
    {
        $api = $this->getContactApi();
        $this->assertMethod($api,'search');
        $contacts = $api->search(array('name' => 'gugus'));
        if (is_object($contacts)) {
            $this->assertInstanceOf('\Iterator', $contacts);
        } else {
            $this->assertTrue(is_array($contacts));
        }
        $contact = current($contacts);
        $this->assertInstanceOf('\Iterator', $contact);
    }

    protected function assertMethod($object, $method){
        $this->assertTrue(method_exists($object, $method), "method $method don't exists");
    }

    public function testContactCreate()
    {
        $api = $this->getContactApi();

        $this->assertMethod($api,'createFromArray');
        $contact = $api->createFromArray(array('name' => 'gugus'));
        $this->assertInstanceOf('Ibrows\EasySysBundle\Model\Contact', $contact);

        $this->assertMethod($api,'create');
        $contact = $api->create('myname');
        $this->assertInstanceOf('Ibrows\EasySysBundle\Model\Contact', $contact);

        $this->assertMethod($api,'createFromObject');
        $contact = $api->createFromObject(new Contact());
        $this->assertInstanceOf('Ibrows\EasySysBundle\Model\Contact', $contact);
    }

    public function testContactUpdate()
    {
        $api = $this->getContactApi();

        $this->assertMethod($api,'updateFromArray');
        $contact = $api->updateFromArray(array('name' => 'gugus'));
        $this->assertInstanceOf('Ibrows\EasySysBundle\Model\Contact', $contact);

        $this->assertMethod($api,'update');
        $contact = $api->update('myname');
        $this->assertInstanceOf('Ibrows\EasySysBundle\Model\Contact', $contact);

        $$this->assertMethod($api,'updateFromObject');
        $contact = $api->updateFromObject(new Contact());
        $this->assertInstanceOf('Ibrows\EasySysBundle\Model\Contact', $contact);

    }

    public function testContactDelete()
    {
        $api = $this->getContactApi();
        $this->assertMethod($api,'delete');
        $return = $api->delete(1);
        $this->assertTrue($return);

    }

    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
    }

    protected function getContactApi()
    {
        return new Contact($this->mockConnection());
    }

    protected function mockConnection()
    {
        return $this->getMock('Ibrows\EasySysBundle\Connection\ConnectionInterface');
    }

}
