<?php

/*
 * This file is part of the php-ActiveResource.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ActiveResource;

use ActiveResource\Connections\Connection;
use ActiveResource\Schemas\Schema;
use ActiveResource\Schemas\AttrsSchema;
use ActiveResource\Responses\HTTPR2Response as Response;
use ActiveResource\Ext\Inflector;
use ActiveResource\Formats\Format;
use ActiveResource\Errors\RemoteErrors;

/**
 * Base implements base REST model abstraction class.
 *
 * @package    ActiveResource
 * @subpackage base
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 * @version    1.0.0
 */
abstract class Base
{  
  protected $id;
  protected $connection;
  protected $schema;
  protected $prefix_options = array();
  protected $errors;

  /**
   * Constructs new object
   *
   * @param   array                                   $attrs        object attributes
   * @param   ActiveResource\Connections\Connection   $connection   connection instance
   */
  public function __construct(array $attrs, Connection $connection)
  {
    $this->setConnection($connection);
    $this->setSchema(self::initSchema());

    $this->load($attrs);
  }

  /**
   * Sets current resource connection manager
   *
   * @param   ActiveResource\Connections\Connection   $connection   conneciton instance
   */
  public function setConnection(Connection $connection)
  {
    $this->connection = $connection;
  }

  /**
   * Returns current resource connection manager
   *
   * @return  ActiveResource\Connections\Connection
   */
  public function getConnection()
  {
    return $this->connection;
  }

  /**
   * Sets current resource attribute schema
   *
   * @param   ActiveResource\Schemas\Schema           $schema       schema instance
   */
  public function setSchema(Schema $schema)
  {
    $this->schema = $schema;
  }

  /**
   * Returns current resource schema
   *
   * @return  ActiveResource\Schemas\Schema
   */
  public function getSchema()
  {
    return $this->schema;
  }

  /**
   * Returns new scheme for resource (override this method to set your custom schema for resource)
   *
   * @return  ActiveResource\Schemas\Schema
   */
  protected static function initSchema()
  {
    return new AttrsSchema(
      self::schemaDefinition()
    );
  }

  /**
   * Returns schema definition, used to create new schema for resource (override for custom def.)
   *
   * @return  array
   */
  protected static function schemaDefinition()
  {
    return array();
  }

  /**
   * Returns new Formatter instance
   *
   * @return  ActiveResource\Formats\Format formatter instance
   */
  protected static function getFormat()
  {
    static $format;

    if (null === $format)
    {
      $format = self::initFormat();
    }

    return $format;
  }

  /**
   * Creates new Formatter instance
   *
   * @return  ActiveResource\Formats\Format formatter instance
   */
  protected static function initFormat()
  {
    $class = self::formatClass();

    return new $class;
  }

  /**
   * Returns Formatter class name
   *
   * @return  string                        formatter class
   */
  protected static function formatClass()
  {
    return 'ActiveResource\\Formats\\XML';
  }

  /**
   * Constructs & returns newly created object (*new* method in Ruby AR)
   *
   * @param   array                                   $attrs        object attributes
   * @param   ActiveResource\Connections\Connection   $connection   connection instance
   * 
   * @return  ActiveResource\Base
   */
  public static function init(array $attrs, Connection $connection)
  {
    return self::instantiateRecord($attrs, array(), $connection);
  }


  /**
   * Return remote errors
   *
   * @return ActiveResource\Errors\RemoteErrors
   */
  public function getErrors()
  {
    if (!$this->errors)
    {
      $this->errors = $this->initErrors();
    }
    return $this->errors;
  }

  /**
   * Init errors holder instance
   *
   * @return ActiveResource\Errors\RemoteErrors
   */
  protected function initErrors()
  {
    return new RemoteErrors(get_class());
  }

  /**
   * Sets default prefix options for object
   *
   * @param   array $options  prefix options
   */
  public function setPrefixOptions(array $options)
  {
    $this->prefix_options = $options;
  }

