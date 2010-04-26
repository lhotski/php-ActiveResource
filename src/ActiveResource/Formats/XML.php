<?php

namespace ActiveResource\Formats;

use ActiveResource\Formats\Format;
use ActiveResource\Ext\Inflector;

class XML implements Format
{
  public function getExtension()
  {
    return 'xml';
  }

  public function getMimeType()
  {
    return 'application/xml';
  }

  public function encode(array $attrs)
  {
    $xml = $this->arrayToNode($attrs);

    $dom  = new \DOMDocument('1.0');
    $dom->preserveWhitespace = true;
    $dom->formatOutput = true;
    $dom->loadXML($xml);

    return trim($dom->saveXML($dom->documentElement));
  }

  public function decode($xml)
  {
    $elements = new \SimpleXMLElement($xml);

    return $this->nodeToArray($elements);
  }

  private function endash($underscored_string)
  {
    return strtr($underscored_string, array('_' => '-'));
  }

  private function dedash($dashed_string)
  {
    return strtr($dashed_string, array('-' => '_'));
  }

  private function arrayToNode(array $array, $array_node = false)
  {
    $xml = '';

    foreach ($array as $name => $value)
    {
      $xml_name = false === $array_node ? $name : Inflector::singularize($array_node);
      $xml_name = $this->endash($xml_name);

      if (is_int($value))
      {
        $node = sprintf('<%s type="integer">%d</%s>',
          $xml_name, $value, $xml_name
        );
      }
      elseif (is_bool($value))
      {
        $node = sprintf('<%s type="boolean">%s</%s>',
          $xml_name, $value ? 'true' : 'false', $xml_name
        );
      }
      elseif (is_float($value))
      {
        $node = sprintf('<%s type="float">%s</%s>',
          $xml_name, floatval($value), $xml_name
        );
      }
      elseif (null === $value)
      {
        $node = sprintf('<%s/>',
          $xml_name, $xml_name
        );
      }
      elseif (is_array($value) && !Inflector::isHash($value))
      {
        $node = sprintf('<%s type="array">%s</%s>',
          $xml_name, $this->arrayToNode($value, $xml_name), $xml_name
        );
      }
      elseif (is_array($value))
      {
        $node = sprintf('<%s>%s</%s>',
          $xml_name, $this->arrayToNode($value), $xml_name
        );
      }
      elseif (false !== strtotime($value))
      {
        $node = sprintf('<%s type="datetime">%s</%s>',
          $xml_name, date('c', strtotime($value)), $xml_name
        );
      }
      else
      {
        $node = sprintf('<%s>%s</%s>',
          $xml_name, $value, $xml_name
        );
      }

      $xml .= $node;
    }

    return $xml;
  }

  private function nodeToArray(\SimpleXMLElement $element, $is_array = false)
  {
    $data = array();
    $a_counter = 0;

    foreach ($element->xpath('../*') as $node)
    {
      $name = $is_array ? $a_counter++ : $this->dedash($node->getName());

      switch ($node['type'])
      {
        case 'integer':
          $data[$name] = intval($node);
          break;
        case 'boolean':
          $data[$name] = false !== stripos($node, 'true');
          break;
        case 'float':
        case 'decimal':
        case 'double':
          $data[$name] = floatval($node);
          break;
        case 'date':
        case 'datetime':
          $data[$name] = date('c', strtotime($node));
          break;
        case 'array':
          $data[$name] = $this->nodeToArray($node->children(), true);
          break;
        default:
          if (count($node->children()))
          {
            $data[$name] = $this->nodeToArray($node->children());
          }
          else
          {
            $data[$name] = (string)$node;
          }
      }
    }

    return $data;
  }
}
