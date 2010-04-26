<?php

namespace ActiveResource\Exceptions;

use ActiveResource\Exceptions\ConnectionException;

class Redirection extends ConnectionException
{
  public function __toString()
  {
    if (is_object($this->response) && method_exists($this->response, 'getHeader'))
    {
      return sprintf('%s => %s', get_class($this), $this->response->getHeader('location'));
    }
    else
    {
      return get_class($this);
    }
  }
}