  /**
   * Return default object prefix options
   *
   * @return  array           prefix options
   */
  public function getPrefixOptions()
  {
    return $this->prefix_options;
  }

  /**
   * Populate object values with specified attributes
   *
   * @param   array $attrs  object attributes
   */
  public function load(array $attrs)
  {
    if (isset($attrs['id']))
    {
      $this->id = $attrs['id'];
      unset($attrs['id']);
    }

    $this->schema->setValues($attrs);
  }

  /**
   * Reloads object values from remote remote service
   * 
   */
  public function reload()
  {
    if ($this->isPersisted())
    {
      $this->load(
        $this->connection->get(self::getElementPath($this->getId()))
      );
    }
  }

  /**
   * Returns object id
   *
   * @return  integer id of the object
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Returns object attribute
   *
   * @param   string $name  attribute name
   * 
   * @return  mixed         attribute value
   */
  public function __get($name)
  {
    return $this->schema->get($name);
  }

  /**
   * Sets object attribute
   *
   * @param   string  $name   attribute name
   * @param   string  $value  attribute value
   */
  public function __set($name, $value)
  {
    $this->schema->set($name, $value);
  }

  /**
   * Returns object element name (override it if you need)
   *
   * @return  string          element name
   */
  public static function getElementName()
  {
    return Inflector::underscoreClassName(get_called_class());
  }

  /**
   * Returns object collection name (override it if you need)
   *
   * @return  string          collection name
   */
  public static function getCollectionName()
  {
    return Inflector::pluralize(self::getElementName());
  }

  /**
   * Checks whether object exists on remote service
   *
   * @param   integer                                 $id           object id
   * @param   ActiveResource\Connections\Connection   $connection   remote service connection
   * 
   * @return  boolean                                               true if exists, false otherwise
   */
  public static function isExists($id, array $prefix_options,
                                       array $query_options,
                                       Connection $connection)
  {
    try
    {
      try
      {
        $response = $connection->head(self::getElementPath($id, $prefix_options, $query_options));

        return 200 == $response->getCode();
      }
      catch (\ActiveResource\Exceptions\ResourceGone $e)
      {
        return false;
      }
    }
    catch (\ActiveResource\Exceptions\ResourceNotFound $e)
    {
      return false;
    }
  }

  /**
   * Sends new request to remote service & create new object with fictive data
   *
   * @param   ActiveResource\Connections\Connection  $connection   remote service connection
   * 
   * @return  ActiveResource\Base                                  new object
   */
  public static function build(array $prefix_options, array $query_options, Connection $connection)
  {
    $response = $connection->get(self::getNewElementPath($prefix_options, $query_options));
    $body     = self::getFormat()->decode($response->getBody());

    $attrs = $body[self::getElementName()];
    $class = get_called_class();

    return new $class($attrs, $connection);
  }

  /**
   * Checks if object is new (not saved to remote service)
   *
   * @return boolean  true if new, false otherways
   */
  public function isNew()
  {
    return null === $this->getId();
  }

  /**
   * Checks if object is saved to remote service
   *
   * @return  boolean  true if saved, false otherways
   */
  public function isPersisted()
  {
    return !$this->isNew();
  }

  /**
   * Saves current object to remote service (create if object is new & update otherways)
   *
   * @return  boolean true if saved, false otherways
   */
  public function save()
  {
    try
    {
      return $this->isNew() ? $this->create() : $this->update();
    }
    catch (\ActiveResource\Exceptions\ResourceInvalid $e)
    {
      $this->loadRemoteErrors(new Response($e->getResponse()));
      return false;
    }
  }

  /**
   * Removes current object from remote service
   *
   * @return  boolean true if succesfully destroyed, false otherways
   */
  public function destroy()
  {
    $response = $this->connection->delete(self::getElementPath($this->getId()));

    return 200 == $response->getCode();
  }

