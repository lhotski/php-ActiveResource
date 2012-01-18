<?php

/*
 * This file is part of the php-ActiveResource.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ActiveResource\Schemas\AttrsSchema;
use ActiveResource\Ext\Inflector;

class AttrsSchemaTest extends PHPUnit_Framework_TestCase
{
  public function settedAttributesDataProvider()
  {
    return array(
      array(
        array(
          'name'        => 'string',
          'is_man'      => 'boolean',
          'age'         => 'integer',
          'money'       => 'float',
          'knowledges'  => 'array'
        ),
      )
    );
  }

  public function testUnsettedAttributes()
  {
    $schema = new AttrsSchema;

    $this->assertFalse($schema->isDefined());
    $this->assertTrue($schema->hasAttribute('random_attribute'));
    $this->assertEquals('string', $schema->getAttributeType('random_attribute'));
    $this->assertTrue($schema->isAttributeType('random_attribute', 'string'));
    $this->assertFalse($schema->isAttributeType('random_attribute', 'boolean'));
  }

  /**
   * @dataProvider settedAttributesDataProvider
   */
  public function testSetAttributes(array $attrs)
  {
    $schema = new AttrsSchema($attrs);

    $this->assertTrue($schema->isDefined());

    foreach ($attrs as $name => $type)
    {
      $this->assertTrue($schema->hasAttribute($name));
      $this->assertTrue($schema->isAttributeType($name, $type));
      $this->assertEquals($type, $schema->getAttributeType($name));
      $this->assertFalse($schema->isAttributeType($name, 'unnamed'));
    }
  }

  /**
   * @dataProvider settedAttributesDataProvider
   */
  public function testSetAttribute(array $attrs)
  {
    $schema = new AttrsSchema;

    $this->assertFalse($schema->isDefined());

    foreach ($attrs as $name => $type)
    {
      $schema->setAttribute($name, $type);
    }

    $this->assertTrue($schema->isDefined());

    foreach ($attrs as $name => $type)
    {
      $this->assertTrue($schema->hasAttribute($name));
      $this->assertTrue($schema->isAttributeType($name, $type));
      $this->assertEquals($type, $schema->getAttributeType($name));
      $this->assertFalse($schema->isAttributeType($name, 'unnamed'));
    }
  }

  public function definedSetGetDataProvider()
  {
    $schema = array(
      'name'        => 'string',
      'is_man'      => 'boolean',
      'age'         => 'integer',
      'money'       => 'float',
      'knowledges'  => 'array'
    );

    return array(
      array($schema, 'name', 'Konstantin', 'Konstantin'),
      array($schema, 'is_man', 'true', true),
      array($schema, 'is_man', 'false', false),
      array($schema, 'is_man', '0', false),
      array($schema, 'age', '12asd', 12),
      array($schema, 'age', 12.5, 12),
      array($schema, 'age', 'true', 0),
      array($schema, 'money', '12.5', 12.5),
      array($schema, 'money', 3, 3),
      array($schema, 'knowledges', array('php' => 5, 'java' => 4), array('php' => 5, 'java' => 4))
    );
  }

  /**
   * @dataProvider definedSetGetDataProvider
   */
  public function testDefinedSet(array $attrs, $name, $value, $expected_value)
  {
    $schema = new AttrsSchema($attrs);

    $schema->set($name, $value);
    $this->assertEquals($expected_value, $schema->get($name));
  }

  /**
   * @dataProvider definedSetGetDataProvider
   */
  public function testDefinedGetValues(array $attrs, $name, $value, $expected_value)
  {
    $schema = new AttrsSchema($attrs);

    $schema->set($name, $value);

    $values = array();
    foreach (array_keys($attrs) as $attr)
    {
      $values[$attr] = $name === $attr ? $expected_value : null;
    }

    $this->assertEquals($values, $schema->getValues());
  }

  public function undefinedSetGetDataProvider()
  {
    return array(
      array('name', 'Konstantin', 'Konstantin'),
      array('is_man', 'true', 'true'),
      array('is_man', 'false', 'false'),
      array('is_man', '0', '0'),
      array('age', '12asd', '12asd'),
      array('age', 12.5, 12.5),
      array('age', true, true),
      array('money', '12.5', '12.5'),
      array('money', 3, 3),
      array('id', 1987010, 1987010),
      array('knowledges', array('php' => 5, 'java' => 4), array('php' => 5, 'java' => 4)),
    );
  }

  /**
   * @dataProvider undefinedSetGetDataProvider
   */
  public function testUndefinedSet($name, $value, $expected_value)
  {
    $schema = new AttrsSchema;

    $schema->set($name, $value);
    $this->assertEquals($expected_value, $schema->get($name));
  }

  /**
   * @dataProvider undefinedSetGetDataProvider
   */
  public function testUndefinedGetValues($name, $value, $expected_value)
  {
    $schema = new AttrsSchema;

    $schema->set($name, $value);
    $this->assertEquals(array($name => $expected_value), $schema->getValues());
  }
}
