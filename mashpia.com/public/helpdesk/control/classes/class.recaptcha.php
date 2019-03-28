<?php

class gRecaptcha extends jsonHandler  {

  public $settings;
  private $api_url = 'https://www.google.com/recaptcha/api/siteverify';

  public function verify() {
    $url = $this->api_url . '?secret=' . $this->settings->recaptchaPrivateKey . '&response=' . $_POST['g-recaptcha-response'];
    if (function_exists('curl_init')) {
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($curl, CURLOPT_TIMEOUT, 15);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
      $response = curl_exec($curl);
      curl_close($curl);
      $res = $this->decode($response);
      return (isset($res['success']) && $res['success'] == 'true' ? 'ok' : 'fail');
    }
    if (@ini_get('allow_url_fopen') == 1) {
      $response = @file_get_contents($url);
      $res      = $this->decode($response);
      return (isset($res['success']) && $res['success'] == 'true' ? 'ok' : 'fail');
    }
    return 'nothing-supported';
  }

}


?>