  /**
   * Core method for finding resources.
   *
   * ==== 1st argument:
   * 
   * integer - Returns resource with specified id
   * string - One of the specified scopes ('all', 'first', 'last', 'one')
   * array - First element must be is integer or scope, others - options
   * 
   * ==== Options:
   * 
   * 'from' - string, which specifies resource name or path
   * 'params' - prefix & query parameters
   * 
   * ==== Examples:
   * 
   * Person::find(1, $connection)
   * // => GET /people/1.xml
   * 
   * Person::find('all', $connection)
   * // => GET /people.xml
   * 
   * Person::find(array('all', 'params' => array('title' => 'CEO')), $connection)
   * // => GET /people.xml?title=CEO
   * 
   * Person::find(array('first', 'from' => 'managers'), $connection)
   * // => GET /people/managers.xml
   * 
   * Person::find(array('last', 'from' => 'managers'), $connection)
   * // => GET /people/managers.xml
   * 
   * Person::find(array('all', 'from' => '/companies/1/people.xml'), $connection)
   * // => GET /companies/1/people.xml
   * 
   * Person::find(array('one', 'from' => 'leader'), $connection)
   * // => GET /people/leader.xml
   * 
   * Person::find(array('all', 'from' => 'developers', params => array('lang' => 'php')), $connection)
   * // => GET /people/developers.xml?lang=php
   * 
   * Person::find(array('one', 'from' => '/companies/1/manager.xml'), $connection)
   * // => GET /companies/1/manager.xml
   * 
   * StreetAddress::find(array(1, 'params' => array('person_id' => 2)))
   * // => GET /people/2/street_addresses/1.xml
   * 
   * ==== Failure or missing data
   * 
   * A failure to find the requested object raises a ResourceNotFound
   * exception if the find was called with an id.
   * With any other scope, find returns nil when no data is returned.
   *
   * @param   integer|string|array                  $criteria   criteria of the query
   * @param   ActiveResource\Connections\Connection $connection remote resource connection
   * 
   * @return  array|ActiveResource\Base                         single object or array of objects
   */
  public static function find($criteria, Connection $connection)
  {
    if (!is_array($criteria))
    {
      $criteria = array($criteria);
    }

    switch ($scope = array_shift($criteria))
    {
      case 'one':   return self::findOne($criteria, $connection);
      case 'first': return array_shift(self::findEvery($criteria, $connection));
      case 'last':  return array_pop(self::findEvery($criteria, $connection));
      case 'all':   return self::findEvery($criteria, $connection);
      default:      return self::findSingle($scope, $criteria, $connection);
    }
  }

  public function elementGet($method_name, array $params = array())
  {
    list($prefix_options, $query_options) = self::splitParams($params);

    if ($this->isNew())
    {
      $response = $this->connection->get(
        self::getCustomMethodNewElementPath($method_name, $prefix_options, $query_options),
        array('accept' => self::getFormat()->getMimeType())
      );
    }
    else
    {
      $response = $this->connection->get(
        self::getCustomMethodElementPath(
          $method_name, $this->getId(), $prefix_options, $query_options
        ),
        array('accept' => self::getFormat()->getMimeType())
      );
    }
    $attrs = self::getFormat()->decode($response->getBody());

    return $attrs[end(array_keys($attrs))];
  }

  public static function collectionGet($method_name, array $params = array(),
                                       Connection $connection)
  {
    list($prefix_options, $query_options) = self::splitParams($params);

    $response = $connection->get(
      self::getCustomMethodCollectionPath($method_name, $prefix_options, $query_options),
      array('accept' => self::getFormat()->getMimeType())
    );
    $attrs = self::getFormat()->decode($response->getBody());

    return $attrs[end(array_keys($attrs))];
  }

