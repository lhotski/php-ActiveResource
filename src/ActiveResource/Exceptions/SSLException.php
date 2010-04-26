<?php

namespace ActiveResource\Exceptions;

use ActiveResource\Exceptions\ConnectionException;

class SSLException extends ConnectionException
{
  public function __toString()
  {
    return $this->message;
  }
}
