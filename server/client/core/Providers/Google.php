<?php

namespace App\Core\Providers;


class Google extends Provider implements ProviderInterface
{
  private $name;
  private $client_id;
  private $client_secret;

  public function __construct($name, $client_id, $client_secret)
  {
      $this->name = $name;
      $this->client_id = $client_id;
      $this->client_secret = $client_secret;
  }


  public function get_url()
  {

    $queryParams = http_build_query([
      'client_id' => $this->client_id,
      'redirect_uri' => 'http://localhost:8081/gg_callback',
      'response_type' => 'code',
      'scope' => 'profile',
      'access_type' => 'offline',
      'include_granted_scopes' => 'true',
      'state' => bin2hex(random_bytes(16)),
    ]);
    return  "https://accounts.google.com/o/oauth2/auth?{$queryParams}";
  }

  public function get_client_secret(){
    return $this->client_secret;
  }

  public function get_client_id()
  {
    return $this->client_id;
  }

  public function get_name()
  {
    return ucfirst($this->name);
  }

  public function get_icon()
  {
    return '<i class="fa fa-google" aria-hidden="true"></i>';
  }
  public function get_icon_class()
  {
    return "btn-face m-b-20";
  }


  public function set_name($name)
  {
      $this->name = $name;
  }

  public function set_client_id($client_id)
  {
       $this->client_id = $client_id;
  }

  public function set_client_secret($client_secret)
  {
       $this->client_secret = $client_secret;
  }
}