  public function elementPost($method_name, array $params = array(), array $body = array())
  {
    list($prefix_options, $query_options) = self::splitParams($params);
    $body = self::getFormat()->encode(array('request' => $body));

    if ($this->isNew())
    {
      $response = $this->connection->post(
        self::getCustomMethodNewElementPath($method_name, $prefix_options, $query_options), $body,
        array(
          'accept'        => self::getFormat()->getMimeType(),
          'content-type'  => self::getFormat()->getMimeType()
        )
      );
    }
    else
    {
      $response = $this->connection->post(
        self::getCustomMethodElementPath(
          $method_name, $this->getId(), $prefix_options, $query_options
        ), $body,
        array(
          'accept'        => self::getFormat()->getMimeType(),
          'content-type'  => self::getFormat()->getMimeType()
        )
      );
    }

    return 201 === $response->getCode();
  }

  public static function collectionPost($method_name, array $params = array(),
                                                      array $body = array(), Connection $connection)
  {
    list($prefix_options, $query_options) = self::splitParams($params);
    $body = self::getFormat()->encode(array('request' => $body));

    $response = $connection->post(
      self::getCustomMethodCollectionPath($method_name, $prefix_options, $query_options), $body,
      array(
        'accept'        => self::getFormat()->getMimeType(),
        'content-type'  => self::getFormat()->getMimeType()
      )
    );

    return 201 === $response->getCode();
  }

  public function elementPut($method_name, array $params = array(), array $body = array())
  {
    list($prefix_options, $query_options) = self::splitParams($params);
    $body = self::getFormat()->encode(array('request' => $body));

    $response = $this->connection->put(
      self::getCustomMethodElementPath(
        $method_name, $this->getId(), $prefix_options, $query_options
      ), $body,
      array(
        'accept'        => self::getFormat()->getMimeType(),
        'content-type'  => self::getFormat()->getMimeType()
      )
    );

    return 204 === $response->getCode() || 200 === $response->getCode();
  }

  public static function collectionPut($method_name, array $params = array(),
                                                     array $body = array(), Connection $connection)
  {
    list($prefix_options, $query_options) = self::splitParams($params);
    $body = self::getFormat()->encode(array('request' => $body));

    $response = $connection->put(
      self::getCustomMethodCollectionPath($method_name, $prefix_options, $query_options), $body,
      array(
        'accept'        => self::getFormat()->getMimeType(),
        'content-type'  => self::getFormat()->getMimeType()
      )
    );

    return 204 === $response->getCode() || 200 === $response->getCode();
  }

  public function elementDelete($method_name, array $params = array())
  {
    list($prefix_options, $query_options) = self::splitParams($params);

    $response = $this->connection->delete(
      self::getCustomMethodElementPath(
        $method_name, $this->getId(), $prefix_options, $query_options
      ),
      array('accept' => self::getFormat()->getMimeType())
    );

    return 200 === $response->getCode();
  }

  public static function collectionDelete($method_name, array $params = array(),
                                          Connection $connection)
  {
    list($prefix_options, $query_options) = self::splitParams($params);

    $response = $connection->delete(
      self::getCustomMethodCollectionPath($method_name, $prefix_options, $query_options),
      array('accept' => self::getFormat()->getMimeType())
    );

    return 200 === $response->getCode();
  }

  public function elementHead($method_name, array $params = array())
  {
    list($prefix_options, $query_options) = self::splitParams($params);

    $response = $this->connection->head(
      self::getCustomMethodElementPath(
        $method_name, $this->getId(), $prefix_options, $query_options,
        array('accept' => self::getFormat()->getMimeType())
      )
    );

    return 200 === $response->getCode();
  }

  public static function collectionHead($method_name, array $params = array(),
                                        Connection $connection)
  {
    list($prefix_options, $query_options) = self::splitParams($params);

    $response = $connection->head(
      self::getCustomMethodCollectionPath($method_name, $prefix_options, $query_options),
      array('accept' => self::getFormat()->getMimeType())
    );

    return 200 === $response->getCode();
  }

  /**
   * Returns collection url path to objects
   *
   * @param   array $prefix_options prefix options
   * @param   array $query_options  query options
   * 
   * @return  string                collection path
   */
  protected static function getCollectionPath(array $prefix_options = array(),
                                              array $query_options = array())
  {
    return sprintf('%s%s.%s%s',
      self::getPrefix($prefix_options),
      self::getCollectionName(),
      self::getFormat()->getExtension(),
      self::getQueryString($query_options)
    );
  }

