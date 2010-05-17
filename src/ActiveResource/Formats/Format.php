<?php

/*
 * This file is part of the php-ActiveResource.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ActiveResource\Formats;

/**
 * Format interface describes request/response formatter
 *
 * @package    ActiveResource
 * @subpackage Formats
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 * @version    1.0.0
 */
interface Format
{
  /**
   * Returns format extension for resource
   *
   * @return  string  resource format extension
   */
  public function getExtension();

  /**
   * Returns format MIME type for resource
   *
   * @return  string  request/response MIME type
   */
  public function getMimeType();

  /**
   * Encodes object values to resource attributes
   *
   * @param   array   $attrs  object values
   * 
   * @return  string          request body
   */
  public function encode(array $attrs);

  /**
   * Decodes resource attributes to object values
   *
   * @param   string  $body response body
   * 
   * @return  array         object values
   */
  public function decode($body);
}
