<?php

namespace ActiveResource\Exceptions;

use cdyweb\http\Exception\RequestException;

class ConnectionException extends RequestException
{
    public function __construct($message = null)
    {
        if ($message instanceof RequestException) {
            parent::__construct($message->getMessage(), $message->getRequest(), $message->getResponse(), $message);
        } else if ($message instanceof \Exception) {
            parent::__construct($message->getMessage(), null, null, $message);
        } else {
            parent::__construct($message, null, null, null);
        }
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
