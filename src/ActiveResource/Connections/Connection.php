<?php

/*
 * This file is part of the php-ActiveResource.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ActiveResource\Connections;

use Psr\Http\Message\ResponseInterface;

/**
 * Connection interface describes base connection object
 *
 * @package    ActiveResource
 * @subpackage Connections
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 * @version    1.0.0
 */
interface Connection
{
    /**
     * Returns site pure URL (without path & user parts)
     *
     * @return  string        site URL
     */
    public function getSite();

    /**
     * Sets base site URL
     *
     * http://site.com/base/path
     * https://user@pass:site.com/base/path
     *
     * @param   string  $site base site URL
     */
    public function setSite($site);

    /**
     * Sets base path
     *
     * @param   string  $path       base path to resources
     */
    public function setBasePath($path);

    /**
     * Returns base path
     *
     * @return  string              base path to resources
     */
    public function getBasePath();

    /**
     * Returns connection username
     *
     * @return  string              username (login)
     */
    public function getUsername();

    /**
     * Returns connection password
     *
     * @return  string              password
     */
    public function getPassword();

    /**
     * Returns connection auth type
     *
     * @return  string              auth type
     */
    public function getAuthType();

    /**
     * Sets connection auth routines
     *
     * @param   string  $username   username
     * @param   string  $password   password
     * @param   string  $auth_type  auth type ('basic' or 'digest')
     */
    public function setAuth($username, $password, $auth_type = 'basic');

    /**
     * Returns specific connection header
     *
     * @param   string  $name       header name
     * @return  string              header
     */
    public function getHeader($name);

    /**
     * Sets specific connection headers
     *
     * @param   array   $headers    hash of headers
     */
    public function setHeaders(array $headers);

    /**
     * Sets specific connection header
     *
     * @param   string  $name       header name
     * @param   string  $value      header
     */
    public function setHeader($name, $value);

    /**
     * Returns connection timeout in ms
     *
     * @return  integer             connection timeout
     */
    public function getTimeout();

    /**
     * Sets connection timeout
     *
     * @param   integer $timeout    connection timeout
     */
    public function setTimeout($timeout);

    /**
     * Sends HEAD request & returns formatted response object
     *
     * @param   string  $path                     resource path
     * @param   array   $headers                  specific headers hash
     *
     * @return  ResponseInterface response instance
     */
    public function head($path, array $headers = array());

    /**
     * Sends GET request & returns formatted response object
     *
     * @param   string  $path                     resource path
     * @param   array   $headers                  specific headers hash
     *
     * @return  ResponseInterface response instance
     */
    public function get($path, array $headers = array());

    /**
     * Sends DELETE request & returns formatted response object
     *
     * @param   string  $path                     resource path
     * @param   array   $headers                  specific headers hash
     *
     * @return  ResponseInterface response instance
     */
    public function delete($path, array $headers = array());

    /**
     * Sends PUT request & returns formatted response object
     *
     * @param   string  $path                     resource path
     * @param   array   $headers                  specific headers hash
     * @param   string  $body                     request body
     *
     * @return  ResponseInterface response instance
     */
    public function put($path, $body, array $headers = array());

    /**
     * Sends POST request & returns formatted response object
     *
     * @param   string  $path                     resource path
     * @param   array   $headers                  specific headers hash
     * @param   string  $body                     request body
     *
     * @return  ResponseInterface response instance
     */
    public function post($path, $body, array $headers = array());
}
