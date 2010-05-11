<?php

/*
 * This file is part of the php-ActiveResource.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ActiveResource\Connections;

use ActiveResource\Responses\HTTPR2Response as Response;

use ActiveResource\Formats\Format;
use ActiveResource\Formats\XML as XMLFormat;

use ActiveResource\Exceptions\ConnectionException;
use ActiveResource\Exceptions\TimeoutException;
use ActiveResource\Exceptions\SSLException;
use ActiveResource\Exceptions\Redirection;
use ActiveResource\Exceptions\ClientException;
use ActiveResource\Exceptions\BadRequest;
use ActiveResource\Exceptions\UnauthorizedAccess;
use ActiveResource\Exceptions\ForbiddenAccess;
use ActiveResource\Exceptions\ResourceNotFound;
use ActiveResource\Exceptions\ResourceConflict;
use ActiveResource\Exceptions\ResourceGone;
use ActiveResource\Exceptions\ServerError;
use ActiveResource\Exceptions\MethodNotAllowed;

require_once 'Net/URL2.php';
require_once 'HTTP/Request2.php';

/**
 * HTTPR2Connection implements Connection interface using HTTP_Request2 PEAR library
 *
 * @package    ActiveResource
 * @subpackage Connections
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 * @version    1.0.0
 */
class HTTPR2Connection implements Connection
{
  protected $site;
  protected $auth_type = 'basic';
  protected $headers = array();
  protected $timeout;
  protected $format;
  protected $adapter = 'socket';

  /**
   * Connection constructor
   *
   * @param   string                        $site   base site URL
   * @param   ActiveResource\Formats\Format $format formatter instance
   */
  public function __construct($site, Format $format = null)
  {
    if (null === $site || empty($site))
    {
      throw new \InvalidArgumentException('Missing site URI');
    }

    $this->setSite($site);
    if (null === $format)
    {
      $this->setFormat(new XMLFormat);
    }
    else
    {
      $this->setFormat($format);
    }
  }

  /**
   * Sets connection adapter, used to send requests & recieve responses
   *
   * @param   mixed   $adapter  adapter name or object
   */
  public function setAdapter($adapter)
  {
    $this->adapter = $adapter;
  }

  /**
   * Returns current connection adapter
   *
   * @return  mixed             adapter name or object
   */
  public function getAdapter()
  {
    return $this->adapter;
  }

  /**
   * Returns site pure URL (without path & user parts)
   *
   * @see     ActiveResource\Connections\Connection::getSite()
   */
  public function getSite()
  {
    $user_info = $this->site->getUserinfo();
    $base_path = $this->site->getPath();

    $this->site->setUserinfo(false);
    $this->site->setPath('');

    $site = $this->site->getUrl();

    $this->site->setUserinfo($user_info);
    $this->site->setPath($base_path);

    return $site;
  }

  /**
   * Sets base site URL
   *
   * @see     ActiveResource\Connections\Connection::setSite()
   */
  public function setSite($site)
  {
    if ($site instanceof \Net_URL2)
    {
      $this->site = $site;
    }
    else
    {
      $this->site = new \Net_URL2($site);
    }
  }

  /**
   * Sets base path
   *
   * @see     ActiveResource\Connections\Connection::setBasePath()
   */
  public function setBasePath($path)
  {
    $this->site->setPath($path);
  }

  /**
   * Returns base path
   *
   * @see     ActiveResource\Connections\Connection::getBasePath()
   */
  public function getBasePath()
  {
    return $this->site->getPath();
  }

  /**
   * Returns connection username
   *
   * @see     ActiveResource\Connections\Connection::getUsername()
   */
  public function getUsername()
  {
    return $this->site->getUser();
  }

  /**
   * Returns connection password
   *
   * @see     ActiveResource\Connections\Connection::getPassword()
   */
  public function getPassword()
  {
    return $this->site->getPassword();
  }

