<?php

include './config.php';

if ($_SERVER['argc'] == 1)
{
   echo "Usage: ".$_SERVER['argv'][0]." [register | validate <pin>]\n" ;
   die() ;
}

  $oauth = new OAuth($consumer_key,$consumer_secret,
           OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
  $oauth->enableDebug();  // This will generate debug output in your error_log

try {
  if ($_SERVER['argv'][1] == 'register')
  {

    // Generate request token and redirect user to Twitter to authorize
    $request_token_info =
      $oauth->getRequestToken('https://twitter.com/oauth/request_token');

    $request_token = $request_token_info['oauth_token'];
    $request_token_secret = $request_token_info['oauth_token_secret'];
    file_put_contents("request_token", $request_token);
    file_put_contents("request_token_secret", $request_token_secret);

    // Generate a request link and output it
     echo 'https://twitter.com/oauth/authorize?oauth_token='.$request_token."\n";
     exit();
  }
  else if ($_SERVER['argv'][1] == 'validate')
  {
    if ($_SERVER['argc'] < 3)
    {
      echo "Usage: ".$_SERVER['argv'][0]." validate <pin>\n" ;
      die() ;
    }

    $request_token = file_get_contents("request_token");
    $request_token_secret = file_get_contents("request_token_secret");

    // Get and store an access token
    $oauth->setToken($request_token, $request_token_secret);
    $access_token_info = $oauth->getAccessToken('https://twitter.com/oauth/access_token',null,$_SERVER['argv'][2])
;
    $access_token = $access_token_info['oauth_token'];
    $access_token_secret = $access_token_info['oauth_token_secret'];

    // Now store the two tokens into another file (or database or whatever):
    file_put_contents("access_token", $access_token);
    file_put_contents("access_token_secret", $access_token_secret);

    $oauth->setToken($access_token, $access_token_secret) ;
    $oauth->fetch('https://twitter.com/account/verify_credentials.json');
    $json = json_decode($oauth->getLastResponse());
    echo "Access token saved! Authorized as @".(string)$json->screen_name."\n";
  }
} catch(OAuthException $E) {
  print_r($E);
}
?>
