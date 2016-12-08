<?php

CONST CLIENT_ID = '2_18xrn7c43ctcocsocgkkss4kg0swcwsk88o0ccw8gk4w0o4oco';
CONST CLIENT_SECRET = '4mqghz4mnc848ks0so8ok84g08wggc0wk8sogc0400c4gw4css';
CONST AUTH_PAGE = 'https://emcid.local/oauth/v2/auth';
CONST AUTH_TOKEN = 'https://emcid.local/oauth/v2/token';
CONST REDIRECT_URI = 'http://client.local/client.php';


if (!(array_key_exists('error', $_REQUEST) || array_key_exists('code', $_REQUEST))) {
  $auth = AUTH_PAGE;
  $authQ = http_build_query([
    'client_id' => CLIENT_ID,
    'redirect_uri'=> REDIRECT_URI,
    'response_type'=>'code'
  ]);

  header('Location: ' . $auth. '?'. $authQ);
}

if(array_key_exists('code', $_REQUEST) && array_key_exists('state', $_REQUEST) && !array_key_exists('error', $_REQUEST)) {
  $connect = AUTH_TOKEN;

  $opts = [
    'http' => [
      'method' => 'POST',
      'header' => join(
        "\r\n",
        [
          'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
          'Accept-Charset: utf-8;q=0.7,*;q=0.7',
        ]
      ),
      'content' => http_build_query([
        'code' => $_REQUEST['code'],
        'client_id'=> CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'grant_type' => 'authorization_code',
        'redirect_uri' => REDIRECT_URI
      ]),
      'ignore_errors' => true,
      'timeout' => 10
    ],
    'ssl' => [
      "verify_peer" => false,
      "verify_peer_name" => false,
    ],
  ];


  $response = @file_get_contents($connect, false, stream_context_create($opts));
  $response = json_decode($response,true);

  if(!array_key_exists('error', $response)) {
    echo '<pre>';
    echo 'Authorized'."\r\n";
    var_dump($response);
    echo '</pre>';
  } else {
    echo '<pre>';
    echo($response['error_description']);
    echo '</pre>';
  }

}
else {
  echo '<pre>';
  echo $_REQUEST['error_description'];
  echo '</pre>';
}