  /**
   * Returns connection auth type
   *
   * @see     ActiveResource\Connections\Connection::getAuthType()
   */
  public function getAuthType()
  {
    return $this->auth_type;
  }

  /**
   * Sets connection auth routines
   *
   * @see     ActiveResource\Connections\Connection::setAuth()
   */
  public function setAuth($username, $password, $auth_type = 'basic')
  {
    if (!in_array($auth_type, array('basic', 'digest')))
    {
      throw new \InvalidArgumentException(sprintf('Wrong Auth type: %s', $auth_type));
    }

    $this->site->setUserinfo($username, $password);
    $this->auth_type = $auth_type;
  }

  /**
   * Sets specific connection headers
   *
   * @see     ActiveResource\Connections\Connection::setHeaders()
   */
  public function setHeaders(array $headers)
  {
    $this->headers = $headers;
  }

  /**
   * Sets specific connection headers
   *
   * @see     ActiveResource\Connections\Connection::setHeader()
   */
  public function setHeader($name, $value)
  {
    $this->headers[$name] = $value;
  }

  /**
   * Returns specific connection header
   *
   * @see     ActiveResource\Connections\Connection::getHeader()
   */
  public function getHeader($name)
  {
    if (!isset($this->headers[$name]))
    {
      return null;
    }

    return $this->headers[$name];
  }

  /**
   * Returns connection timeout in ms
   *
   * @see     ActiveResource\Connections\Connection::getTimeout()
   */
  public function getTimeout()
  {
    return $this->timeout;
  }

  /**
   * Sets connection timeout
   *
   * @see     ActiveResource\Connections\Connection::setTimeout()
   */
  public function setTimeout($timeout)
  {
    $this->timeout = intval($timeout);
  }

  /**
   * Returns connection request/response formatter
   *
   * @see     ActiveResource\Connections\Connection::getFormat()
   */
  public function getFormat()
  {
    return $this->format;
  }

  /**
   * Sets connection request/response formatter
   *
   * @see     ActiveResource\Connections\Connection::setFormat()
   */
  public function setFormat(Format $format)
  {
    $this->format = $format;
  }

  /**
   * Sends GET request & returns formatted response object
   *
   * @see     ActiveResource\Connections\Connection::get()
   */
  public function get($path, array $headers = array())
  {
    $response = $this->send('get', $path, null, $headers);

    return $response;
  }

  /**
   * Sends HEAD request & returns formatted response object
   *
   * @see     ActiveResource\Connections\Connection::head()
   */
  public function head($path, array $headers = array())
  {
    $response = $this->send('head', $path, null, $headers);

    return $response;
  }

  /**
   * Sends DELETE request & returns formatted response object
   *
   * @see     ActiveResource\Connections\Connection::delete()
   */
  public function delete($path, array $headers = array())
  {
    $response = $this->send('delete', $path, null, $headers);

    return $response;
  }

  /**
   * Sends PUT request & returns formatted response object
   *
   * @see     ActiveResource\Connections\Connection::put()
   */
  public function put($path, array $body = array(), array $headers = array())
  {
    $body = $this->format->encode($body);
    $response = $this->send('put', $path, $body, $headers);

    return $response;
  }

  /**
   * Sends POST request & returns formatted response object
   *
   * @see     ActiveResource\Connections\Connection::post()
   */
  public function post($path, array $body = array(), array $headers = array())
  {
    $body = $this->format->encode($body);
    $response = $this->send('post', $path, $body, $headers);

    return $response;
  }

