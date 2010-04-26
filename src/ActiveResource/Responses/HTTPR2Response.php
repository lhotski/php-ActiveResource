<?php

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
