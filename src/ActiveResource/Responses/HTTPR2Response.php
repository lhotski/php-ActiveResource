<?php

/*
 * This file is part of the php-ActiveResource.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ActiveResource\Responses;

require_once 'HTTP/Request2/Response.php';

class HTTPR2Response implements Response
{
  protected $code;
  protected $headers;
  protected $body;
  protected $decoded;

  public function __construct(\HTTP_Request2_Response $response, $decoded)
  {
    $this->code = $response->getStatus();
    $this->headers = $response->getHeader();
    $this->body = $response->getBody();
    $this->decoded = $decoded;
  }

  public function getCode()
  {
    return $this->code;
  }

  public function getHeader($name)
  {
    $name = strtolower($name);

    return isset($this->headers[$name])? $this->headers[$name]: null;
  }

  public function getHeaders()
  {
    return $this->headers;
  }

  public function getDecodedBody()
  {
    return $this->decoded;
  }

  public function getBody()
  {
    return $this->body;
  }
}
