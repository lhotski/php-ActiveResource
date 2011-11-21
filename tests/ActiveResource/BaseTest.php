<?php

/*
 * This file is part of the php-ActiveResource.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/Connections/HTTP_Request2_Adapter_UrlMock.php';

use ActiveResource\Connections\HTTPR2Connection as Connection;
use HTTP_Request2_Adapter_UrlMock as MockAdapter;

define('BASE_URL', 'https://some.rest.site.com/base/path');

class TodoList extends ActiveResource\Base{}
class Person extends ActiveResource\Base{}

class BaseTest extends PHPUnit_Framework_TestCase
{
  private function getConnection()
  {
    return new Connection(BASE_URL);
  }

  private function getMockedConnection($method, $url, $body, $response)
  {
    $connection = $this->getConnection();
    $adapter = new MockAdapter;
    $adapter->addResponse($method, $url, $body, $response);
    $connection->setAdapter($adapter);

    return $connection;
  }

  public function constructorDataProvider()
  {
    return array(
      array(4, 10, 'David'),
      array(7, 24, 'John'),
      array(5, 33, 'ever'),
      array(101, 51, 'Smith Evans'),
    );
  }

  public function testElementAndCollectionName()
  {
    $this->assertEquals('todo_list', TodoList::getElementName());
    $this->assertEquals('todo_lists', TodoList::getCollectionName());
  }

  /**
   * @dataProvider constructorDataProvider
   */
  public function testConstruct($id, $user_id, $name)
  {
    $todo = new TodoList(array('id' => $id, 'user_id' => $user_id, 'name' => $name), $this->getConnection());

    $this->assertEquals($id, $todo->getId());
    $this->assertEquals($user_id, $todo->user_id);
    $this->assertEquals($name, $todo->name);
  }

  /**
   * @dataProvider constructorDataProvider
   */
  public function testInit($id, $user_id, $name)
  {
    $todo = TodoList::init(array('id' => $id, 'user_id' => $user_id, 'name' => $name), $this->getConnection());

    $this->assertEquals($id, $todo->getId());
    $this->assertEquals($user_id, $todo->user_id);
    $this->assertEquals($name, $todo->name);
  }

  /**
   * @dataProvider constructorDataProvider
   */
  public function testLoad($id, $user_id, $name)
  {
    $todo = new TodoList(array(), $this->getConnection());
    $todo->load(array('id' => $id, 'user_id' => $user_id, 'name' => $name));

    $this->assertEquals($id, $todo->getId());
    $this->assertEquals($user_id, $todo->user_id);
    $this->assertEquals($name, $todo->name);
  }

  public function isExistsDataProvider()
  {
    return array(
      array(
         <<<RESPONSE
HTTP/1.1 200 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 200

RESPONSE
        ,23
        ,array()
        ,array()
        ,BASE_URL . '/todo_lists/23.xml'
        ,true
      ),
      array(
         <<<RESPONSE
HTTP/1.1 410
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 410

RESPONSE
        ,2
        ,array('project_id' => 4)
        ,array('name' => 'Ivan', 'age' => 21)
        ,BASE_URL . '/projects/4/todo_lists/2.xml?name=Ivan&age=21'
        ,false
      ),
      array(
         <<<RESPONSE
HTTP/1.1 404
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 404

RESPONSE
        ,102
        ,array('project_id' => 4, 'person_id' => 101)
        ,array('name' => 'Ivan', 'age' => 21)
        ,BASE_URL . '/projects/4/people/101/todo_lists/102.xml?name=Ivan&age=21'
        ,false
      ),
    );
  }

  /**
   * @dataProvider isExistsDataProvider
   */
  public function testIsExists($response, $id, array $prefix_options, array $query_options, $url, $answer)
  {
    $connection = $this->getMockedConnection('head', $url, null, $response);
    
    $this->assertEquals($answer, TodoList::isExists($id, $prefix_options, $query_options, $connection));
  }

  public function buildDataProvider()
  {
    return array(
      array(
        <<<RESPONSE
HTTP/1.1 202 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 202

<?xml version="1.0" encoding="UTF-8"?>
<todo-list>
  <id type="integer">5</id>
  <user-id type="integer">1</user-id>
  <name>LIST</name>
</todo-list>
RESPONSE
        ,5
        ,1
        ,'LIST'
        ,array()
        ,array()
        ,BASE_URL . '/todo_lists/new.xml'
      ),
      array(
        <<<RESPONSE
HTTP/1.1 200 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 200

<?xml version="1.0" encoding="UTF-8"?>
<todo-list>
  <id>1</id>
  <user-id>2</user-id>
  <name>ToDo list</name>
</todo-list>
RESPONSE
        ,1
        ,2
        ,'ToDo list'
        ,array('project_id' => 4, 'person_id' => 101)
        ,array('name' => 'Ivan', 'age' => 21)
        ,BASE_URL . '/projects/4/people/101/todo_lists/new.xml?name=Ivan&age=21'
      ),
    );
  }

  /**
   * @dataProvider buildDataProvider
   */
  public function testBuild($response, $id, $user_id, $name, array $prefix_options, array $query_options, $url)
  {
    $connection = $this->getMockedConnection('get', $url, null, $response);

    $list = TodoList::build($prefix_options, $query_options, $connection);

    $this->assertEquals($id, $list->getId());
    $this->assertEquals($user_id, $list->user_id);
    $this->assertEquals($name, $list->name);
  }

  /**
   * @dataProvider buildDataProvider
   */
  public function testIsNewAndIsPersisted($response, $id, $user_id, $name, array $prefix_options, array $query_options, $url)
  {
    $connection = $this->getMockedConnection('get', $url, null, $response);

    $list = TodoList::build($prefix_options, $query_options, $connection);

    $this->assertTrue($list->isPersisted());
    $this->assertFalse($list->isNew());

    $list = new TodoList(array('user_id' => $user_id, 'name' => $name), $connection);

    $this->assertFalse($list->isPersisted());
    $this->assertTrue($list->isNew());
  }

  public function createDataProvider()
  {
    return array(
      array(
        <<<RESPONSE
HTTP/1.1 201 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 201
Location: {BASE_URL}/todo_lists/5.xml
RESPONSE
        ,<<<BODY
<todo-list>
  <user-id type="integer">41</user-id>
  <name>everzet</name>
  <is-bool type="boolean">true</is-bool>
</todo-list>
BODY
        ,BASE_URL . '/todo_lists.xml'
        ,41
        ,'everzet'
        ,true
        ,5
        ,true
      ),
      array(
        <<<RESPONSE
HTTP/1.1 202 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 202
Location: {BASE_URL}/todo_lists/5.xml
RESPONSE
        ,<<<BODY
<todo-list>
  <user-id type="integer">55</user-id>
  <name>Ivan</name>
  <is-bool type="boolean">false</is-bool>
</todo-list>
BODY
        ,BASE_URL . '/todo_lists.xml'
        ,55
        ,'Ivan'
        ,false
        ,null
        ,false
      ),
    );
  }

  /**
   * @dataProvider createDataProvider
   */
  public function testCreate($response, $body, $url, $user_id, $name, $is_bool, $id, $answer)
  {
    $connection = $this->getMockedConnection('post', $url, $body, $response);

    $list = new TodoList(array('user_id' => $user_id, 'name' => $name, 'is_bool' => $is_bool), $connection);

    $this->assertEquals($answer, $list->save());
    $this->assertEquals($id, $list->getId());
  }

  public function updateDataProvider()
  {
    return array(
      array(
        <<<RESPONSE
HTTP/1.1 204 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 204
Location: {BASE_URL}/todo_lists/5.xml
RESPONSE
        ,<<<BODY
<todo-list>
  <user-id type="integer">41</user-id>
  <name>everzet</name>
  <is-bool type="boolean">true</is-bool>
</todo-list>
BODY
        ,BASE_URL . '/todo_lists/5.xml'
        ,41
        ,'everzet'
        ,true
        ,5
        ,true
      ),
      array(
        <<<RESPONSE
HTTP/1.1 202 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 202
Location: {BASE_URL}/todo_lists/5.xml
RESPONSE
        ,<<<BODY
<todo-list>
  <user-id type="integer">55</user-id>
  <name>Ivan</name>
  <is-bool type="boolean">false</is-bool>
</todo-list>
BODY
        ,BASE_URL . '/todo_lists/10.xml'
        ,55
        ,'Ivan'
        ,false
        ,10
        ,false
      ),
    );
  }

  /**
   * @dataProvider updateDataProvider
   */
  public function testUpdate($response, $body, $url, $user_id, $name, $is_bool, $id, $answer)
  {
    $connection = $this->getMockedConnection('put', $url, $body, $response);

    $list = new TodoList(array('id' => $id, 'user_id' => $user_id, 'name' => $name, 'is_bool' => $is_bool), $connection);

    $this->assertEquals($answer, $list->save());
    $this->assertEquals($id, $list->getId());
  }

  public function destroyDataProvider()
  {
    return array(
      array(
        <<<RESPONSE
HTTP/1.1 200 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 200
RESPONSE
        ,BASE_URL . '/todo_lists/5.xml'
        ,5
        ,true
      ),
      array(
        <<<RESPONSE
HTTP/1.1 202 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 202
RESPONSE
        ,BASE_URL . '/todo_lists/10.xml'
        ,10
        ,false
      ),
    );
  }

  /**
   * @dataProvider destroyDataProvider
   */
  public function testDestroy($response, $url, $id, $answer)
  {
    $connection = $this->getMockedConnection('delete', $url, null, $response);
    $list = new TodoList(array('id' => $id), $connection);

    $this->assertEquals($answer, $list->destroy());
  }

  public function findDataProvider()
  {
    $connection = $this->getConnection();
    $connection->setAdapter(new MockAdapter);

    $person_response = <<<RESPONSE
HTTP/1.1 200 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 200

<person>
  <id type="integer">5</id>
  <name>Marry</name>
</person>
RESPONSE;
    $people_response = <<<RESPONSE
HTTP/1.1 200 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 200

<people type="array">
  <person>
    <id type="integer">12</id>
    <name>John</name>
  </person>
  <person>
    <id type="integer">5</id>
    <name>Marry</name>
  </person>
  <person>
    <id type="integer">104</id>
    <name>David</name>
  </person>
</people>
RESPONSE;
    $managers_response = <<<RESPONSE
HTTP/1.1 200 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 200

<people type="array">
  <person>
    <id type="integer">104</id>
    <name>David</name>
  </person>
  <person>
    <id type="integer">5</id>
    <name>Marry</name>
  </person>
</people>
RESPONSE;


    return array(
      array(
         $person_response
        ,array(1)
        ,BASE_URL . '/people/1.xml'
        ,new Person(array('id' => 5, 'name' => 'Marry'), $connection)
      ),
      array(
         $person_response
        ,1
        ,BASE_URL . '/people/1.xml'
        ,new Person(array('id' => 5, 'name' => 'Marry'), $connection)
      ),
      array(
         $people_response
        ,array('all')
        ,BASE_URL . '/people.xml'
        ,array(
          new Person(array('id' => 12, 'name' => 'John'), $connection),
          new Person(array('id' => 5, 'name' => 'Marry'), $connection),
          new Person(array('id' => 104, 'name' => 'David'), $connection)
        )
      ),
      array(
         $people_response
        ,'all'
        ,BASE_URL . '/people.xml'
        ,array(
          new Person(array('id' => 12, 'name' => 'John'), $connection),
          new Person(array('id' => 5, 'name' => 'Marry'), $connection),
          new Person(array('id' => 104, 'name' => 'David'), $connection)
        )
      ),
      array(
         $managers_response
        ,array('all', 'params' => array('title' => 'CEO'))
        ,BASE_URL . '/people.xml?title=CEO'
        ,array(
          new Person(array('id' => 104, 'name' => 'David'), $connection),
          new Person(array('id' => 5, 'name' => 'Marry'), $connection)
        )
      ),
      array(
         $managers_response
        ,array('first', 'from' => 'managers')
        ,BASE_URL . '/people/managers.xml'
        ,new Person(array('id' => 104, 'name' => 'David'), $connection),
      ),
      array(
         $managers_response
        ,array('last', 'from' => 'managers')
        ,BASE_URL . '/people/managers.xml'
        ,new Person(array('id' => 5, 'name' => 'Marry'), $connection),
      ),
      array(
         $people_response
        ,array('all', 'from' => '/companies/1/people.xml')
        ,BASE_URL . '/companies/1/people.xml'
        ,array(
          new Person(array('id' => 12, 'name' => 'John'), $connection),
          new Person(array('id' => 5, 'name' => 'Marry'), $connection),
          new Person(array('id' => 104, 'name' => 'David'), $connection)
        )
      ),
      array(
         $person_response
        ,array('one', 'from' => 'leader')
        ,BASE_URL . '/people/leader.xml'
        ,new Person(array('id' => 5, 'name' => 'Marry'), $connection)
      ),
      array(
         $managers_response
        ,array('all', 'from' => 'developers', 'params' => array('language' => 'php'))
        ,BASE_URL . '/people/developers.xml?language=php'
        ,array(
          new Person(array('id' => 104, 'name' => 'David'), $connection),
          new Person(array('id' => 5, 'name' => 'Marry'), $connection)
        )
      ),
      array(
         $person_response
        ,array('one', 'from' => '/companies/1/manager.xml')
        ,BASE_URL . '/companies/1/manager.xml'
        ,new Person(array('id' => 5, 'name' => 'Marry'), $connection)
      ),
      array(
         $person_response
        ,array(1, 'params' => array('project_id' => 2))
        ,BASE_URL . '/projects/2/people/1.xml'
        ,new Person(array('id' => 5, 'name' => 'Marry'), $connection)
      )
    );
  }

  /**
   * @dataProvider findDataProvider
   */
  public function testFind($response, $criteria, $url, $etalon_data)
  {
    $connection = $this->getMockedConnection('get', $url, null, $response);
    $response_data = Person::find($criteria, $connection);

    $this->assertEquals($etalon_data, $response_data);
  }

  public function collectionGetDataProvider()
  {
    return array(
      array(
        <<<RESPONSE
HTTP/1.1 200 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 200

<people type="array">
  <person>
    <id type="integer">15</id>
    <name>David</name>
  </person>
  <person>
    <id type="integer">22</id>
    <name>John</name>
  </person>
</people>
RESPONSE
        ,<<<BODY
<people type="array">
  <person>
    <id type="integer">15</id>
    <name>David</name>
  </person>
  <person>
    <id type="integer">22</id>
    <name>John</name>
  </person>
</people>
BODY
        ,'positions'
        ,array()
        ,BASE_URL . '/people/positions.xml'
        ,array(array('id' => 15, 'name' => 'David'), array('id' => 22, 'name' => 'John'))
      ),
      array(
        <<<RESPONSE
HTTP/1.1 200 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 200

<person>
  <id type="integer">22</id>
  <name>John</name>
</person>
RESPONSE
        ,<<<BODY
<person>
  <id type="integer">22</id>
  <name>John</name>
</person>
BODY
        ,'managers'
        ,array('title' => 'CEO', 'project_id' => 22)
        ,BASE_URL . '/projects/22/people/managers.xml?title=CEO'
        ,array('id' => 22, 'name' => 'John')
      ),
    );
  }

  /**
   * @dataProvider collectionGetDataProvider
   */
  public function testCollectionGet($response, $body, $method, $params, $url, $etalon_data)
  {
    $connection = $this->getMockedConnection('get', $url, $body, $response);
    $response_data = Person::collectionGet($method, $params, $connection);

    $this->assertEquals($etalon_data, $response_data);
  }

  public function elementGetDataProvider()
  {
    return array(
      array(
        <<<RESPONSE
HTTP/1.1 200 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 200

<people type="array">
  <person>
    <id type="integer">15</id>
    <name>David</name>
  </person>
  <person>
    <id type="integer">22</id>
    <name>John</name>
  </person>
</people>
RESPONSE
        ,<<<BODY
<people type="array">
  <person>
    <id type="integer">15</id>
    <name>David</name>
  </person>
  <person>
    <id type="integer">22</id>
    <name>John</name>
  </person>
</people>
BODY
        ,'positions'
        ,array()
        ,BASE_URL . '/people/11/positions.xml'
        ,array('id' => 11)
        ,array(array('id' => 15, 'name' => 'David'), array('id' => 22, 'name' => 'John'))
      ),
      array(
        <<<RESPONSE
HTTP/1.1 200 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 200

<person>
  <id type="integer">22</id>
  <name>John</name>
</person>
RESPONSE
        ,<<<BODY
<person>
  <id type="integer">22</id>
  <name>John</name>
</person>
BODY
        ,'managers'
        ,array('title' => 'CEO', 'project_id' => 22)
        ,BASE_URL . '/projects/22/people/new/managers.xml?title=CEO'
        ,array()
        ,array('id' => 22, 'name' => 'John')
      ),
    );
  }

  /**
   * @dataProvider elementGetDataProvider
   */
  public function testElementGet($response, $body, $method, $params, $url, array $cur_data, array $etalon_data)
  {
    $connection = $this->getMockedConnection('get', $url, $body, $response);

    $person = new Person($cur_data, $connection);
    $response_data = $person->elementGet($method, $params);

    $this->assertEquals($etalon_data, $response_data);
  }

  public function elementPostAndPutDataProvider()
  {
    return array(
      array(
        <<<RESPONSE
HTTP/1.1 200 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 200

RESPONSE
        ,<<<BODY
<request type="array">
  <person>
    <id type="integer">15</id>
    <name>David</name>
  </person>
  <person>
    <id type="integer">22</id>
    <name>John</name>
  </person>
</request>
BODY
        ,BASE_URL . '/people/3/poll.xml?filter=*&sex=man'
        ,3
        ,'poll'
        ,array('filter' => '*', 'sex' => 'man')
        ,array(array('id' => 15, 'name' => 'David'), array('id' => 22, 'name' => 'John'))
        ,200
      ),
      array(
        <<<RESPONSE
HTTP/1.1 201 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 201

RESPONSE
        ,<<<BODY
<request type="array">
  <person>
    <id type="integer">15</id>
    <name>David</name>
  </person>
  <person>
    <id type="integer">22</id>
    <name>John</name>
  </person>
</request>
BODY
        ,BASE_URL . '/projects/22/people/25/register.xml?title=CEO'
        ,25
        ,'register'
        ,array('project_id' => 22, 'title' => 'CEO')
        ,array(array('id' => 15, 'name' => 'David'), array('id' => 22, 'name' => 'John'))
        ,201
      ),
    );
  }

  /**
   * @dataProvider elementPostAndPutDataProvider 
   */
  public function testElementPost($response, $sent_body, $url, $id, $method, array $params, array $data, $return)
  {
    $connection = $this->getMockedConnection('post', $url, $sent_body, $response);
    $connection->getAdapter()->addResponse('post', $url, $sent_body, $response);

    $person = new Person(array('id' => $id), $connection);

    $this->assertEquals((201 === $return), $person->elementPost($method, $params, $data));
  }

  /**
   * @dataProvider elementPostAndPutDataProvider 
   */
  public function testElementPut($response, $sent_body, $url, $id, $method, array $params, array $data, $return)
  {
    $connection = $this->getMockedConnection('put', $url, $sent_body, $response);
    $connection->getAdapter()->addResponse('put', $url, $sent_body, $response);

    $person = new Person(array('id' => $id), $connection);

    $this->assertEquals((200 === $return), $person->elementPut($method, $params, $data));
  }

  public function collectionPostAndPutDataProvider()
  {
    return array(
      array(
        <<<RESPONSE
HTTP/1.1 204 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 204

RESPONSE
        ,<<<BODY
<request type="array">
  <person>
    <id type="integer">15</id>
    <name>David</name>
  </person>
  <person>
    <id type="integer">22</id>
    <name>John</name>
  </person>
</request>
BODY
        ,BASE_URL . '/people/poll.xml?filter=*&sex=man'
        ,'poll'
        ,array('filter' => '*', 'sex' => 'man')
        ,array(array('id' => 15, 'name' => 'David'), array('id' => 22, 'name' => 'John'))
        ,204
      ),
      array(
        <<<RESPONSE
HTTP/1.1 201 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 201

RESPONSE
        ,<<<BODY
<request type="array">
  <person>
    <id type="integer">15</id>
    <name>David</name>
  </person>
  <person>
    <id type="integer">22</id>
    <name>John</name>
  </person>
</request>
BODY
        ,BASE_URL . '/projects/22/people/register.xml?title=CEO'
        ,'register'
        ,array('project_id' => 22, 'title' => 'CEO')
        ,array(array('id' => 15, 'name' => 'David'), array('id' => 22, 'name' => 'John'))
        ,201
      ),
    );
  }

  /**
   * @dataProvider collectionPostAndPutDataProvider 
   */
  public function testCollectionPost($response, $sent_body, $url, $method, array $params, array $data, $return)
  {
    $connection = $this->getMockedConnection('post', $url, $sent_body, $response);

    $this->assertEquals((201 === $return), Person::collectionPost($method, $params, $data, $connection));
  }

  /**
   * @dataProvider collectionPostAndPutDataProvider 
   */
  public function testCollectionPut($response, $sent_body, $url, $method, array $params, array $data, $return)
  {
    $connection = $this->getMockedConnection('put', $url, $sent_body, $response);

    $this->assertEquals((204 === $return), Person::collectionPut($method, $params, $data, $connection));
  }

  public function elementDeleteAndHeadDataProvider()
  {
    return array(
      array(
        <<<RESPONSE
HTTP/1.1 200 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 200

RESPONSE
        ,BASE_URL . '/people/3/fire.xml?filter=*&sex=man'
        ,3
        ,'fire'
        ,array('filter' => '*', 'sex' => 'man')
        ,true
      ),
      array(
        <<<RESPONSE
HTTP/1.1 201 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 201

RESPONSE
        ,BASE_URL . '/projects/22/people/25/delete.xml?title=CEO'
        ,25
        ,'delete'
        ,array('project_id' => 22, 'title' => 'CEO')
        ,false
      ),
    );
  }

  /**
   * @dataProvider elementDeleteAndHeadDataProvider 
   */
  public function testElementDelete($response, $url, $id, $method, array $params, $return)
  {
    $connection = $this->getMockedConnection('delete', $url, null, $response);
    $connection->getAdapter()->addResponse('delete', $url, null, $response);

    $person = new Person(array('id' => $id), $connection);

    $this->assertEquals($return, $person->elementDelete($method, $params));
  }

  /**
   * @dataProvider elementDeleteAndHeadDataProvider 
   */
  public function testElementHead($response, $url, $id, $method, array $params, $return)
  {
    $connection = $this->getMockedConnection('head', $url, null, $response);
    $connection->getAdapter()->addResponse('head', $url, null, $response);

    $person = new Person(array('id' => $id), $connection);

    $this->assertEquals($return, $person->elementHead($method, $params));
  }

  public function collectionDeleteAndHeadDataProvider()
  {
    return array(
      array(
        <<<RESPONSE
HTTP/1.1 200 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 200

RESPONSE
        ,BASE_URL . '/people/fire.xml?filter=*&sex=man'
        ,'fire'
        ,array('filter' => '*', 'sex' => 'man')
        ,true
      ),
      array(
        <<<RESPONSE
HTTP/1.1 201 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 201

RESPONSE
        ,BASE_URL . '/projects/22/people/delete.xml?title=CEO'
        ,'delete'
        ,array('project_id' => 22, 'title' => 'CEO')
        ,false
      ),
    );
  }

  /**
   * @dataProvider collectionDeleteAndHeadDataProvider 
   */
  public function testCollectionDelete($response, $url, $method, array $params, $return)
  {
    $connection = $this->getMockedConnection('delete', $url, null, $response);
    $connection->getAdapter()->addResponse('delete', $url, null, $response);

    $this->assertEquals($return, Person::collectionDelete($method, $params, $connection));
  }

  /**
   * @dataProvider collectionDeleteAndHeadDataProvider 
   */
  public function testCollectionHead($response, $url, $method, array $params, $return)
  {
    $connection = $this->getMockedConnection('head', $url, null, $response);
    $connection->getAdapter()->addResponse('head', $url, null, $response);

    $this->assertEquals($return, Person::collectionHead($method, $params, $connection));
  }

  /**
   * @dataProvider loadRemoteErrorsDataProvider
   */
  public function testLoadRemoteErrors($response, $url, $return)
  {
      $connection = $this->getMockedConnection('post', $url, null, $response);

      $todo = new TodoList(array(), $connection);
      $this->assertFalse($todo->save());
      $this->assertEquals($return, $todo->getErrors()->getFullMessages());
  }

  public function loadRemoteErrorsDataProvider()
  {
    return array(
      array(
        <<<RESPONSE
HTTP/1.1 422 Unprocessable Entity
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8

<errors>
    <error>Name cannot be blank</error>
    <error>User is not a number</error>
</errors>
RESPONSE
        ,BASE_URL . '/todo_lists.xml',
        array(
          'Name cannot be blank',
          'User is not a number',
        ),
      ),
    );
  }
}

