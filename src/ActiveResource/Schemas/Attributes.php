<?php

namespace ActiveResource\Schemas;

use ActiveResource\Ext\Inflector;

class Attributes implements Schema
{
  protected $definitions = array();
  protected $values = array();

  public function setAttributes(array $attrs)
  {
    foreach ($attrs as $name => $type)
    {
      $this->setAttribute($name, $type);
    }
  }

  public function setAttribute($name, $type)
  {
    $this->definitions[$name] = $type;
  }

  public function isDefined()
  {
    return !empty($this->definitions);
  }

  public function hasAttribute($name)
  {
    return $this->isDefined() ? isset($this->definitions[$name]) : true;
  }

  public function getAttributeType($name)
  {
    if ($this->isDefined())
    {
      if (!$this->hasAttribute($name))
      {
        throw new \InvalidArgumentException(sprintf('Attribute %s doesn\'t exists', $name));
      }

      return $this->definitions[$name];
    }
    else
    {
      if (isset($this->values[$name]))
      {
        return $this->guessType($this->values[$name]);
      }
      else
      {
        return 'string';
      }
    }
  }

  public function isAttributeType($name, $type)
  {
    return $type === $this->getAttributeType($name);
  }

  public function set($name, $value = null)
  {
    if (!$this->hasAttribute($name))
    {
      throw new \InvalidArgumentException(sprintf('Attribute %s doesn\'t exists', $name));
    }

    if (null === $value && isset($this->values[$name]))
    {
      unset($this->values[$name]);
    }

    $type = $this->isDefined() ? $this->getAttributeType($name) : $this->guessType($value);
    $this->values[$name] = $this->castTo($type, $value);
  }

  public function get($name)
  {
    if (!$this->hasAttribute($name))
    {
      throw new \InvalidArgumentException(sprintf('Attribute %s doesn\'t exists', $name));
    }

    if (!isset($this->values[$name]))
    {
      return null;
    }

    return $this->castFrom($this->getAttributeType($name), $this->values[$name]);
  }

  public function setValues(array $values)
  {
    foreach ($values as $name => $value)
    {
      $this->set($name, $value);
    }
  }

  public function getValuesArray()
  {
    $values = array();

    if ($this->isDefined())
    {
      foreach (array_keys($this->definitions) as $name)
      {
        $values[$name] = $this->get($name);
      }
    }
    else
    {
      foreach (array_keys($this->values) as $name)
      {
        $values[$name] = $this->get($name);
      }
    }

    return $values;
  }

  protected function guessType($value)
  {
    if (is_float($value))
    {
      return 'float';
    }
    elseif (is_bool($value))
    {
      return 'boolean';
    }
    elseif (is_array($value) && Inflector::isHash($value))
    {
      return 'hash';
    }
    elseif (is_array($value))
    {
      return 'array';
    }
    if (false !== strtotime($value))
    {
      return 'datetime';
    }
    if (is_int($value))
    {
      return 'integer';
    }
    elseif (is_string($value))
    {
      return 'string';
    }
    elseif (is_object($value))
    {
      return 'object';
    }
    else
    {
      return 'string';
    }
  }

  protected function castTo($type, $value)
  {
    switch ($type)
    {
      case 'integer':
        return intval($value);
        break;
      case 'float':
      case 'double':
        return floatval($value);
        break;
      case 'datetime':
      case 'date':
      case 'time':
        return strtotime($value);
        break;
      case 'boolean':
        return 'true' === strtolower($value) || 1 === intval($value);
        break;
      default:
        return $value;
    }
  }

  protected function castFrom($type, $value)
  {
    switch ($type)
    {
      case 'integer':
      case 'float':
        return strval($value);
        break;
      case 'datetime':
        return date('c', $value);
        break;
      case 'date':
        return date('Y-m-d', $value);
        break;
      case 'time':
        return date('H:i:s', $value);
        break;
      default:
        return $value;
    }
  }
}