  /**
   * Returns collection url path to objects
   *
   * @param   array $prefix_options prefix options
   * @param   array $query_options  query options
   * 
   * @return  string                collection path
   */
  protected static function getCustomMethodCollectionPath($method_name,
                                                          array $prefix_options = array(),
                                                          array $query_options = array())
  {
    return sprintf('%s%s/%s.%s%s',
      self::getPrefix($prefix_options),
      self::getCollectionName(),
      $method_name,
      self::getFormat()->getExtension(),
      self::getQueryString($query_options)
    );
  }

  /**
   * Returns url path to object with specified id
   *
   * @param   integer $id             id of the object
   * @param   array   $prefix_options prefix options
   * @param   array   $query_options  query options
   * 
   * @return  string                  element path
   */
  protected static function getElementPath($id, array $prefix_options = array(),
                                                array $query_options = array())
  {
    return sprintf('%s%s/%d.%s%s',
      self::getPrefix($prefix_options),
      self::getCollectionName(),
      $id,
      self::getFormat()->getExtension(),
      self::getQueryString($query_options)
    );
  }

  /**
   * Returns url path to object with specified id
   *
   * @param   integer $id             id of the object
   * @param   array   $prefix_options prefix options
   * @param   array   $query_options  query options
   * 
   * @return  string                  element path
   */
  protected static function getCustomMethodElementPath($method_name, $id,
                                                       array $prefix_options = array(),
                                                       array $query_options = array())
  {
    return sprintf('%s%s/%d/%s.%s%s',
      self::getPrefix($prefix_options),
      self::getCollectionName(),
      $id,
      $method_name,
      self::getFormat()->getExtension(),
      self::getQueryString($query_options)
    );
  }

  /**
   * Returns url path to new object
   *
   * @param   array   $prefix_options prefix options
   * @param   array   $query_options  query options
   * 
   * @return  string                  new element path
   */
  protected static function getNewElementPath(array $prefix_options = array(),
                                              array $query_options = array())
  {
    return sprintf('%s%s/new.%s%s',
      self::getPrefix($prefix_options),
      self::getCollectionName(),
      self::getFormat()->getExtension(),
      self::getQueryString($query_options)
    );
  }

  /**
   * Returns url path to new object
   *
   * @param   array   $prefix_options prefix options
   * @param   array   $query_options  query options
   * 
   * @return  string                  new element path
   */
  protected static function getCustomMethodNewElementPath($method_name,
                                                          array $prefix_options = array(),
                                                          array $query_options = array())
  {
    return sprintf('%s%s/new/%s.%s%s',
      self::getPrefix($prefix_options),
      self::getCollectionName(),
      $method_name,
      self::getFormat()->getExtension(),
      self::getQueryString($query_options)
    );
  }

  protected static function findEvery(array $args = array(), Connection $connection)
  {
    try
    {
      list($from, $prefix_options, $query_options) = self::extractOptions($args);

      if (null === $from)
      {
        $response = $connection->get(self::getCollectionPath($prefix_options, $query_options));
        $decoded  = self::getFormat()->decode($response->getBody());
        $attrs    = (isset($decoded[self::getCollectionName()])
                      && is_array($decoded[self::getCollectionName()]))
                        ? $decoded[self::getCollectionName()]
                        : array();
      }
      elseif (false !== strpos($from, '/'))
      {
        $path     = sprintf('%s%s', $from, self::getQueryString($query_options));
        $response = $connection->get($path);
        $decoded  = self::getFormat()->decode($response->getBody());
        $attrs    = $decoded[self::getCollectionName()];
      }
      else
      {
        $attrs    = self::collectionGet(
          $from, array_merge($prefix_options, $query_options), $connection
        );
      }

      return self::instantiateCollection($attrs, $prefix_options, $connection);
    }
    catch (\ActiveResource\Exceptions\ResourceNotFound $e)
    {
      return null;
    }
  }

