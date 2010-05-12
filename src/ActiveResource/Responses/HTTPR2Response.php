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

/**
 * HTTPR2Response implements Response interface, by parsing HTTP_Request2_Response object
 *
 * @package    ActiveResource
 * @subpackage Responses
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 * @version    1.0.0
 */
class HTTPR2Response implements Response
{
  protected $code;
  protected $headers;
  protected $body;
  protected $decoded;

  /**
   * Response constructor
   *
   * @param   HTTP_Request2_Response  $response HTTP_Request2 response object
   * @param   array                   $decoded  decoded response body
   */
  public function __construct(\HTTP_Request2_Response $response, array $decoded)
  {
    $this->code     = $response->getStatus();
    $this->headers  = $response->getHeader();
    $this->body     = $response->getBody();
    $this->decoded  = $decoded;
  }

  /**
   * Returns HTTP status code of the response
   *
   * @see     ActiveResource\Responses\Response::getCode()
   */
  public function getCode()
  {
    return $this->code;
  }

  /**
   * Returns value of the specified header
   *
   * @see     ActiveResource\Responses\Response::getHeader()
   */
  public function getHeader($name)
  {
    $name = strtolower($name);

    return isset($this->headers[$name]) ? $this->headers[$name] : null;
  }

  /**
   * Returns all headers
   *
   * @see     ActiveResource\Responses\Response::getHeaders()
   */
  public function getHeaders()
  {
    return $this->headers;
  }

  /**
   * Returns decoded body of the response
   *
   * @see     ActiveResource\Responses\Response::getDecodedBody()
   */
  public function getDecodedBody()
  {
    return $this->decoded;
  }

  /**
   * Returns body of the response
   *
   * @see     ActiveResource\Responses\Response::getBody()
   */
  public function getBody()
  {
    return $this->body;
  }
}
