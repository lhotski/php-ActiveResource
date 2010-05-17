<?php

/*
 * This file is part of the php-ActiveResource.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ActiveResource\Responses;

/**
 * Response interface describes base AR response object
 *
 * @package     ActiveResource
 * @subpackage  Responses
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 * @version     1.0.0
 */
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
}
