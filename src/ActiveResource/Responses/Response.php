<?php

namespace ActiveResource\Responses;

interface Response
{
  /**
   * Returns HTTP status code of the response
   *
   * @return integer  HTTP status code
   */
  public function getCode();

  /**
   * Returns value of the specified header
   *
   * @param   string  $name header name
   * 
   * @return  string        header value
   */
  public function getHeader($name);

  /**
   * Returns headers array
   *
   * @return  array         headers
   */
  public function getHeaders();

  /**
   * Returns body of the response
   *
   * @return  string  response body
   */
  public function getBody();

  /**
   * Returns decoded body of the response
   *
   * @return  string  response body
   */
  public function getDecodedBody();
}
