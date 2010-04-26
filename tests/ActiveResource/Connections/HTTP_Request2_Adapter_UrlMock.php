<?php

require_once 'HTTP/Request2/Adapter/Mock.php';

class HTTP_Request2_Adapter_UrlMock extends HTTP_Request2_Adapter_Mock
{
  private function generateUrl($method, $url, $body = null)
  {
    return sprintf('%s: %s / %s', strtoupper($method), $url, md5(trim($body)));
  }

  public function addResponse($method, $url, $body, $response)
  {
    $url = $this->generateUrl($method, $url);

    if (is_string($response))
    {
      $response = self::createResponseFromString($response);
    }
    elseif (is_resource($response))
    {
      $response = self::createResponseFromFile($response);
    }
    elseif (!$response instanceof HTTP_Request2_Response &&
            !$response instanceof Exception)
    {
      throw new HTTP_Request2_Exception('Parameter is not a valid response');
    }

    if (!isset($this->responses[$url]))
    {
      $this->responses[$url] = array();
    }

    $this->responses[$url][] = $response;
  }

  public function sendRequest(HTTP_Request2 $request)
  {
    $url = $this->generateUrl($request->getMethod(), $request->getUrl()->getUrl());

    if (isset($this->responses[$url]))
    {
      $response = array_shift($this->responses[$url]);

      if (empty($this->responses[$url]))
      {
        unset($this->responses[$url]);
      }

      if ($response instanceof HTTP_Request2_Response)
      {
        return $response;
      }
      else
      {
        // rethrow the exception
        $class   = get_class($response);
        $message = $response->getMessage();
        $code  = $response->getCode();

        throw new $class($message, $code);
      }
    }
    else
    {
      return $this->throwWrongRequestException($request);
    }
  }

  private function throwWrongRequestException(HTTP_Request2 $request)
  {
    $message = sprintf("\n\nCan't find: %s\nAvailable: \n%s",
      $this->generateUrl($request->getMethod(), $request->getUrl()->getUrl(), $request->getBody()),
      var_export(array_keys($this->responses), true)
    );

    throw new ActiveResource\Exceptions\BadRequest(null, $message);
  }
}
