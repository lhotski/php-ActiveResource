<?php

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

class HTTPR2Connection implements Connection
{
  protected $site;
  protected $auth_type = 'basic';
  protected $headers = array();
  protected $timeout;
  protected $format;
  protected $adapter = 'socket';

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

  public function setAdapter($adapter)
  {
    $this->adapter = $adapter;
  }

  public function getAdapter()
  {
    return $this->adapter;
  }

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

  public function setBasePath($path)
  {
    $this->site->setPath($path);
  }

  public function getBasePath()
  {
    return $this->site->getPath();
  }

  public function getUsername()
  {
    return $this->site->getUser();
  }

  public function getPassword()
  {
    return $this->site->getPassword();
  }

  public function getAuthType()
  {
    return $this->auth_type;
  }

  public function setAuth($username, $password, $auth_type = 'basic')
  {
    if (!in_array($auth_type, array('basic', 'digest')))
    {
      throw new \InvalidArgumentException(sprintf('Wrong Auth type: %s', $auth_type));
    }

    $this->site->setUserinfo($username, $password);
    $this->auth_type = $auth_type;
  }

  public function setHeaders(array $headers)
  {
    $this->headers = $headers;
  }

  public function setHeader($name, $value)
  {
    $this->headers[$name] = $value;
  }

  public function getHeader($name)
  {
    if (!isset($this->headers[$name]))
    {
      return null;
    }

    return $this->headers[$name];
  }

  public function getTimeout()
  {
    return $this->timeout;
  }

  public function setTimeout($timeout)
  {
    $this->timeout = intval($timeout);
  }

  public function getFormat()
  {
    return $this->format;
  }

  public function setFormat(Format $format)
  {
    $this->format = $format;
  }

  public function get($path, array $headers = array())
  {
    $response = $this->send('get', $path, null, $headers);

    return $response;
  }

  public function head($path, array $headers = array())
  {
    $response = $this->send('head', $path, null, $headers);

    return $response;
  }

  public function delete($path, array $headers = array())
  {
    $response = $this->send('delete', $path, null, $headers);

    return $response;
  }

  public function put($path, array $body = array(), array $headers = array())
  {
    $body = $this->format->encode($body);
    $response = $this->send('put', $path, $body, $headers);

    return $response;
  }

  public function post($path, array $body = array(), array $headers = array())
  {
    $body = $this->format->encode($body);
    $response = $this->send('post', $path, $body, $headers);

    return $response;
  }

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

  protected function prepareUrl($path)
  {
    $site = new \Net_URL2($this->getSite());
    $site->setPath(
      $this->getBasePath() . strtr($path, array(':extension:' => $this->format->getExtension()))
    );

    return $site->getUrl();
  }

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

  protected function prepareResponse(\HTTP_Request2_Response $response)
  {  
    $body     = trim($response->getBody());
    $decoded  = strlen($body) ? $this->format->decode($body) : '';
    $response = new Response($response, $decoded);

    return $response;
  }
}
