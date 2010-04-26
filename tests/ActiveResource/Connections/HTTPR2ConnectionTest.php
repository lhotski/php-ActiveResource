<?php

/*
 * This file is part of the php-ActiveResource.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/HTTP_Request2_Adapter_UrlMock.php';
require_once __DIR__ . '/ConnectionMockData.php';

use ActiveResource\Connections\HTTPR2Connection as Connection;
use ActiveResource\Exceptions\ConnectionException;
use HTTP_Request2_Adapter_UrlMock as MockAdapter;

class HTTPR2ConnectionTest extends PHPUnit_Framework_TestCase
{
  public function newConnectionProvider()
  {
    return array(
      array(new Connection('http://mysite.com'), 'http://mysite.com', '', null, null),
      array(new Connection('http://subdom.mysite.com/path/2/test'), 'http://subdom.mysite.com', '/path/2/test', null, null),
      array(new Connection('http://ever:test123@subdom.mysite.com/path'), 'http://subdom.mysite.com', '/path', 'ever', 'test123'),
      array(new Connection('https://subdom.mysite.com'), 'https://subdom.mysite.com', '', null, null),
      array(new Connection('https://ever:test123@subdom.mysite.com/path/23/sub'), 'https://subdom.mysite.com', '/path/23/sub', 'ever', 'test123'),
    );
  }

  /**
   * @dataProvider newConnectionProvider
   */
  public function testConnectionConstruct(Connection $connection, $url, $path, $user, $pass)
  {
    $this->assertEquals($url,  $connection->getSite(), 'Right site base URL');
    $this->assertEquals($user, $connection->getUsername(), 'Right username');
    $this->assertEquals($pass, $connection->getPassword(), 'Right password');
    $this->assertEquals($path, $connection->getBasePath(), 'BasePath for connection');
  }

  /**
   * @dataProvider newConnectionProvider
   */
  public function testConnectionSetters(Connection $connection, $url, $path, $user, $pass)
  {
    $user .= '_setTest';
    $pass .= '_setTest';

    $connection->setSite($url);
    $connection->setBasePath($path);
    $connection->setAuth($user, $pass);

    $this->assertEquals($url,  $connection->getSite(), 'Right site base URL');
    $this->assertEquals($user, $connection->getUsername(), 'Right username');
    $this->assertEquals($pass, $connection->getPassword(), 'Right password');
    $this->assertEquals($path, $connection->getBasePath(), 'BasePath for connection');
  }

  public function mockedGetConnectionProvider()
  {
    $connection = new Connection(MOCK_DATA_URL);
    $data = array();

    foreach ($GLOBALS['mock_data_get'] as $mock_data)
    {
      $adapter = new MockAdapter;
      $adapter->addResponse('get', $mock_data['url'], $mock_data['body'], $mock_data['response']);

      $data[] = array(
        $connection, $adapter, $mock_data['body'], $mock_data['decoded'], $mock_data['path'], $mock_data['code']
      );
    }

    return $data;
  }

  /**
   * @dataProvider mockedGetConnectionProvider
   */
  public function testGet(Connection $connection, MockAdapter $adapter, $body, $decoded, $path, $code)
  {
    $connection->setAdapter($adapter);
    $response = $connection->get($path);

    // Response test
    $this->assertEquals(trim($body),  $response->getBody(), 'Right response body');
    $this->assertEquals($code,        $response->getCode(), 'Right response status code');

    // Decoded body test
    $this->assertEquals($decoded, $response->getDecodedBody(), 'Right decoded body');
  }

  public function mockedHeadAndDeleteConnectionProvider()
  {
    $connection = new Connection(MOCK_DATA_URL);
    $data = array();

    foreach ($GLOBALS['mock_data_head'] as $mock_data)
    {
      $adapter = new MockAdapter;
      $adapter->addResponse('head', $mock_data['url'], null, $mock_data['response']);
      $adapter->addResponse('delete', $mock_data['url'], null, $mock_data['response']);

      $data[] = array(
        $connection, $adapter, $mock_data['path'], $mock_data['code'], $mock_data['headers']
      );
    }

    return $data;
  }

  /**
   * @dataProvider mockedHeadAndDeleteConnectionProvider
   */
  public function testHeadAndDelete(Connection $connection, MockAdapter $adapter, $path, $code, array $headers)
  {
    $connection->setAdapter($adapter);

    foreach (array('head', 'delete') as $method)
    {
      $response = $connection->$method($path);

      // Response test
      $this->assertEquals($code,        $response->getCode(), 'Right response status code');
      $this->assertEquals($headers,     $response->getHeaders(), 'Right headers');
    }
  }

  public function mockedPostAndPutConnectionProvider()
  {
    $connection = new Connection(MOCK_DATA_URL);
    $data = array();

    foreach ($GLOBALS['mock_data_post'] as $mock_data)
    {
      $adapter = new MockAdapter;
      $adapter->addResponse('post', $mock_data['url'], $mock_data['body'], $mock_data['response']);
      $adapter->addResponse('put', $mock_data['url'], $mock_data['body'], $mock_data['response']);

      $data[] = array(
        $connection, $adapter, $mock_data['body'], $mock_data['path'], $mock_data['code'], $mock_data['headers']
      );
    }

    return $data;
  }

  /**
   * @dataProvider mockedPostAndPutConnectionProvider
   */
  public function testPostAndPut(Connection $connection, MockAdapter $adapter, $body, $path, $code, array $headers)
  {
    $connection->setAdapter($adapter);

    foreach (array('put', 'post') as $method)
    {
      $response = $connection->$method($path, $body);

      // Response test
      $this->assertEquals($code,        $response->getCode(), 'Right response status code');
      $this->assertEquals($headers,     $response->getHeaders(), 'Right headers');
    }
  }

  public function connectionBadStatusCodeProvider()
  {
    $connection = new Connection(MOCK_DATA_URL);
    $data = array();

    foreach ($GLOBALS['mock_data_bad_status'] as $mock_data)
    {
      $adapter = new MockAdapter;
      $adapter->addResponse('get', MOCK_DATA_URL_PREFIX . '/', null, $mock_data['response']);
      $adapter->addResponse('post', MOCK_DATA_URL_PREFIX . '/', null, $mock_data['response']);
      $adapter->addResponse('put', MOCK_DATA_URL_PREFIX . '/', null, $mock_data['response']);
      $adapter->addResponse('head', MOCK_DATA_URL_PREFIX . '/', null, $mock_data['response']);
      $adapter->addResponse('delete', MOCK_DATA_URL_PREFIX . '/', null, $mock_data['response']);

      $data[] = array(
        $connection, $adapter, $mock_data['exception']
      );
    }

    return $data;
  }

  /**
   * @dataProvider connectionBadStatusCodeProvider
   */
  public function testBadStatusExceptions(Connection $connection, MockAdapter $adapter, $exception)
  {
    $this->setExpectedException($exception);
    $connection->setAdapter($adapter);

    foreach(array('get', 'post', 'put', 'head', 'delete') as $method)
    {
      $response = $connection->$method('/');
    }
  }
}
