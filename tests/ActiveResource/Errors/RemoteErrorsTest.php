<?php

require_once __DIR__ . "/../TodoList.php";

use ActiveResource\Errors\RemoteErrors;

class RemoteErrorsTest extends PHPUnit_Framework_TestCase
{
  private $errors;

  public function setUp()
  {
    $this->errors = new RemoteErrors('TodoList');
  }

  /**
  * @dataProvider setDataProvider
  */
  public function testSet($attribute, $value, $result, $fullMessages)
  {
    $this->errors->set($attribute, $value);
    $this->assertEquals($result, $this->errors->get($attribute));
    $this->assertEquals($fullMessages, $this->errors->getFullMessages());
    $this->assertFalse($this->errors->isEmpty());
  }

  public function setDataProvider()
  {
    return array(
      array('body', 'cannot be blank', array('cannot be blank'),
        array('Body cannot be blank')),
      array('user_id', 'is not a number', array('is not a number'),
        array('User is not a number')),
    );
  }

  /**
   * @dataProvider addDataProvider
   */
  public function testAdd($count, array $keys, array $messages, array $fullMessages)
  {
    foreach($messages as $attribute => $message)
    {
      $this->errors->add($message[0], $message[1]);
    }
    $this->assertEquals($count, $this->errors->getCount());
    $this->assertEquals($keys, $this->errors->getKeys());
    $this->assertEquals($fullMessages, $this->errors->getFullMessages());
  }

  public function addDataProvider()
  {
    return array(
      array(
        4,
        array('body', 'user_id'),
        array(
          array('body', 'cannot be blank'),
          array('body', 'must be specified'),
          array('user_id', 'is not a number'),
          array('user_id', 'less than zero'),
        ),
        array(
          'Body cannot be blank',
          'Body must be specified',
          'User is not a number',
          'User less than zero',
        ),
      ),
    );
  }

  /**
   * @dataProvider loadFromXMLDataProvider
   */
  public function testLoadFromXML($count, $result, $xml)
  {
    $this->errors->loadFromXML($xml);
    //fwrite(STDOUT, var_export($this->errors->getFullMessages(), true));
    $this->assertEquals($count, $this->errors->getCount());
    $this->assertEquals($result, $this->errors->getFullMessages());
  }

  public function loadFromXMLDataProvider() {
    return array(
      array(
        2, 
        array('Body cannot be blank', 'User is not a number'),
        <<<XML
<errors>
    <error>Body cannot be blank</error>
    <error>User is not a number</error>
</errors>
XML
      ),
      array(0, array(), "<errors></errors>"),
      array(0, array(), "<alien></alien>"),
      array(0, array(), "notxml"),
    );
  }

}
