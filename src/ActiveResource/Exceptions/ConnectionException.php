<?php

namespace ActiveResource\Exceptions;

class ConnectionException extends \Exception
{
  protected $response;

  public function __construct($response, $message = null)
  {  
    parent::__construct($message);

    $this->response = $response;
  }

  public function getResponse()
  {
    return $this->response;
  }

  public function __toString()
  {
    $message = sprintf('%s: %s.', get_class($this), $this->message);

    if (is_object($this->response) && method_exists($this->response, 'getStatus'))
    {
      $message .= sprintf('  Response code = %s', $this->response->getStatus());
    }

    if (is_object($this->response) && method_exists($this->response, 'getReasonPhrase'))
    {
      $message .= sprintf('  Response message = %s', $this->response->getReasonPhrase());
    }

    return $message;
  }
}
