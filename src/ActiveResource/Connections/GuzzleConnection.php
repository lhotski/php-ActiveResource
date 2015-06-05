<?php
namespace ActiveResource\Connections;

use Psr\Http\Message\ResponseInterface;

class GuzzleConnection implements Connection {
    protected $site;
    protected $base_path = '';
    protected $username = '';
    protected $password = '';
    protected $auth_type = 'basic';
    protected $headers = array();
    protected $timeout = null;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Connection constructor
     *
     * @param   string $site   base site URL
     */
    public function __construct($site)
    {
        if (null === $site || empty($site))
        {
            throw new \InvalidArgumentException('Missing site URI');
        }

        $this->setSite($site);
    }

    /**
     * @param \GuzzleHttp\Client $client
     */
    public function setClient(\GuzzleHttp\Client $client) {
        $this->client = $client;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient() {
        if (empty($this->client)) {
            $this->client = new \GuzzleHttp\Client();
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
        return $this->site;
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
        $url = parse_url($site);
        $this->site = "{$url['scheme']}://{$url['host']}";
        if (isset($url['path'])) $this->base_path = $url['path'];
        if (isset($url['user'])) $this->username = $url['user'];
        if (isset($url['pass'])) $this->password = $url['pass'];
    }

    /**
     * Sets base path
     *
     * @param   string $path base path to resources
     */
    public function setBasePath($base_path)
    {
        $this->base_path = $base_path;
    }

    /**
     * Returns base path
     *
     * @return  string              base path to resources
     */
    public function getBasePath()
    {
        return $this->base_path;
    }

    /**
     * Returns connection username
     *
     * @return  string              username (login)
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns connection password
     *
     * @return  string              password
     */
    public function getPassword()
    {
        return $this->password;
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
        $this->auth_type = $auth_type;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Returns specific connection header
     *
     * @param   string $name header name
     * @return  string              header
     */
    public function getHeader($name)
    {
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }

    /**
     * Sets specific connection headers
     *
     * @param   array $headers hash of headers
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $name=>$value) $this->setHeader($name, $value);
    }

    /**
     * Sets specific connection header
     *
     * @param   string $name header name
     * @param   string $value header
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
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
            return $this->getClient()->__call(
                $method,
                [
                    $path,
                    [
                        'base_uri' => $this->site . $this->base_path,
                        'headers' => $headers,
                        'body' => $body,
                    ]
                ]
            );
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            $response = $ex->getResponse();
            switch ($ex->getCode()) {
                case 300:
                case 301:
                case 302:
                case 303:
                case 304:
                case 305:
                case 307:
                    throw new \ActiveResource\Exceptions\Redirection($response, $ex);
                    break;
                case 400:
                    throw new \ActiveResource\Exceptions\BadRequest($response, $ex);
                    break;
                case 401:
                    throw new \ActiveResource\Exceptions\UnauthorizedAccess($response, $ex);
                    break;
                case 403:
                    throw new \ActiveResource\Exceptions\ForbiddenAccess($response, $ex);
                    break;
                case 404:
                    throw new \ActiveResource\Exceptions\ResourceNotFound($response, $ex);
                    break;
                case 405:
                    throw new \ActiveResource\Exceptions\MethodNotAllowed($response, $ex);
                    break;
                case 409:
                    throw new \ActiveResource\Exceptions\ResourceConflict($response, $ex);
                    break;
                case 410:
                    throw new \ActiveResource\Exceptions\ResourceGone($response, $ex);
                    break;
                case 404:
                    throw new \ActiveResource\Exceptions\ResourceNotFound($response, $ex);
                    break;
                case 422:
                    throw new \ActiveResource\Exceptions\ResourceInvalid($response, $ex);
                    break;
                case 500:
                case 501:
                case 502:
                case 503:
                case 504:
                case 505:
                case 509:
                    throw new \ActiveResource\Exceptions\ServerError($response, $ex);
                    break;
                default:
                    throw new \ActiveResource\Exceptions\ConnectionException($response, $ex);
            }
        }
    }

}