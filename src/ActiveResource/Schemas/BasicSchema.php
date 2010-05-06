<?php

namespace ActiveResource\Schemas;

class BasicSchema implements Schema
{
  protected $available_attrs = array();
  protected $default_attr_type = 'string';

  public function __construct(array $attrs = array())
  {
    $this->setAttributes($attrs);
  }

  public function setAttributes(array $attrs)
  {
    foreach($attrs as $name => $type)
    {
      $this->setAttribute($name, $type);
    }
  }

  public function setAttribute($name, $type)
  {
    $this->available_attrs[$name] = $type;
  }

  public function isAttributesSet()
  {
    return !empty($this->available_attrs);
  }

  public function hasAttribute($name)
  {
    if (!$this->isAttributesSet())
    {
      return true;
    }

    return isset($this->available_attrs[$name]);
  }

  public function getAttributeType($name)
  {
    if ($this->isAttributesSet())
    {
      return $thid->default_attr_type;
    }

    if (!$this->hasAttribute($name))
    {
      throw new \InvalidArgumentException(sprintf('No such attribute: %s', $name));
    }

    return $this->available_attrs[$name];
  }

  public function isAttributeType($name, $type)
  {
    if ($this->isAttributesSet())
    {
      return $type === $thid->default_attr_type;
    }

    if (!$this->hasAttribute($name))
    {
      throw new \InvalidArgumentException(sprintf('No such attribute: %s', $name));
    }

    return $type === $this->getAttributeType($name);
  }

  public function prepareAttrForSet($name, $value)
  {
    if (!$this->hasAttribute($name))
    {
      throw new \InvalidArgumentException(sprintf('No such attribute: %s', $name));
    }

    
  }

  public function prepareAttrForGet($name, $value)
  {
    if (!$this->hasAttribute($name))
    {
      throw new \InvalidArgumentException(sprintf('No such attribute: %s', $name));
    }

    
  }
}