  protected static function findOne(array $args = array(), Connection $connection)
  {
    try
    {
      list($from, $prefix_options, $query_options) = self::extractOptions($args);

      if (false !== strpos($from, '/'))
      {
        $path     = sprintf('%s%s', $from, self::getQueryString($query_options));
        $response = $connection->get($path);
        $decoded  = self::getFormat()->decode($response->getBody());
        $attrs    = $decoded[self::getElementName()];
      }
      else
      {
        $attrs    = self::collectionGet(
          $from, array_merge($prefix_options, $query_options), $connection
        );
      }

      return self::instantiateRecord($attrs, $prefix_options, $connection);
    }
    catch (\ActiveResource\Exceptions\ResourceNotFound $e)
    {
      return null;
    }
  }

  protected static function findSingle($id, array $args = array(), Connection $connection)
  {
    try
    {
      list($from, $prefix_options, $query_options) = self::extractOptions($args);

      $response = $connection->get(self::getElementPath($id, $prefix_options, $query_options));
      $decoded  = self::getFormat()->decode($response->getBody());
      $attrs    = $decoded[self::getElementName()];

      return self::instantiateRecord($attrs, $prefix_options, $connection);
    }
    catch (\ActiveResource\Exceptions\ResourceNotFound $e)
    {
      return null;
    }
  }

  /**
   * Create current resource on remote service.
   * 
   * @return  boolean true if created, false otherways
   */
  protected function create()
  {
    $prepared_attrs = array();
    $prepared_attrs[$this->getElementName()] = $this->schema->getValues();

    $response = $this->connection->post($this->getCollectionPath(),
      self::getFormat()->encode($prepared_attrs), array(
        'accept'        => self::getFormat()->getMimeType(),
        'content-type'  => self::getFormat()->getMimeType()
      )
    );

    if (201 == $response->getCode())
    {
      $this->id = $this->getIdFromResponse($response);
      $this->loadAttributesFromResponse($response);
      
      return null !== $this->id;
    }

    return false;
  }

  /**
   * Updates current resource on remote service
   *
   * @return  boolean true if updated, false otherways
   */
  protected function update()
  {
    $prepared_attrs = array();
    $prepared_attrs[$this->getElementName()] = $this->schema->getValues();

    $response = $this->connection->put($this->getElementPath($this->getId()),
        self::getFormat()->encode($prepared_attrs), array(
            'accept' => self::getFormat()->getMimeType(),
            'content-type' => self::getFormat()->getMimeType()
        )
    );
    $this->loadAttributesFromResponse($response);

    return 204 == $response->getCode() || 200 == $response->getCode();
  }


  /**
   * Returns url prefix, generated from the prefix options
   *
   * @param   array   $options  prefix options
   * 
   * @return  string            generated prefix
   */
  protected static function getPrefix(array $options)
  {
    $prefix = '/';

    foreach ($options as $name => $value)
    {
      $collection = Inflector::pluralize(strtr($name, array('_id' => '')));
      $prefix .= sprintf('%s/%d/', $collection, $value);
    }

    return $prefix;
  }

  /**
   * Returns url query string, generated from query options
   *
   * @param   array   $options  query options
   * 
   * @return  string            generated query string
   */
  protected static function getQueryString(array $options)
  {
    $query_options = array();

    foreach ($options as $name => $value)
    {
      if (is_array($value))
      {
        $query_options = array_merge($query_options,
          self::buildQueryArray($name, $value));
      }
      else
      {
        $query_options[] = urlencode($name) . '=' . urlencode($value);
      }
    }

    return !empty($query_options) ? '?' . implode('&', $query_options) : '';
  }

