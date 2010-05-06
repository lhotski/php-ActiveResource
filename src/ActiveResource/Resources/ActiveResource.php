<?php

/*
 * This file is part of the php-ActiveResource.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ActiveResource\Resources;

use ActiveResource\Connections\Connection;

/**
 * Resource implements reource holder interface.
 *
 * @package    ActiveResource
 * @subpackage managers
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 * @version    1.0.0
 */
class ActiveResource
{
  protected $resource_class;
  protected $connection;

  public function __construct($resource_class, Connection $connection)
  {
    $this->resource_class = $resource_class;
    $this->connection = $connection;
  }

  public function __call($func, array $args)
  {
    $args[] = $this->connection;

    return call_user_func_array(array($this->resource_class, $func), $args);
  }
}
