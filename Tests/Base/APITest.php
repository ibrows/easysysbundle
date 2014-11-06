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
        $this->assertTrue(method_exists($api, 'search'), 'search update dont exists');
        $contacts = $api->search(array('name' => 'gugus'));
        if (is_object($contacts)) {
            $this->assertInstanceOf('\Iterator', $contacts);
        } else {
            $this->assertTrue(is_array($contacts));
        }
        $contact = current($contacts);
        $this->assertInstanceOf('\Iterator', $contact);
    }


    public function testContactCreate()
    {
        $api = $this->getContactApi();
        $this->assertTrue(method_exists($api, 'createFromArray'), 'create createFromArray dont exists');
        $contact = $api->createFromArray(array('name' => 'gugus'));
        $this->assertInstanceOf('Ibrows\EasySysBundle\Model\Contact', $contact);

        $this->assertTrue(method_exists($api, 'create'), 'create create dont exists');
        $contact = $api->create('myname');
        $this->assertInstanceOf('Ibrows\EasySysBundle\Model\Contact', $contact);

        $this->assertTrue(method_exists($api, 'createFromObject'), 'create createFromObject dont exists');
        $contact = $api->createFromObject(new Contact());
        $this->assertInstanceOf('Ibrows\EasySysBundle\Model\Contact', $contact);
    }

    public function testContactUpdate()
    {
        $api = $this->getContactApi();
        $this->assertTrue(method_exists($api, 'updateFromArray'), 'update updateFromArray dont exists');
        $contact = $api->updateFromArray(array('name' => 'gugus'));
        $this->assertInstanceOf('Ibrows\EasySysBundle\Model\Contact', $contact);

        $this->assertTrue(method_exists($api, 'update'), 'create update dont exists');
        $contact = $api->update('myname');
        $this->assertInstanceOf('Ibrows\EasySysBundle\Model\Contact', $contact);

        $this->assertTrue(method_exists($api, 'updateFromObject'), 'create updateFromObject dont exists');
        $contact = $api->updateFromObject(new Contact());
        $this->assertInstanceOf('Ibrows\EasySysBundle\Model\Contact', $contact);

    }

    public function testContactDelete()
    {


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
