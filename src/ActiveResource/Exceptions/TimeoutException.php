<?php

namespace ActiveResource\Exceptions;

use ActiveResource\Exceptions\ConnectionException;

class TimeoutException extends ConnectionException
{
  public function __toString()
  {
    return $this->message;
  }
}
