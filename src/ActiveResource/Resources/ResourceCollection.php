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
 * ResourceCollection implements reources holder interface.
 *
 * @package    ActiveResource
 * @subpackage managers
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 * @version    1.0.0
 */
class ResourceCollection
{
  protected $resources_namespace = '';
  protected $connection;
  protected $collection = array();

  public function __construct($namespace, Connection $connection)
  {
    $this->resources_namespace = $namespace;
    $this->connection = $connection;
  }

  public function __get($name)
  {
    return $this->getResource($name);
  }

  public function getResource($name)
  {
    $class = $this->generateClassName($name);

    if (!$this->isLoaded($class))
    {
      $collection[$class] = $this->initResource($class);
    }

    return $collection[$class];
  }

  protected function generateClassName($name)
  {
    return $this->resources_namespace . '\\' . $name;
  }

  protected function isLoaded($class)
  {
    return isset($this->collection[$class]);
  }

  protected function initResource($class)
  {
    return new ActiveResource($class, $this->connection);
  }
}
