<?php

namespace ActiveResource\Schemas;

interface Schema
{
  public function __construct(array $attrs = array());
  public function setAttributes(array $attrs);
  public function setAttribute($name, $type);

  public function isAttributesSet();
  public function hasAttribute($name);

  public function getAttributeType($name);
  public function isAttributeType($name, $type);

  public function prepareAttrForSet($name, $value);
  public function prepareAttrForGet($name, $value);
}
