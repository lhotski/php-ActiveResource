<?php

namespace ActiveResource\Schemas;

interface Schema
{
    public function setAttributes(array $attrs);
    public function setAttribute($name, $type);

    public function isDefined();
    public function hasAttribute($name);

    public function getAttributeType($name);
    public function isAttributeType($name, $type);

    public function set($name, $value = null);
    public function get($name);
    public function setValues(array $values);
    public function getValues();
}
