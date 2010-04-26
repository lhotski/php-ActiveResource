<?php

namespace ActiveResource\Formats;

interface Format
{
  public function getExtension();
  public function getMimeType();
  public function encode(array $attrs);
  public function decode($xml);
}
