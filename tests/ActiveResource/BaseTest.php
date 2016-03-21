<?php

/*
 * This file is part of the php-ActiveResource.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'Person.php';
require_once 'TodoList.php';

use ActiveResource\Connections\GuzzleConnection;

define('BASE_URL', 'https://some.rest.site.com/base/path');


class BaseTest extends PHPUnit_Framework_TestCase
{

    public function setUp() {
        GuzzleConnection::init(BASE_URL);
    }

    private function getMockedConnection($method, $url, $response_body, $response_code, $respone_headers=array(), $request_headers=array(),  $request_body='')
    {
        $connection = new GuzzleConnection(BASE_URL);
        $mockedClient = $this->getMock('\cdyweb\http\Adapter');
        $connection->setClient($mockedClient);

        $response = (new \cdyweb\http\psr\Response($response_code, $respone_headers, $response_body));

        if (substr($response_code,0,1)==4) {
            $mockedClient
                ->expects($this->once())
                ->method('send')
                ->willThrowException(new \cdyweb\http\Exception\RequestException('not found', new \cdyweb\http\psr\Request($method, $url, $request_headers, $request_body), $response));
        } else {
            $mockedClient
                ->expects($this->once())
                ->method('send')
                ->with(1)
                ->will($this->returnValue($response));
            $mockedClient
                ->expects($this->once())
                ->method('createRequest')
                ->with($method, $url, $request_headers, $request_body)
                ->will($this->returnValue(1));
        }

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
        $todo = new TodoList(array('id' => $id, 'user_id' => $user_id, 'name' => $name));

        $this->assertEquals($id, $todo->getId());
        $this->assertEquals($user_id, $todo->user_id);
        $this->assertEquals($name, $todo->name);
    }

    /**
     * @dataProvider constructorDataProvider
     */
    public function testInit($id, $user_id, $name)
    {
        $todo = TodoList::init(array('id' => $id, 'user_id' => $user_id, 'name' => $name));

        $this->assertEquals($id, $todo->getId());
        $this->assertEquals($user_id, $todo->user_id);
        $this->assertEquals($name, $todo->name);
    }

    /**
     * @dataProvider constructorDataProvider
     */
    public function testLoad($id, $user_id, $name)
    {
        $todo = new TodoList(array());
        $todo->load(array('id' => $id, 'user_id' => $user_id, 'name' => $name));

        $this->assertEquals($id, $todo->getId());
        $this->assertEquals($user_id, $todo->user_id);
        $this->assertEquals($name, $todo->name);
    }

    public function isExistsDataProvider()
    {
        return array(
            array(
                200
                ,23
                ,array()
                ,array()
                ,BASE_URL . '/todo_lists/23.json'
                ,true
            ),
            array(
                410
                ,2
                ,array('project_id' => 4)
                ,array('name' => 'Ivan', 'age' => 21)
                ,BASE_URL . '/projects/4/todo_lists/2.json?name=Ivan&age=21'
                ,false
            ),
            array(
                404
                ,102
                ,array('project_id' => 4, 'person_id' => 101)
                ,array('name' => 'Ivan', 'age' => 21)
                ,BASE_URL . '/projects/4/people/101/todo_lists/102.json?name=Ivan&age=21'
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
                202
                ,json_encode(array('todo_list'=>array('id'=>5, 'user_id'=>1, 'name'=>'LIST')))
                ,5
                ,1
                ,'LIST'
                ,array()
                ,array()
                ,BASE_URL . '/todo_lists/new.json'
            ),
            array(
                200
                ,json_encode(array('todo_list'=>array('id'=>1, 'user_id'=>2, 'name'=>'ToDo list')))
                ,1
                ,2
                ,'ToDo list'
                ,array('project_id' => 4, 'person_id' => 101)
                ,array('name' => 'Ivan', 'age' => 21)
                ,BASE_URL . '/projects/4/people/101/todo_lists/new.json?name=Ivan&age=21'
            ),
        );
    }

    /**
     * @dataProvider buildDataProvider
     */
    public function testBuild($response, $body, $id, $user_id, $name, array $prefix_options, array $query_options, $url)
    {
        $connection = $this->getMockedConnection('get', $url, $body, $response);

        $list = TodoList::build($prefix_options, $query_options, $connection);

        $this->assertEquals($id, $list->getId());
        $this->assertEquals($user_id, $list->user_id);
        $this->assertEquals($name, $list->name);
    }

    /**
     * @dataProvider buildDataProvider
     */
    public function testIsNewAndIsPersisted($response, $body, $id, $user_id, $name, array $prefix_options, array $query_options, $url)
    {
        $connection = $this->getMockedConnection('get', $url, $body, $response);

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
                201
                ,json_encode(array('todo_list'=>array('user_id'=>'41', 'name'=>'everzet', 'is_bool'=>true)))
                , array('Location'=>array(BASE_URL.'/todo_list/5'))
                ,json_encode(array('todo_list'=>array('user_id'=>'41', 'name'=>'everzet', 'is_bool'=>true)))
                , array('accept'=>'application/json','content-type'=>'application/json')
                ,'/test/123/create'
                ,41
                ,'everzet'
                ,true
                ,5
                ,true
            ),
            array(
                202
                ,json_encode(array('todo_list'=>array('user_id'=>'55', 'name'=>'Ivan', 'is_bool'=>false)))
                , array('Location'=>array())
                ,json_encode(array('todo_list'=>array('user_id'=>'55', 'name'=>'Ivan', 'is_bool'=>false)))
                , array('accept'=>'application/json','content-type'=>'application/json')
                ,'/todo_lists.json'
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
    public function testCreate($response, $response_body, $response_headers,  $request_body, $request_headers, $path, $user_id, $name, $is_bool, $id, $answer)
    {
        $connection = $this->getMockedConnection('post', BASE_URL.$path, $response_body, $response, $response_headers, $request_headers, $request_body);

        $list = new TodoList(array('user_id' => $user_id, 'name' => $name, 'is_bool' => $is_bool), $connection);

        $this->assertEquals($answer, $list->save($path));
        $this->assertEquals($id, $list->getId());
    }

    public function updateDataProvider()
    {
        return array(
            array(
                204
                ,json_encode(array('todo_list'=>array('user_id'=>'41', 'name'=>'everzet', 'is_bool'=>true)))
                ,json_encode(array('todo_list'=>array('user_id'=>'41', 'name'=>'everzet', 'is_bool'=>true)))
                ,array('Location'=> array(BASE_URL.'/todo_lists/5.json'))
                ,array('accept'=>'application/json','content-type'=>'application/json')
                ,BASE_URL . '/todo_lists/5.json'
                ,41
                ,'everzet'
                ,true
                ,5
                ,true
                ),
            array(
                202
                ,json_encode(array('todo_list'=>array('user_id'=>'55', 'name'=>'Ivan', 'is_bool'=>false)))
                ,json_encode(array('todo_list'=>array('user_id'=>'55', 'name'=>'Ivan', 'is_bool'=>false)))
                ,array('Location'=> array(BASE_URL.'/todo_lists/5.json'))
                , array('accept'=>'application/json','content-type'=>'application/json')
                ,BASE_URL . '/todo_lists/10.json'
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
    public function testUpdate($response, $response_body, $request_body, $response_headers, $request_headers, $url, $user_id, $name, $is_bool, $id, $answer)
    {
        $connection = $this->getMockedConnection('put', $url, $response_body, $response, $response_headers, $request_headers, $request_body);

        $list = new TodoList(array('id' => $id, 'user_id' => $user_id, 'name' => $name, 'is_bool' => $is_bool), $connection);

        $this->assertEquals($answer, $list->save());
        $this->assertEquals($id, $list->getId());
    }

    public function destroyDataProvider()
    {
        return array(
            array(
                200
            ,BASE_URL . '/todo_lists/5.json'
            ,5
            ,true
            ),
            array(
                202
            ,BASE_URL . '/todo_lists/10.json'
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
        $person_response = json_encode(array('person'=>array('id'=>5, 'name'=>'Mary')));
        $people_response = json_encode(array('people'=>array(array('id'=>12, 'name'=>'John'),array('id'=>5, 'name'=>'Mary'),array('id'=>104, 'name'=>'David'))));
        $managers_response = json_encode(array('people'=>array(array('id'=>104, 'name'=>'David'),array('id'=>5, 'name'=>'Mary'))));

        return array(
            array(
                $person_response
                ,array(1)
                ,BASE_URL . '/people/1.json'
                ,new Person(array('id' => 5, 'name' => 'Mary'))
            ),
            array(
                $person_response
                ,1
                ,BASE_URL . '/people/1.json'
                ,new Person(array('id' => 5, 'name' => 'Mary'))
            ),
            array(
                $people_response
                ,array('all')
                ,BASE_URL . '/people.json'
                ,array(
                    new Person(array('id' => 12, 'name' => 'John')),
                    new Person(array('id' => 5, 'name' => 'Mary')),
                    new Person(array('id' => 104, 'name' => 'David'))
                )
            ),
            array(
                $people_response
                ,'all'
                ,BASE_URL . '/people.json'
                ,array(
                    new Person(array('id' => 12, 'name' => 'John')),
                    new Person(array('id' => 5, 'name' => 'Mary')),
                    new Person(array('id' => 104, 'name' => 'David'))
                )
            ),
            array(
                $managers_response
                ,array('all', 'params' => array('title' => 'CEO'))
                ,BASE_URL . '/people.json?title=CEO'
                ,array(
                    new Person(array('id' => 104, 'name' => 'David')),
                    new Person(array('id' => 5, 'name' => 'Mary'))
                )
            ),
            array(
                $managers_response
                ,array('first', 'from' => 'managers')
                ,BASE_URL . '/people/managers.json'
                ,new Person(array('id' => 104, 'name' => 'David')),
                ),
            array(
                $managers_response
                ,array('last', 'from' => 'managers')
                ,BASE_URL . '/people/managers.json'
                ,new Person(array('id' => 5, 'name' => 'Mary')),
            ),
            array(
                $people_response
            ,array('all', 'from' => '/companies/1/people.json')
            ,BASE_URL . '/companies/1/people.json'
            ,array(
                new Person(array('id' => 12, 'name' => 'John')),
                new Person(array('id' => 5, 'name' => 'Mary')),
                new Person(array('id' => 104, 'name' => 'David'))
            )
            ),
            array(
                $person_response
            ,array('one', 'from' => 'leader')
            ,BASE_URL . '/people/leader.json'
            ,new Person(array('id' => 5, 'name' => 'Mary'))
            ),
            array(
                $managers_response
            ,array('all', 'from' => 'developers', 'params' => array('language' => 'php'))
            ,BASE_URL . '/people/developers.json?language=php'
            ,array(
                new Person(array('id' => 104, 'name' => 'David')),
                new Person(array('id' => 5, 'name' => 'Mary'))
            )
            ),
            array(
                $person_response
            ,array('one', 'from' => '/companies/1/manager.json')
            ,BASE_URL . '/companies/1/manager.json'
            ,new Person(array('id' => 5, 'name' => 'Mary'))
            ),
            array(
                $person_response
            ,array(1, 'params' => array('project_id' => 2))
            ,BASE_URL . '/projects/2/people/1.json'
            ,new Person(array('id' => 5, 'name' => 'Mary'))
            )
        );
    }

    /**
     * @dataProvider findDataProvider
     */
    public function testFind($body, $criteria, $url, $etalon_data)
    {
        $connection = $this->getMockedConnection('get', $url, $body, 200, array(), array('accept'=>'application/json'));
        if (is_array($etalon_data)) foreach ($etalon_data as $obj) $obj->setConnection($connection);
        else $etalon_data->setConnection($connection);
        $response_data = Person::find($criteria, $connection);

        $this->assertEquals($etalon_data, $response_data);
    }

    public function collectionGetDataProvider()
    {
        return array(
            array(
                200
            ,json_encode(array('people'=>array(array('id'=>15, 'name'=>'David'),array('id'=>22, 'name'=>'John'))))
            ,'positions'
            ,array()
            ,BASE_URL . '/people/positions.json'
            ,array(array('id' => 15, 'name' => 'David'), array('id' => 22, 'name' => 'John'))
            ),
            array(
                200
            ,json_encode(array('person'=>array('id'=>22, 'name'=>'John')))
            ,'managers'
            ,array('title' => 'CEO', 'project_id' => 22)
            ,BASE_URL . '/projects/22/people/managers.json?title=CEO'
            ,array('id' => 22, 'name' => 'John')
            ),
        );
    }

    /**
     * @dataProvider collectionGetDataProvider
     */
    public function testCollectionGet($response, $body, $method, $params, $url, $etalon_data)
    {
        $connection = $this->getMockedConnection('get', $url, $body, $response, array(), array('accept'=>'application/json'));
        $response_data = Person::collectionGet($method, $params, $connection);

        $this->assertEquals($etalon_data, $response_data);
    }

    public function elementGetDataProvider()
    {
        return array(
            array(
                200
            ,json_encode(array('people'=>array(array('id'=>15, 'name'=>'David'),array('id'=>22, 'name'=>'John'))))
            ,'positions'
            ,array()
            ,BASE_URL . '/people/11/positions.json'
            ,array('id' => 11)
            ,array(array('id' => 15, 'name' => 'David'), array('id' => 22, 'name' => 'John'))
            ),
            array(
                200
            ,json_encode(array('person'=>array('id'=>22, 'name'=>'John')))
            ,'managers'
            ,array('title' => 'CEO', 'project_id' => 22)
            ,BASE_URL . '/projects/22/people/new/managers.json?title=CEO'
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
        $connection = $this->getMockedConnection('get', $url, $body, $response, array(), array('accept'=>'application/json'));

        $person = new Person($cur_data, $connection);
        $response_data = $person->elementGet($method, $params);

        $this->assertEquals($etalon_data, $response_data);
    }

    public function elementPostAndPutDataProvider()
    {
        return array(
            array(
                200
                ,json_encode(array('people'=>array(array('id'=>'15', 'name'=>'David'),array('id'=>'22', 'name'=>'John'))))
                ,json_encode(array('request'=>array(array('id'=>'15', 'name'=>'David'),array('id'=>'22', 'name'=>'John'))))
                ,BASE_URL . '/people/3/poll.json?filter=%2A&sex=man'
                ,3
                ,'poll'
                ,array('filter' => '*', 'sex' => 'man')
                ,array(array('id' => '15', 'name' => 'David'), array('id' => '22', 'name' => 'John'))
                ,200
            ),
            array(
                201
                ,json_encode(array('people'=>array(array('id'=>'15', 'name'=>'David'),array('id'=>'22', 'name'=>'John'))))
                ,json_encode(array('request'=>array(array('id'=>'15', 'name'=>'David'),array('id'=>'22', 'name'=>'John'))))
                ,BASE_URL . '/projects/22/people/25/register.json?title=CEO'
                ,25
                ,'register'
                ,array('project_id' => '22', 'title' => 'CEO')
                ,array(array('id' => '15', 'name' => 'David'), array('id' => '22', 'name' => 'John'))
                ,201
            ),
        );
    }

    /**
     * @dataProvider elementPostAndPutDataProvider
     */
    public function testElementPost($response, $response_body, $request_body, $url, $id, $method, array $params, array $data, $return)
    {
        $connection = $this->getMockedConnection('post', $url, $response_body, $response, array(), array('accept'=>'application/json', 'content-type'=>'application/json'), $request_body);
        $person = new Person(array('id' => $id), $connection);
        $result = $person->elementPost($method, $params, $data);
        $this->assertEquals((201 === $return), $result);
    }

    /**
     * @dataProvider elementPostAndPutDataProvider
     */
    public function testElementPut($response, $response_body, $request_body, $url, $id, $method, array $params, array $data, $return)
    {
        $connection = $this->getMockedConnection('put', $url, $response_body, $response, array(), array('accept'=>'application/json', 'content-type'=>'application/json'), $request_body);
        $person = new Person(array('id' => $id), $connection);
        $this->assertEquals((200 === $return), $person->elementPut($method, $params, $data));
    }

    public function collectionPostAndPutDataProvider()
    {
        return array(
            array(
                204
                ,json_encode(array('request'=>array(array('id'=>15, 'name'=>'David'),array('id'=>22, 'name'=>'John'))))
                ,json_encode(array('people'=>array(array('id'=>15, 'name'=>'David'),array('id'=>22, 'name'=>'John'))))
                ,BASE_URL . '/people/poll.json?filter=%2A&sex=man'
                ,'poll'
                ,array('filter' => '*', 'sex' => 'man')
                ,array(array('id' => 15, 'name' => 'David'), array('id' => 22, 'name' => 'John'))
                ,204
            ),
            array(
                201
                ,json_encode(array('request'=>array(array('id'=>15, 'name'=>'David'),array('id'=>22, 'name'=>'John'))))
                ,json_encode(array('people'=>array(array('id'=>15, 'name'=>'David'),array('id'=>22, 'name'=>'John'))))
                ,BASE_URL . '/projects/22/people/register.json?title=CEO'
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
    public function testCollectionPost($response, $request_body, $response_body, $url, $method, array $params, array $data, $return)
    {
        $connection = $this->getMockedConnection('post', $url, $response_body, $response, array(), array('accept'=>'application/json', 'content-type'=>'application/json'), $request_body);
        $this->assertEquals((201 === $return), Person::collectionPost($method, $params, $data, $connection));
    }

    /**
     * @dataProvider collectionPostAndPutDataProvider
     */
    public function testCollectionPut($response, $request_body, $response_body, $url, $method, array $params, array $data, $return)
    {
        $connection = $this->getMockedConnection('put', $url, $response_body, $response, array(), array('accept'=>'application/json', 'content-type'=>'application/json'), $request_body);
        $this->assertEquals((204 === $return), Person::collectionPut($method, $params, $data, $connection));
    }

    public function elementDeleteAndHeadDataProvider()
    {
        return array(
            array(
                200
                ,BASE_URL . '/people/3/fire.json?filter=%2A&sex=man'
                ,3
                ,'fire'
                ,array('filter' => '*', 'sex' => 'man')
                ,true
            ),
            array(
                201
                ,BASE_URL . '/projects/22/people/25/delete.json?title=CEO'
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
        $connection = $this->getMockedConnection('delete', $url, null, $response, array(), array('accept'=>'application/json'));
        $person = new Person(array('id' => $id), $connection);
        $this->assertEquals($return, $person->elementDelete($method, $params));
    }

    /**
     * @dataProvider elementDeleteAndHeadDataProvider
     */
    public function testElementHead($response, $url, $id, $method, array $params, $return)
    {
        $connection = $this->getMockedConnection('head', $url, null, $response, array(), array());
        $person = new Person(array('id' => $id), $connection);
        $this->assertEquals($return, $person->elementHead($method, $params));
    }

    public function collectionDeleteAndHeadDataProvider()
    {
        return array(
            array(
                200
                ,BASE_URL . '/people/fire.json?filter=%2A&sex=man'
                ,'fire'
                ,array('filter' => '*', 'sex' => 'man')
                ,true
            ),
            array(
                201
                ,BASE_URL . '/projects/22/people/delete.json?title=CEO'
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
        $connection = $this->getMockedConnection('delete', $url, null, $response, array(), array('accept'=>'application/json'));
        $this->assertEquals($return, Person::collectionDelete($method, $params, $connection));
    }

    /**
     * @dataProvider collectionDeleteAndHeadDataProvider
     */
    public function testCollectionHead($response, $url, $method, array $params, $return)
    {
        $connection = $this->getMockedConnection('head', $url, null, $response, array(), array('accept'=>'application/json'));
        $this->assertEquals($return, Person::collectionHead($method, $params, $connection));
    }

    public function loadRemoteErrorsDataProvider()
    {
        return array(
            array(
                422
            ,json_encode(array('errors'=>array('Name cannot be blank','User is not a number')))
            ,BASE_URL . '/todo_lists.json',
                array(
                    'Name cannot be blank',
                    'User is not a number',
                ),
            ),
        );
    }
    /**
     * @dataProvider loadRemoteErrorsDataProvider
     */
    public function testLoadRemoteErrors($response, $body, $url, $return)
    {
        $connection = $this->getMockedConnection('post', $url, $body, $response);

        $todo = new TodoList(array(), $connection);
        $this->assertFalse($todo->save());
        $this->assertEquals($return, $todo->getErrors()->getFullMessages());
    }

}

