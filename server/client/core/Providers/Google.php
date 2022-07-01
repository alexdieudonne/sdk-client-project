<?php

namespace App\Core\Providers;


class Google implements ProviderInterface
{
  private $name_provider;
  private $client_id;

  public function __construct($name, $client_id)
  {
    $this->name_provider = $name;
    $this->client_id = $client_id;
  }


  public function get_url()
  {

    $queryParams = http_build_query([
      'client_id' => GOOGLE_CLIENT_ID,
      'redirect_uri' => 'http://localhost:8081/gg_callback',
      'response_type' => 'code',
      'scope' => 'profile',
      'access_type' => 'offline',
      'include_granted_scopes' => 'true',
      'state' => bin2hex(random_bytes(16)),
    ]);

    return  "https://accounts.google.com/o/oauth2/auth?{$queryParams}";
  }

  public function get_client_id()
  {
    return $this->client_id;
  }

  public function get_name()
  {
    return ucfirst($this->name_provider);
  }

  public function get_icon()
  {
    return '<i class="fa fa-google" aria-hidden="true"></i>';
  }
  public function get_icon_class()
  {
    return "btn-face m-b-20";
  }
}
