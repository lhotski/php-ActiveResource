<?php

/*
 * This file is part of the php-ActiveResource.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ActiveResource\Formats;

interface Format
{
  public function getExtension();
  public function getMimeType();
  public function encode(array $attrs);
  public function decode($xml);
}
