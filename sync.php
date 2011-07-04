#!/usr/bin/env php5
<?php
  require_once 'googleplus.class.php';
  include './config.php';
  $tokenfiles=array('access_token','access_token_request','request_token','request_token_secret');
  foreach($tokenfiles as $tokenfile) {
    if (!file_exists($tokenfile) || !is_readable($tokenfile)) {
      echo "please run 'php oauth.php' first!\n";
      die();
    }
  }
  if (!file_exists($datastore)) {
    if(!mkdir($datastore,0700,true)) { die(); }
  }
  if(!is_writable($datastore)) { die(); }
  if(!file_exists($historyfile)) {
    touch($historyfile);
    chmod($historyfile,0600);
  }
  if(!is_writable($historyfile)) { die(); }
  
  $access_token = file_get_contents("access_token");
  $access_token_secret = file_get_contents("access_token_secret");

  $oauth = new OAuth($consumer_key,$consumer_secret,OAUTH_SIG_METHOD_HMACSHA1);
  $oauth->setToken($access_token, $access_token_secret) ;

  $oGooglePlus = new GooglePlus($google_plus_id);
  if ($oGooglePlus->isReady) {
    $arrPosts = $oGooglePlus->getPosts();
    $i        = 0;
    $wriPosts = 0;
    foreach ($arrPosts as $strPost) {
      $postid =md5($strPost);
      $history = file_get_contents($historyfile);
      if (!strstr($history,$postid . "\n")) {
        $args=array('status'=>substr($strPost,0,136-strlen($shortcut_to_gp)) . "... " . $shortcut_to_gp) ;
        try {
          $oauth->fetch('http://twitter.com/statuses/update.json',$args,
                 OAUTH_HTTP_METHOD_POST);
          $json = json_decode($oauth->getLastResponse(),true);
          if(isset($json['id'])) {
	     file_put_contents($historyfile, $postid . "\n",FILE_APPEND);
             $wriPosts++;
           } else {
             print_r($json);
          }
        } catch(OAuthException $E) {
         print_r($E);
        }
      } else {
	continue;
      }
    }
    $i++;
  }
  
?>
