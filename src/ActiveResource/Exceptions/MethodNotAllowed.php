<?php

namespace ActiveResource\Exceptions;

use ActiveResource\Exceptions\ConnectionException;

class MethodNotAllowed extends ConnectionException
{
  public function getAllowedMethods()
  {
    if (is_object($this->response) && method_exists($this->response, 'getHeader'))
    {
      return explode(',', $this->response->getHeader('Allow'));
    }
  }
}
