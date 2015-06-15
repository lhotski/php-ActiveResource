<?php
namespace ActiveResource\Connections;

use cdyweb\http\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class GuzzleConnection extends Connection {

    /**
     * @var \cdyweb\http\psr\Uri
     */
    protected $uri;

    protected $headers = array();
    protected $timeout = null;
    protected $auth_type = 'basic';

    /**
     * @var \cdyweb\http\Adapter
     */
    protected $client;

    /**
     * @param \cdyweb\http\Adapter $client
     */
    public function setClient(\cdyweb\http\Adapter $client) {
        $this->client = $client;
    }

    /**
     * @return \cdyweb\http\Adapter
     */
    public function getClient() {
        if (empty($this->client)) {
            $this->client = \cdyweb\http\guzzle\Guzzle::getAdapter();
        }
        return $this->client;
    }

    /**
     * Returns site pure URL (without path & user parts)
     *
     * @return  string        site URL
     */
    public function getSite()
    {
        if (empty($this->uri)) throw new \RuntimeException('URI not set');
        return $this->uri->getScheme().'://'.$this->uri->getHost();
    }

    /**
     * Sets base site URL
     *
     * http://site.com/base/path
     * https://user@pass:site.com/base/path
     *
     * @param   string $site base site URL
     */
    public function setSite($site)
    {
        $this->uri = new \cdyweb\http\psr\Uri($site);
    }

    /**
     * Sets base path
     *
     * @param   string $path base path to resources
     */
    public function setBasePath($base_path)
    {
        if (empty($this->uri)) throw new \RuntimeException('URI not set');
        $this->uri = $this->uri->withPath($base_path);
    }

    /**
     * Returns base path
     *
     * @return  string              base path to resources
     */
    public function getBasePath()
    {
        if (empty($this->uri)) throw new \RuntimeException('URI not set');
        return $this->uri->getPath();
    }

    /**
     * Returns connection username
     *
     * @return  string              username (login)
     */
    public function getUsername()
    {
        if (empty($this->uri)) throw new \RuntimeException('URI not set');
        $arr=explode(':',$this->uri->getUserInfo(),2);
        return isset($arr[0]) ? $arr[0] : null;
    }

    /**
     * Returns connection password
     *
     * @return  string              password
     */
    public function getPassword()
    {
        if (empty($this->uri)) throw new \RuntimeException('URI not set');
        $arr=explode(':',$this->uri->getUserInfo(),2);
        return isset($arr[1]) ? $arr[1] : null;
    }

    /**
     * Returns connection auth type
     *
     * @return  string              auth type
     */
    public function getAuthType()
    {
        return $this->auth_type;
    }

    /**
     * Sets connection auth routines
     *
     * @param   string $username username
     * @param   string $password password
     * @param   string $auth_type auth type ('basic' or 'digest')
     */
    public function setAuth($username, $password, $auth_type = 'basic')
    {
        if (empty($this->uri)) throw new \RuntimeException('URI not set');
        $this->auth_type = $auth_type;
        $this->uri = $this->uri->withUserInfo($username, $password);
    }

    /**
     * Returns specific connection header
     *
     * @param   string $name header name
     * @return  string              header
     */
    public function getHeader($name)
    {
        $headers = $this->getClient()->getRequestHeaders();
        return isset($headers[$name]) ? $headers[$name] : null;
    }

    /**
     * Sets specific connection headers
     *
     * @param   array $headers hash of headers
     */
    public function setHeaders(array $headers)
    {
        $this->getClient()->appendRequestHeaders($headers);
    }

    /**
     * Sets specific connection header
     *
     * @param   string $name header name
     * @param   string $value header
     */
    public function setHeader($name, $value)
    {
        $this->getClient()->appendRequestHeader($name, $value);
    }

    /**
     * Returns connection timeout in ms
     *
     * @return  integer             connection timeout
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Sets connection timeout
     *
     * @param   integer $timeout connection timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * Sends HEAD request & returns formatted response object
     *
     * @param   string $path resource path
     * @param   array $headers specific headers hash
     *
     * @return  ResponseInterface response instance
     */
    public function head($path, array $headers = array())
    {
        return $this->client_call('head', $path, '', $headers);
    }

    /**
     * Sends GET request & returns formatted response object
     *
     * @param   string $path resource path
     * @param   array $headers specific headers hash
     *
     * @return  ResponseInterface response instance
     */
    public function get($path, array $headers = array())
    {
        return $this->client_call('get', $path, '', $headers);
    }

    /**
     * Sends DELETE request & returns formatted response object
     *
     * @param   string $path resource path
     * @param   array $headers specific headers hash
     *
     * @return  ResponseInterface response instance
     */
    public function delete($path, array $headers = array())
    {
        return $this->client_call('delete', $path, '', $headers);
    }

    /**
     * Sends PUT request & returns formatted response object
     *
     * @param   string $path resource path
     * @param   array $headers specific headers hash
     * @param   string $body request body
     *
     * @return  ResponseInterface response instance
     */
    public function put($path, $body, array $headers = array())
    {
        return $this->client_call('put', $path, $body, $headers);
    }

    /**
     * Sends POST request & returns formatted response object
     *
     * @param   string $path resource path
     * @param   array $headers specific headers hash
     * @param   string $body request body
     *
     * @return  ResponseInterface response instance
     */
    public function post($path, $body, array $headers = array())
    {
        return $this->client_call('post', $path, $body, $headers);
    }

    /**
     * @param $method
     * @param $path
     * @param $body
     * @param array $headers
     * @return \GuzzleHttp\Promise\PromiseInterface|mixed
     * @throws \ActiveResource\Exceptions\BadRequest
     * @throws \ActiveResource\Exceptions\ResourceNotFound
     */
    public function client_call($method, $path, $body, array $headers = array()) {
        try {
            return $this->getClient()->send($this->getClient()->createRequest(
                $method, $this->uri . $path, $headers, $body
            ));
        } catch (RequestException $ex) {
            switch ($ex->getCode()) {
                case 300:
                case 301:
                case 302:
                case 303:
                case 304:
                case 305:
                case 307:
                    throw new \ActiveResource\Exceptions\Redirection($ex);
                    break;
                case 400:
                    throw new \ActiveResource\Exceptions\BadRequest($ex);
                    break;
                case 401:
                    throw new \ActiveResource\Exceptions\UnauthorizedAccess($ex);
                    break;
                case 403:
                    throw new \ActiveResource\Exceptions\ForbiddenAccess($ex);
                    break;
                case 404:
                    throw new \ActiveResource\Exceptions\ResourceNotFound($ex);
                    break;
                case 405:
                    throw new \ActiveResource\Exceptions\MethodNotAllowed($ex);
                    break;
                case 409:
                    throw new \ActiveResource\Exceptions\ResourceConflict($ex);
                    break;
                case 410:
                    throw new \ActiveResource\Exceptions\ResourceGone($ex);
                    break;
                case 404:
                    throw new \ActiveResource\Exceptions\ResourceNotFound($ex);
                    break;
                case 422:
                    throw new \ActiveResource\Exceptions\ResourceInvalid($ex);
                    break;
                case 500:
                case 501:
                case 502:
                case 503:
                case 504:
                case 505:
                case 509:
                    throw new \ActiveResource\Exceptions\ServerError($ex);
                    break;
                default:
                    throw new \ActiveResource\Exceptions\ConnectionException($ex);
            }
        }
    }

}