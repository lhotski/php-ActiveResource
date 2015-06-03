<?php

/*
 * This file is part of the php-ActiveResource.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('MOCK_DATA_PROTOCOL', 'https');
define('MOCK_DATA_URI', 'mysite.com/path/23');
define('MOCK_DATA_USER', 'admin');
define('MOCK_DATA_PASS', 'test');

define('MOCK_DATA_URL', MOCK_DATA_PROTOCOL . '://' . MOCK_DATA_USER . ':' . MOCK_DATA_PASS . '@' . MOCK_DATA_URI);
define('MOCK_DATA_URL_PREFIX', MOCK_DATA_PROTOCOL . '://' . MOCK_DATA_URI);

$mock_data_get = array(
    array(
        'response' => <<<RESPONSE
HTTP/1.1 200 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 200

<?xml version="1.0" encoding="UTF-8"?>
<projects type="array">
  <project>
    <user-id>14</user-id>
    <username>everzet</username>
    <subfields>
      <sub-id>12</sub-id>
    </subfields>
  </project>
  <project>
    <user-id>5</user-id>
    <username>test-user</username>
    <subfields>
      <sub-id>104</sub-id>
    </subfields>
  </project>
</projects>
RESPONSE
    ,'body'     => <<<BODY
<?xml version="1.0" encoding="UTF-8"?>
<projects type="array">
  <project>
    <user-id>14</user-id>
    <username>everzet</username>
    <subfields>
      <sub-id>12</sub-id>
    </subfields>
  </project>
  <project>
    <user-id>5</user-id>
    <username>test-user</username>
    <subfields>
      <sub-id>104</sub-id>
    </subfields>
  </project>
</projects>
BODY
    ,'path'     => '/projects.xml'
    ,'url'      => MOCK_DATA_URL_PREFIX . '/projects.xml'
    ,'code'     => 200
    ,'headers'  => array(
        'server'        => [0=>'nginx/0.8.33'],
        'date'          => [0=>'Wed, 21 Apr 2010 10:32:14 GMT'],
        'content-type'  => [0=>'application/xml; charset=utf-8'],
        'status'        => [0=>200]
    )
    ),
    array(
        'response' => <<<RESPONSE
HTTP/1.1 200 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml; charset=utf-8
Status: 200

<?xml version="1.0" encoding="UTF-8"?>
<project>
  <id>2</id>
  <user-id>5</user-id>
  <username>test-user</username>
  <subfields>
    <sub-id>104</sub-id>
  </subfields>
</project>
RESPONSE
    ,'body'     => <<<BODY
<?xml version="1.0" encoding="UTF-8"?>
<project>
  <id>2</id>
  <user-id>5</user-id>
  <username>test-user</username>
  <subfields>
    <sub-id>104</sub-id>
  </subfields>
</project>
BODY
    ,'path'     => '/project/2.xml'
    ,'url'      => MOCK_DATA_URL_PREFIX . '/project/2.xml'
    ,'code'     => 200
    ,'headers'  => array(
        'server'        => [0=>'nginx/0.8.33'],
        'date'          => [0=>'Wed, 21 Apr 2010 10:32:14 GMT'],
        'content-type'  => [0=>'application/xml; charset=utf-8'],
        'status'        => [0=>200]
    )
    ),
    array(
        'response' => <<<RESPONSE
HTTP/1.1 201 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml
Status: 201

RESPONSE
    ,'body'     => null
    ,'path'     => '/project/2.xml'
    ,'url'      => MOCK_DATA_URL_PREFIX . '/project/2.xml'
    ,'code'     => 201
    ,'headers'  => array(
        'server'        => [0=>'nginx/0.8.33'],
        'date'          => [0=>'Wed, 21 Apr 2010 10:32:14 GMT'],
        'content-type'  => [0=>'application/xml; charset=utf-8'],
        'status'        => [0=>201]
    )
    ),
);

$mock_data_post = array(
    array(
        'response' => <<<RESPONSE
HTTP/1.1 201 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml
Status: 201

RESPONSE
    ,'body'     => <<<BODY
<project>
  <id type="integer">2</id>
  <user-id type="integer">5</user-id>
  <username>test-user</username>
  <subfields>
    <sub-id type="integer">104</sub-id>
  </subfields>
</project>
BODY
    ,'path'     => '/project/2.xml'
    ,'url'      => MOCK_DATA_URL_PREFIX . '/project/2.xml'
    ,'code'     => 201
    ,'headers'  => array(
        'server'        => [0=>'nginx/0.8.33'],
        'date'          => [0=>'Wed, 21 Apr 2010 10:32:14 GMT'],
        'content-type'  => [0=>'application/xml; charset=utf-8'],
        'status'        => [0=>201]
    )
    ),
    array(
        'response' => <<<RESPONSE
HTTP/1.1 203 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml
Status: 203

RESPONSE
    ,'body'     => <<<BODY
<projects type="array">
  <project>
    <id type="integer">2</id>
    <user-id type="integer">5</user-id>
    <username>test-user</username>
    <subfields>
      <sub-id type="integer">104</sub-id>
    </subfields>
  </project>
  <project>
    <id type="integer">5</id>
    <user-id type="integer">1</user-id>
    <username>test-user</username>
    <subfields>
      <sub-id type="integer">4</sub-id>
    </subfields>
  </project>
</projects>
BODY
    ,'path'     => '/project/2/subprojects/5.xml'
    ,'url'      => MOCK_DATA_URL_PREFIX . '/project/2/subprojects/5.xml'
    ,'code'     => 203
    ,'headers'  => array(
        'server'        => [0=>'nginx/0.8.33'],
        'date'          => [0=>'Wed, 21 Apr 2010 10:32:14 GMT'],
        'content-type'  => [0=>'application/xml; charset=utf-8'],
        'status'        => [0=>203]
    )
    ),
);

$mock_data_head = array(
    array(
        'response' => <<<RESPONSE
HTTP/1.1 203 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml
Status: 203

RESPONSE
    ,'path'     => '/project/2.xml'
    ,'url'      => MOCK_DATA_URL_PREFIX . '/project/2.xml'
    ,'code'     => 203
    ,'headers'  => array(
        'server'        => [0=>'nginx/0.8.33'],
        'date'          => [0=>'Wed, 21 Apr 2010 10:32:14 GMT'],
        'content-type'  => [0=>'application/xml; charset=utf-8'],
        'status'        => [0=>203]
    )
    ),
    array(
        'response' => <<<RESPONSE
HTTP/1.1 201 OK
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Content-Type: application/xml
Status: 201

RESPONSE
    ,'path'     => '/projects.xml'
    ,'url'      => MOCK_DATA_URL_PREFIX . '/projects.xml'
    ,'code'     => 201
    ,'headers'  => array(
        'server'        => [0=>'nginx/0.8.33'],
        'date'          => [0=>'Wed, 21 Apr 2010 10:32:14 GMT'],
        'content-type'  => [0=>'application/xml; charset=utf-8'],
        'status'        => [0=>201]
    )
    ),
);

$mock_data_bad_status = array(
    array(
        'response' => 304
    ,'exception'=> 'ActiveResource\Exceptions\Redirection'
    ),
    array(
        'response' => 303
    ,'exception'=> 'ActiveResource\Exceptions\Redirection'
    ),
    array(
        'response' => 400
    ,'exception'=> 'ActiveResource\Exceptions\BadRequest'
    ),
    array(
        'response' => 401
    ,'exception'=> 'ActiveResource\Exceptions\UnauthorizedAccess'
    ),
    array(
        'response' => 403
    ,'exception'=> 'ActiveResource\Exceptions\ForbiddenAccess'
    ),
    array(
        'response' => 404
    ,'exception'=> 'ActiveResource\Exceptions\ResourceNotFound'
    ),
    array(
        'response' => 405
    ,'exception'=> 'ActiveResource\Exceptions\MethodNotAllowed'
    ),
    array(
        'response' => 409
    ,'exception'=> 'ActiveResource\Exceptions\ResourceConflict'
    ),
    array(
        'response' => 410
    ,'exception'=> 'ActiveResource\Exceptions\ResourceGone'
    ),
    array(
        'response' => 505
    ,'exception'=> 'ActiveResource\Exceptions\ServerError'
    ),
    array(
        'response' => 605
    ,'exception'=> 'ActiveResource\Exceptions\ConnectionException'
    ),
);

