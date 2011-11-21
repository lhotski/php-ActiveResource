<?php

namespace ActiveResource\Errors;

class RemoteErrors extends Errors
{

  public function loadFromArray(array $array)
  {
    $this->clear();

    foreach($array as $k => $v)
    {
      // TODO: extract attributes from entity schema
      $this->add('base', $v);
    }
  }

  public function loadFromXML($body)
  {
    $a = array();
    try
    {
      $xml = new \SimpleXMLElement($body);
      foreach($xml->xpath('/errors/error') as $error)
      {
        $a[] = (string)$error;
      }
    }
    catch (\Exception $e) {}

    $this->loadFromArray($a);
  }

  public function loadFromJSON($json)
  {
    // not implemented yet
  }

}