  /**
   * Recursive build query parts from array
   *
   * @param   string    $name       argument name
   * @param   array     $options    argument value
   *
   * @return  array                 array of query pairs
   */
  protected static function buildQueryArray($name, array $options)
  {
    $pairs = array();
    foreach($options as $key => $value)
    {
      if (is_array($value))
      {
        $pairs = array_merge($pairs, self::buildQueryArray($key, $value));
      }
      else
      {
        $pairs[] = urlencode($name . '[' . (is_int($key) ? '' : $key) . ']')
          . '=' . urlencode($value);
      }
    }
    return $pairs;
  }

  /**
   * Returns new object, populated with array data
   *
   * @param   array                                 $attrs              attributes
   * @param   array                                 $prefix_options     prefix options for object
   * @param   ActiveResource\Connections\Connection $connection         remote service connection
   * 
   * @return  ActiveResource\Base                                       new resource instance
   */
  protected static function instantiateRecord(array $attrs,
                                              array $prefix_options = array(),
                                              Connection $connection)
  {
    $class  = get_called_class();
    $record = new $class($attrs, $connection);

    return $record;
  }

  /**
   * Returns new objects list, populated with array data
   *
   * @param   array                                 $attrs_list         array of objects attrbiutes
   * @param   array                                 $prefix_options     prefix options for objects
   * @param   ActiveResource\Connections\Connection $connection         remote service connection
   * 
   * @return  array                                                     array of Base objects
   */
  protected static function instantiateCollection(array $attrs_list,
                                                  array $prefix_options = array(),
                                                  Connection $connection)
  {
    $list = array();
    foreach ($attrs_list as $attrs)
    {
      $list[] = self::instantiateRecord($attrs, $prefix_options, $connection);
    }

    return $list;
  }

  /**
   * Extracts from & splitted params optoins from find arguments
   *
   * @param   array $args options array
   * 
   * @return  array       array of [0]=from, [1]=prefix_options & [2]=query_options
   */
  private static function extractOptions(array $args)
  {
    $options = array();
    $options[0] = null;
    $options[1] = $options[2] = array();

    if (isset($args['from']))
    {
      $options[0] = $args['from'];
    }

    if (isset($args['params']))
    {
      list($options[1], $options[2]) = self::splitParams($args['params']);
    }

    return $options;
  }

  /**
   * Split params array into prefix_options & query_options
   *
   * @param   array $params params array
   * 
   * @return  array         array of [0]=prefix_options, [1]=query_options
   */
  private static function splitParams(array $params)
  {
    $prefix_options = array();
    $query_options  = array();

    foreach ($params as $name => $value)
    {
      if (false !== strpos($name, '_id'))
      {
        $prefix_options[$name] = $value;
      }
      else
      {
        $query_options[$name] = $value;
      }
    }

    return array($prefix_options, $query_options);
  }

  /**
   * Takes a response from a typical create post and pulls the ID out
   *
   * @param   ActiveResource\Responses\Response   $response   response object
   * 
   * @return  integer                                         id of the newly created resource
   */
  private function getIdFromResponse(Response $response)
  {
    preg_match('/\/([^\/]*?)(\.\w+)?$/', $response->getHeader('Location'), $matches);

    return isset($matches[1]) ? intval($matches[1]) : null;
  }

  /**
   * Loads resource attributes from response
   *
   * @param   ActiveResource\Responses\Response   $response   response object
   */
  private function loadAttributesFromResponse(Response $response)
  {
    if (!in_array($response->getCode(), array(204, 304))
      && '0' != $response->getHeader('Content-Length')
      && 0 < strlen(trim($response->getBody())))
    {
      $decoded = self::getFormat()->decode($response->getBody());
      if (isset($decoded[self::getElementName()]))
      {
        $this->load($decoded[self::getElementName()]);
      }
    }
  }

  /**
   * Loads remote errors from response
   *
   * @param ActiveResource\Responses\Response $response response object
   */
  protected function loadRemoteErrors(Response $response)
  {
    $this->errors = $this->initErrors();

    switch(self::getFormat()->getExtension())
    {
      case 'xml':
      {
        $this->errors->loadFromXML($response->getBody());
        break;
      }
      default:
        break;
    }
  }

}
