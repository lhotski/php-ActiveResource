<?php

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
    ,'decoded'  => array('projects' => array(
      array('user_id' => 14, 'username' => 'everzet', 'subfields' => array('sub_id' => 12)),
      array('user_id' => 5, 'username' => 'test-user', 'subfields' => array('sub_id' => 104)),
    ))
    ,'path'     => '/projects.:extension:'
    ,'url'      => MOCK_DATA_URL_PREFIX . '/projects.xml'
    ,'code'     => 200
    ,'headers'  => array(
      'server'        => 'nginx/0.8.33',
      'date'          => 'Wed, 21 Apr 2010 10:32:14 GMT',
      'content-type'  => 'application/xml; charset=utf-8',
      'status'        => 200
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
    ,'decoded'  => array('project' => array('id' => 2, 'user_id' => 5, 'username' => 'test-user', 'subfields' => array('sub_id' => 104)))
    ,'path'     => '/project/2.:extension:'
    ,'url'      => MOCK_DATA_URL_PREFIX . '/project/2.xml'
    ,'code'     => 200
    ,'headers'  => array(
      'server'        => 'nginx/0.8.33',
      'date'          => 'Wed, 21 Apr 2010 10:32:14 GMT',
      'content-type'  => 'application/xml; charset=utf-8',
      'status'        => 200
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
    ,'decoded'  => null
    ,'path'     => '/project/2.:extension:'
    ,'url'      => MOCK_DATA_URL_PREFIX . '/project/2.xml'
    ,'code'     => 201
    ,'headers'  => array(
      'server'        => 'nginx/0.8.33',
      'date'          => 'Wed, 21 Apr 2010 10:32:14 GMT',
      'content-type'  => 'application/xml',
      'status'        => 201
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
    ,'encoded'     => <<<BODY
<project>
  <id type="integer">2</id>
  <user-id type="integer">5</user-id>
  <username>test-user</username>
  <subfields>
    <sub-id type="integer">104</sub-id>
  </subfields>
</project>
BODY
    ,'body'     => array('project' => array('id' => 2, 'user_id' => 5, 'username' => 'test-user', 'subfields' => array('sub_id' => 104)))
    ,'path'     => '/project/2.:extension:'
    ,'url'      => MOCK_DATA_URL_PREFIX . '/project/2.xml'
    ,'code'     => 201
    ,'headers'  => array(
      'server'        => 'nginx/0.8.33',
      'date'          => 'Wed, 21 Apr 2010 10:32:14 GMT',
      'content-type'  => 'application/xml',
      'status'        => 201
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
    ,'encoded'     => <<<BODY
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
    ,'body'     => array('projects' => array(
      array('id' => 2, 'user_id' => 5, 'username' => 'test-user', 'subfields' => array('sub_id' => 104)),
      array('id' => 5, 'user_id' => 1, 'username' => 'test-user', 'subfields' => array('sub_id' => 4)),
    ))
    ,'path'     => '/project/2/subprojects/5.:extension:'
    ,'url'      => MOCK_DATA_URL_PREFIX . '/project/2/subprojects/5.xml'
    ,'code'     => 203
    ,'headers'  => array(
      'server'        => 'nginx/0.8.33',
      'date'          => 'Wed, 21 Apr 2010 10:32:14 GMT',
      'content-type'  => 'application/xml',
      'status'        => 203
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
    ,'path'     => '/project/2.:extension:'
    ,'url'      => MOCK_DATA_URL_PREFIX . '/project/2.xml'
    ,'code'     => 203
    ,'headers'  => array(
      'server'        => 'nginx/0.8.33',
      'date'          => 'Wed, 21 Apr 2010 10:32:14 GMT',
      'content-type'  => 'application/xml',
      'status'        => 203
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
    ,'path'     => '/projects.:extension:'
    ,'url'      => MOCK_DATA_URL_PREFIX . '/projects.xml'
    ,'code'     => 201
    ,'headers'  => array(
      'server'        => 'nginx/0.8.33',
      'date'          => 'Wed, 21 Apr 2010 10:32:14 GMT',
      'content-type'  => 'application/xml',
      'status'        => 201
    )
  ),
);

$mock_data_bad_status = array(
  array(
     'response' => <<<RESPONSE
HTTP/1.1 304 Redirection
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Status: 304

RESPONSE
    ,'exception'=> 'ActiveResource\Exceptions\Redirection'
  ),
  array(
     'response' => <<<RESPONSE
HTTP/1.1 303 Redirection
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Status: 303

RESPONSE
    ,'exception'=> 'ActiveResource\Exceptions\Redirection'
  ),
  array(
     'response' => <<<RESPONSE
HTTP/1.1 400 Bad request
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Status: 400

RESPONSE
    ,'exception'=> 'ActiveResource\Exceptions\BadRequest'
  ),
  array(
     'response' => <<<RESPONSE
HTTP/1.1 401
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Status: 401

RESPONSE
    ,'exception'=> 'ActiveResource\Exceptions\UnauthorizedAccess'
  ),
  array(
     'response' => <<<RESPONSE
HTTP/1.1 403
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Status: 403

RESPONSE
    ,'exception'=> 'ActiveResource\Exceptions\ForbiddenAccess'
  ),
  array(
     'response' => <<<RESPONSE
HTTP/1.1 404
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Status: 404

RESPONSE
    ,'exception'=> 'ActiveResource\Exceptions\ResourceNotFound'
  ),
  array(
     'response' => <<<RESPONSE
HTTP/1.1 405
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Status: 405

RESPONSE
    ,'exception'=> 'ActiveResource\Exceptions\MethodNotAllowed'
  ),
  array(
     'response' => <<<RESPONSE
HTTP/1.1 409
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Status: 409

RESPONSE
    ,'exception'=> 'ActiveResource\Exceptions\ResourceConflict'
  ),
  array(
     'response' => <<<RESPONSE
HTTP/1.1 410
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Status: 410

RESPONSE
    ,'exception'=> 'ActiveResource\Exceptions\ResourceGone'
  ),
  array(
     'response' => <<<RESPONSE
HTTP/1.1 404
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Status: 404

RESPONSE
    ,'exception'=> 'ActiveResource\Exceptions\ResourceNotFound'
  ),
  array(
     'response' => <<<RESPONSE
HTTP/1.1 505
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Status: 505

RESPONSE
    ,'exception'=> 'ActiveResource\Exceptions\ServerError'
  ),
  array(
     'response' => <<<RESPONSE
HTTP/1.1 605
Server: nginx/0.8.33
Date: Wed, 21 Apr 2010 10:32:14 GMT
Status: 605

RESPONSE
    ,'exception'=> 'ActiveResource\Exceptions\ConnectionException'
  ),
);