  /**
   * Perform request creation, sending & response preparing
   *
   * @param   string  $method                   method name (GET, HEAD, DELETE, POST, PUT)
   * @param   string  $path                     resource path 
   * @param   string  $body                     request body
   * @param   array   $headers                  headers hash
   * 
   * @return  ActiveResource\Responses\Response response instance
   */
  protected function send($method, $path, $body = null, array $headers = array())
  {
    $request  = $this->prepareRequest($method, $path, $body, $headers);
    $response = null;

    try
    {
      $response = $request->send();

      if (in_array($response->getStatus(), array(200, 201, 202, 203, 204, 205, 206)))
      {
        return $this->prepareResponse($response);
      }
      else
      {
        switch ($response->getStatus())
        {
          case 300:
          case 301:
          case 302:
          case 303:
          case 304:
          case 305:
          case 307:
            throw new Redirection($response);
            break;
          case 400:
            throw new BadRequest($response);
            break;
          case 401:
            throw new UnauthorizedAccess($response);
            break;
          case 403:
            throw new ForbiddenAccess($response);
            break;
          case 404:
            throw new ResourceNotFound($response);
            break;
          case 405:
            throw new MethodNotAllowed($response);
            break;
          case 409:
            throw new ResourceConflict($response);
            break;
          case 410:
            throw new ResourceGone($response);
            break;
          case 404:
            throw new ResourceNotFound($response);
            break;
          case 500:
          case 501:
          case 502:
          case 503:
          case 504:
          case 505:
          case 509:
            throw new ServerError($response);
            break;
          default:
            throw new ConnectionException($response);
        }
      }
    }
    catch (\HTTP_Request2_Exception $e)
    {
      if (false !== strpos($e->getMessage(), '#28'))
      {
        throw new TimeoutException($response, $e->getMessage());
      }
      elseif (false !== strpos($e->getMessage(), 'SSL'))
      {
        throw new SSLException($response, $e->getMessage());
      }
      else
      {
        throw new ConnectionException($response, $e->getMessage());
      }
    }
  }

  /**
   * Creates & prepares new request object
   *
   * @param   string  $method                   method name (GET, HEAD, DELETE, POST, PUT)
   * @param   string  $path                     resource path
   * @param   string  $body                     request body
   * @param   array   $headers                  headers hash
   * 
   * @return  HTTP_Request2                     request object
   */
  protected function prepareRequest($method, $path, $body, array $headers)
  {
    $request = new \HTTP_Request2($this->prepareUrl($path));

    $request->setAdapter($this->adapter);
    $request->setMethod(strtoupper($method));

    $request->setConfig('ssl_verify_peer', false);
    $request->setConfig('ssl_verify_host', false);

    if (null !== $this->timeout)
    {
      $request->setConfig('connect_timeout', $this->timeout);
      $request->setConfig('timeout', $this->timeout);
    }

    if (null !== $this->getUsername() && null !== $this->getPassword())
    {
      $request->setAuth($this->getUsername(), $this->getPassword(), $this->auth_type);
    }

    if (null !== $body)
    {
      $request->setBody($body);
    }

    foreach ($this->prepareHeaders($headers) as $name => $value)
    {
      $request->setHeader($name, $value);
    }

    return $request;
  }

  /**
   * Prepares request URL
   *
   * @param   string  $path                     resource path
   * 
   * @return  string                            request URL
   */
  protected function prepareUrl($path)
  {
    $site = new \Net_URL2($this->getSite());
    $site->setPath(
      $this->getBasePath() . strtr($path, array(':extension:' => $this->format->getExtension()))
    );

    return $site->getUrl();
  }

  /**
   * Prepares request headers
   *
   * @param   array   $headers                  headers hash
   * 
   * @return  array                             prepared headers
   */
  protected function prepareHeaders(array $headers = array())
  {
    return array_merge(
      array(
        'accept'        => $this->format->getMimeType(),
        'content-type'  => $this->format->getMimeType()
      ),
      $this->headers,
      $headers
    );
  }

  /**
   * Converts HTTP_Request2 response object into ActiveResource's one
   *
   * @param   HTTP_Request2_Response            $response recieved response object
   * 
   * @return  ActiveResource\Responses\Response           response object
   */
  protected function prepareResponse(\HTTP_Request2_Response $response)
  {  
    $body     = trim($response->getBody());
    $decoded  = strlen($body) ? $this->format->decode($body) : '';
    $response = new Response($response, $decoded);

    return $response;
  }
}
