<?php

namespace ActiveResource\Connections;

interface Connection
{
  public function getSite();
  public function setSite($site);

  public function setBasePath($path);
  public function getBasePath();
  
  public function getUsername();
  public function getPassword();
  public function getAuthType();
  public function setAuth($username, $password, $auth_type = 'basic');

  public function getHeader($name);
  public function setHeaders(array $headers);
  public function setHeader($name, $value);

  public function getTimeout();
  public function setTimeout($timeout);

  public function getFormat();
  public function setFormat(\ActiveResource\Formats\Format $format);

  public function head($path, array $headers = array());
  public function get($path, array $headers = array());
  public function delete($path, array $headers = array());
  public function put($path, array $body = array(), array $headers = array());
  public function post($path, array $body = array(), array $headers = array());
}
