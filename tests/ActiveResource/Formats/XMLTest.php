<?php

/*
 * This file is part of the php-ActiveResource.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ActiveResource\Formats\XML as XMLFormat;
use ActiveResource\Ext\Inflector;

class XMLTest extends PHPUnit_Framework_TestCase
{
  public function testInfoGetters()
  {
    $format = new XMLFormat;

    $this->assertEquals('application/xml', $format->getMimeType(), 'Correct MIME type');
    $this->assertEquals('xml', $format->getExtension(), 'Correct extension');
  }

  private function toXml(array $data)
  {
    $format = new XMLFormat;

    return $format->encode(array('person' => $data));
  }

  public function testOneLevel()
  {
    $xml = $this->toXml(array('name' => 'David', 'street' => 'Paulina'));

    $this->assertEquals('<person>', substr($xml, 0, 8));
    $this->assertContains('<street>Paulina</street>', $xml);
    $this->assertContains('<name>David</name>', $xml);
  }

  public function testOneLevelWithDash()
  {
    $xml = $this->toXml(array('name' => 'David', 'street_name' => 'Paulina'));

    $this->assertEquals('<person>', substr($xml, 0, 8));
    $this->assertContains('<street-name>Paulina</street-name>', $xml);
    $this->assertContains('<name>David</name>', $xml);
  }

  public function testOneLevelWithTypes()
  {
    $xml = $this->toXml(array('name' => 'David', 'street' => 'Paulina', 'age' => 26, 'age_in_millis' => 820497600000, 'moved_on' => date('c', strtotime('2005-11-15')), 'resident' => false));

    $this->assertEquals('<person>', substr($xml, 0, 8));
    $this->assertContains('<street>Paulina</street>', $xml);
    $this->assertContains('<name>David</name>', $xml);
    $this->assertContains('<age type="integer">26</age>', $xml);
    $this->assertContains('<age-in-millis type="integer">820497600000</age-in-millis>', $xml);
    $this->assertContains('<moved-on type="datetime">2005-11-15T00:00:00+02:00</moved-on>', $xml);
    $this->assertContains('<resident type="boolean">false</resident>', $xml);
  }

  public function testOneLevelWithNulls()
  {
    $xml = $this->toXml(array('name' => 'David', 'street' => 'Paulina', 'age' => null));

    $this->assertEquals('<person>', substr($xml, 0, 8));
    $this->assertContains('<street>Paulina</street>', $xml);
    $this->assertContains('<name>David</name>', $xml);
    $this->assertContains('<age/>', $xml);
  }

  public function testTwoLevels()
  {
    $xml = $this->toXml(array('name' => 'David', 'address' => array('street' => 'Paulina')));

    $this->assertEquals('<person>', substr($xml, 0, 8));
    $this->assertContains("  <address>\n    <street>Paulina</street>\n  </address>", $xml);
    $this->assertContains('<name>David</name>', $xml);
  }

  public function testTwoLevelsWithArray()
  {
    $xml = $this->toXml(array('name' => 'David', 'addresses' => array(array('street' => 'Paulina'), array('street' => 'Evergreen'))));

    $this->assertEquals('<person>', substr($xml, 0, 8));
    $this->assertContains('<name>David</name>', $xml);
    $this->assertContains("  <addresses type=\"array\">\n    <address>", $xml);
    $this->assertContains("    <address>\n      <street>Paulina</street>\n    </address>", $xml);
    $this->assertContains("    <address>\n      <street>Evergreen</street>\n    </address>", $xml);
  }

  public function testThreeLevelsWithArray()
  {
    $xml = $this->toXml(array('name' => 'David', 'addresses' => array(array('streets' => array(array('name' => 'Paulina'), array('name' => 'Evergreen'))))));

    $this->assertContains("  <addresses type=\"array\">\n    <address>\n      <streets type=\"array\">\n        <street>\n          <name>Paulina</name>", $xml);
  }

  public function recordsXmlAndArrayDataProvider()
  {
    return array(
      array(
'<topic>
  <title>The First Topic</title>
  <author-name>David</author-name>
  <id type="integer">1</id>
  <approved type="boolean">true</approved>
  <replies-count type="integer">0</replies-count>
  <replies-close-in type="integer">2592000000</replies-close-in>
  <written-on type="datetime">2003-07-16T00:00:00+03:00</written-on>
  <viewed-at type="datetime">2003-07-16T12:28:00+03:00</viewed-at>
  <author-email-address>david@loudthinking.com</author-email-address>
  <parent-id/>
  <ad-revenue type="float">1.5</ad-revenue>
  <optimum-viewing-angle type="float">135</optimum-viewing-angle>
  <dream-account type="float">' . PHP_INT_MAX . '</dream-account>
  <debt-per-life type="integer">' . (PHP_INT_MAX + 1) . '</debt-per-life>
  <resident>yes</resident>
</topic>'

        ,array(
          'title' => 'The First Topic',
          'author_name' => 'David',
          'id' => 1,
          'approved' => true,
          'replies_count' => 0,
          'replies_close_in' => 2592000000,
          'written_on' => '2003-07-16T00:00:00+03:00',
          'viewed_at' => '2003-07-16T12:28:00+03:00',
          'author_email_address' => 'david@loudthinking.com',
          'parent_id' => null,
          'ad_revenue' => 1.50,
          'optimum_viewing_angle' => 135.0,
          'dream_account' => floatval(PHP_INT_MAX),
          'debt_per_life' => floatval(PHP_INT_MAX + 1),
          'resident' => 'yes'
        )
      ),
      array(
        <<<XML
<topics type="array">
  <topic>
    <title>The First Topic</title>
    <author-name>David</author-name>
    <id type="integer">1</id>
    <approved type="boolean">false</approved>
    <replies-count type="integer">0</replies-count>
    <replies-close-in type="integer">2592000000</replies-close-in>
    <written-on type="datetime">2003-07-16T00:00:00+03:00</written-on>
    <viewed-at type="datetime">2003-07-16T12:28:00+03:00</viewed-at>
    <content>Have a nice day</content>
    <author-email-address>david@loudthinking.com</author-email-address>
    <parent-id/>
  </topic>
  <topic>
    <title>The Second Topic</title>
    <author-name>Jason</author-name>
    <id type="integer">1</id>
    <approved type="boolean">false</approved>
    <replies-count type="integer">0</replies-count>
    <replies-close-in type="integer">2592000000</replies-close-in>
    <written-on type="datetime">2003-07-16T00:00:00+03:00</written-on>
    <viewed-at type="datetime">2003-07-16T12:28:00+03:00</viewed-at>
    <content>Have a nice day</content>
    <author-email-address>david@loudthinking.com</author-email-address>
    <parent-id/>
  </topic>
</topics>
XML
        ,array(
          array(
            'title' => 'The First Topic',
            'author_name' => 'David',
            'id' => 1,
            'approved' => false,
            'replies_count' => 0,
            'replies_close_in' => 2592000000,
            'written_on' => '2003-07-16T00:00:00+03:00',
            'viewed_at' => '2003-07-16T12:28:00+03:00',
            'content' => 'Have a nice day',
            'author_email_address' => 'david@loudthinking.com',
            'parent_id' => null
          ),
          array(
            'title' => 'The Second Topic',
            'author_name' => 'Jason',
            'id' => 1,
            'approved' => false,
            'replies_count' => 0,
            'replies_close_in' => 2592000000,
            'written_on' => '2003-07-16T00:00:00+03:00',
            'viewed_at' => '2003-07-16T12:28:00+03:00',
            'content' => 'Have a nice day',
            'author_email_address' => 'david@loudthinking.com',
            'parent_id' => null
          )
        )
      ),
    );
  }

  /**
   * @dataProvider recordsXmlAndArrayDataProvider
   */
  public function testRecordsXmlToArray($xml, array $data)
  {
    $format = new XMLFormat;
    $root_name = Inflector::isHash($data) ? 'topic' : 'topics';

    $this->assertEquals(array($root_name => $data), $format->decode($xml), 'Correctly decoded');
  }

  /**
   * @dataProvider recordsXmlAndArrayDataProvider
   */
  public function testRecordsArrayToXml($xml, array $data)
  {
    $format = new XMLFormat;
    $root_name = Inflector::isHash($data) ? 'topic' : 'topics';

    $this->assertEquals($xml, $format->encode(array($root_name => $data)), 'Correctly encoded');
  }
}
