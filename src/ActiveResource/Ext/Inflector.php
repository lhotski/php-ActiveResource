<?php

/*
 * This file is part of the php-ActiveResource.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ActiveResource\Ext;

/**
 * Inflector implements core library functions for AR
 *
 * @package    ActiveResource
 * @subpackage Ext
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 * @version    1.0.0
 */
class Inflector
{
    /**
     * Checks if array is hash
     *
     * @param   array   $value  array to check
     * 
     * @return  boolean         true if array is hash
     */
    public static function isHash(array $value)
    {
      return !is_int(end(array_keys($value)));
    }

    /**
     * Underscores & prepares class name to become string
     *
     * @param   string  $class  class name
     * 
     * @return  string          undersored class name
     */
    public static function underscoreClassName($class)
    {
      return self::underscore(end(explode('\\', $class)));
    }

    /**
     * Underscores camelCased word
     *
     * @param   string  $camelCasedWord camelCasedWord 
     * 
     * @return  string                  underscored_word
     */
    public static function underscore($camelCasedWord)
    {
      $tmp = $camelCasedWord;
      $tmp = str_replace('::', '/', $tmp);
      $tmp = self::pregtr($tmp, array('/([A-Z]+)([A-Z][a-z])/'  => '\\1_\\2',
                                           '/([a-z\d])([A-Z])/' => '\\1_\\2'));

      return strtolower($tmp);
    }

    /**
     * Replaces string with patterns by RegExp's
     *
     * @param   string  $search       searchable string
     * @param   string  $replacePairs replace pairs of pattern => replace
     * 
     * @return  string                new string
     */
    public static function pregtr($search, $replacePairs)
    {
      return preg_replace(array_keys($replacePairs), array_values($replacePairs), $search);
    }

    /**
    * Pluralizes English noun.
    * 
    * @param  string  $word english noun to pluralize
    * 
    * @return string        plural noun
    */
    public static function pluralize($word)
    {
      $plurals = array(
        '/(quiz)$/i'                => '\1zes',
        '/^(ox)$/i'                 => '\1en',
        '/([m|l])ouse$/i'           => '\1ice',
        '/(matr|vert|ind)ix|ex$/i'  => '\1ices',
        '/(x|ch|ss|sh)$/i'          => '\1es',
        '/([^aeiouy]|qu)ies$/i'     => '\1y',
        '/([^aeiouy]|qu)y$/i'       => '\1ies',
        '/(hive)$/i'                => '\1s',
        '/(?:([^f])fe|([lr])f)$/i'  => '\1\2ves',
        '/sis$/i'                   => '\1ses',
        '/([ti])um$/i'              => '\1a',
        '/(buffal|tomat)o$/i'       => '\1oes',
        '/(bu)s$/i'                 => '\1ses',
        '/(alias|status)/i'         => '\1es',
        '/(octop|vir)us$/i'         => '\1i',
        '/(ax|test)is$/i'           => '\1es',
        '/s$/i'                     => 's',
        '/$/'                       => 's'
      );
      $uncountables = array(
        'equipment', 'information', 'rice', 'species', 'series', 'fish', 'sheep'
      );
      $irregulars = array(
        'person'    => 'people',
        'man'       => 'men',
        'child'     => 'children',
        'sex'       => 'sexes',
        'move'      => 'moves',
        'atlas'     => 'atlases',
        'corpus'    => 'corpuses',
        'genus'     => 'genera',
        'graffito'  => 'graffiti',
        'loaf'      => 'loaves',
        'money'     => 'monies',
        'mythos'    => 'mythoi',
        'numen'     => 'numina',
        'penis'     => 'penises',
        'soliloquy' => 'soliloquies',
        'turf'      => 'turfs'
      );
      $lowerCasedWord = strtolower($word);
      foreach ($uncountables as $uncountable) {
        if(substr($lowerCasedWord, (-1 * strlen($uncountable))) == $uncountable) {
          return $word;
        }
      }
      foreach ($irregulars as $plural => $singular) {
        if (preg_match('/(' . $plural . ')$/i', $word, $arr)) {
          return preg_replace(
            '/(' . $plural . ')$/i',
            substr($arr[0], 0, 1) . substr($singular, 1),
            $word
          );
        }
      }
      foreach ($plurals as $rule => $replacement) {
        if (preg_match($rule, $word)) {
          return preg_replace($rule, $replacement, $word);
        }
      }

      return false;
    }

    /**
    * Singularizes English plural.
    * 
    * @param  string  $word English noun to singularize
    * 
    * @return string        Singular noun.
    */
    public static function singularize($word)
    {
      $singulars = array(
        '/(quiz)zes$/i'         => '\1',
        '/(matr)ices$/i'        => '\1ix',
        '/(vert|ind)ices$/i'    => '\1ex',
        '/^(ox)en/i'            => '\1',
        '/(alias|status)es$/i'  => '\1',
        '/([octop|vir])i$/i'    => '\1us',
        '/(cris|ax|test)es$/i'  => '\1is',
        '/(shoe)s$/i'           => '\1',
        '/(o)es$/i'             => '\1',
        '/(bus)es$/i'           => '\1',
        '/([m|l])ice$/i'        => '\1ouse',
        '/(x|ch|ss|sh)es$/i'    => '\1',
        '/(m)ovies$/i'          => '\1ovie',
        '/(s)eries$/i'          => '\1eries',
        '/([^aeiouy]|qu)ies$/i' => '\1y',
        '/([lr])ves$/i'         => '\1f',
        '/(tive)s$/i'           => '\1',
        '/(hive)s$/i'           => '\1',
        '/([^f])ves$/i'         => '\1fe',
        '/(^analy)ses$/i'       => '\1sis',
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
        '/([ti])a$/i'           => '\1um',
        '/(n)ews$/i'            => '\1ews',
        '/s$/i'                 => '',
      );
      $uncountables = array(
        'equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep'
      );
      $irregulars = array(
        'person'    => 'people',
        'man'       => 'men',
        'child'     => 'children',
        'sex'       => 'sexes',
        'move'      => 'moves',
        'atlas'     => 'atlases',
        'corpus'    => 'corpuses',
        'genus'     => 'genera',
        'genie'     => 'genies',
        'graffito'  => 'graffiti',
        'loaf'      => 'loaves',
        'money'     => 'monies',
        'mythos'    => 'mythoi',
        'niche'     => 'niches',
        'numen'     => 'numina',
        'penis'     => 'penises',
        'soliloquy' => 'soliloquies',
        'turf'      => 'turfs'
      );
      $lowerCasedWord = strtolower($word);
      foreach ($uncountables as $uncountable) {
        if (substr($lowerCasedWord, (-1 * strlen($uncountable))) == $uncountable) {
          return $word;
        }
      }
      foreach ($irregulars as $plural => $singular) {
        if (preg_match('/(' . $singular.')$/i', $word, $arr)) {
          return preg_replace(
            '/(' . $singular . ')$/i',
            substr($arr[0], 0, 1) . substr($plural, 1),
            $word
          );
        }
      }
      foreach ($singulars as $rule => $replacement) {
        if (preg_match($rule, $word)) {
          return preg_replace($rule, $replacement, $word);
        }
      }

      return $word;
    }

    /**
     * Return human-readable string from lower case and underscored string
     *
     * @param string $lowerCaseAndUnderscoredWord
     *
     * @return string Humanized string
     */
    public static function humanize($lowerCaseAndUnderscoredWord)
    {
      $replace = ucwords(str_replace('_', ' ',
        preg_replace('/_id$/', '', $lowerCaseAndUnderscoredWord)));

      return $replace;
    }

}
