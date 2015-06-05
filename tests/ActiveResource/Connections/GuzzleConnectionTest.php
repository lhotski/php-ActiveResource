<?php

require_once __DIR__ . '/ConnectionMockData.php';

use ActiveResource\Connections\GuzzleConnection as Connection;

class GuzzleConnectionTest extends PHPUnit_Framework_TestCase
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

    public function mockedMethodConnectionProvider($method)
    {
        $data = array();
        if ($method=='delete') $arr=$GLOBALS['mock_data_head'];
        else if ($method=='put') $arr=$GLOBALS['mock_data_post'];
        else $arr=$GLOBALS['mock_data_'.$method];
        foreach ($arr as $mock_data)
        {
            $connection = new Connection(MOCK_DATA_URL);
            $mockedClient = $this->getMock('GuzzleHttp\Client');
            $connection->setClient($mockedClient);

            if (empty($mock_data['body'])) $mock_data['body']='';
            $response = (new GuzzleHttp\Psr7\Response($mock_data['code'], $mock_data['headers'], $mock_data['body']));

            $mockedClient
                ->expects($this->once())
                ->method('__call')
                ->with($method)
                ->will($this->returnValue($response));

            $data[] = array(
                $connection, $mock_data['body'], $mock_data['path'], $mock_data['code'], $mock_data['headers']
            );
        }

        return $data;
    }

    public function mockedGetConnectionProvider()
    {
        return $this->mockedMethodConnectionProvider('get');
    }

    /**
     * @dataProvider mockedGetConnectionProvider
     */
    public function testGet(Connection $connection, $body, $path, $code)
    {
        $response = $connection->get($path);

        // Response test
        $this->assertEquals(trim($body),  (string)$response->getBody(), 'Right response body');
        $this->assertEquals($code,        (string)$response->getStatusCode(), 'Right response status code');
    }
    /**/

    public function mockedHeadConnectionProvider()
    {
        return $this->mockedMethodConnectionProvider('head');
    }


    /**
     * @dataProvider mockedHeadConnectionProvider
     */
    public function testHead(Connection $connection, $body, $path, $code, array $headers)
    {
        $response = $connection->head($path);
        // Response test
        $this->assertEquals($code,        $response->getStatusCode(), 'Right response status code');
        $this->assertEquals($headers,     $response->getHeaders(), 'Right headers');
    }

    public function mockedDeleteConnectionProvider()
    {
        return $this->mockedMethodConnectionProvider('delete');
    }


    /**
     * @dataProvider mockedDeleteConnectionProvider
     */
    public function testDelete(Connection $connection, $body, $path, $code, array $headers)
    {
        $response = $connection->delete($path);
        // Response test
        $this->assertEquals($code,        $response->getStatusCode(), 'Right response status code');
        $this->assertEquals($headers,     $response->getHeaders(), 'Right headers');
    }


    public function mockedPostConnectionProvider()
    {
        return $this->mockedMethodConnectionProvider('post');
    }

    /**
     * @dataProvider mockedPostConnectionProvider
     */
    public function testPost(Connection $connection, $body, $path, $code, array $headers)
    {
        $response = $connection->post($path, $body);

        // Response test
        $this->assertEquals($code,        $response->getStatusCode(), 'Right response status code');
        $this->assertEquals($headers,     $response->getHeaders(), 'Right headers');
    }

    public function mockedPutConnectionProvider()
    {
        return $this->mockedMethodConnectionProvider('put');
    }

    /**
     * @dataProvider mockedPutConnectionProvider
     */
    public function testPut(Connection $connection, $body, $path, $code, array $headers)
    {
        $response = $connection->put($path, $body);

        // Response test
        $this->assertEquals($code,        $response->getStatusCode(), 'Right response status code');
        $this->assertEquals($headers,     $response->getHeaders(), 'Right headers');
    }


    public function connectionBadStatusCodeProvider()
    {
        $data = array();
        foreach ($GLOBALS['mock_data_bad_status'] as $mock_data)
        {

            $connection = new Connection(MOCK_DATA_URL);
            $mockedClient = $this->getMock('GuzzleHttp\Client');
            $connection->setClient($mockedClient);

            $response = (new GuzzleHttp\Psr7\Response($mock_data['response']));

            $mockedClient
                ->expects($this->once())
                ->method('__call')
                ->with('get')
                ->will($this->returnValue($response));

            $data[] = array(
                $connection, $mock_data['exception']
            );
        }

        return $data;
    }

    /**
     * @dataProvider connectionBadStatusCodeProvider
     */
    public function testBadStatusExceptions(Connection $connection, $exception)
    {
        //$this->setExpectedException($exception);
        $connection->get('/');
    }
}
