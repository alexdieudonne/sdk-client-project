<?php

namespace App\Core\Providers;

class Facebook extends Provider implements ProviderInterface
{
  
    private $base_uri = "https://www.facebook.com/v2.10/dialog/oauth";
    public  $url_token = "https://graph.facebook.com/v2.10/oauth/access_token";


    public function get_url()
    {
        $queryParams = http_build_query([
            'client_id' =>  $this->get_client_id(),
            'redirect_uri' => 'http://localhost:8081/fb_callback',
            'response_type' => 'code',
            'scope' => 'public_profile,email',
            "state" => bin2hex(random_bytes(16))
        ]);

        return  $this->base_uri."?{$queryParams}";
    }

    public function get_client_secret(){
        return parent::get_client_secret();
    }

    public function get_client_id()
    {
        return parent::get_client_id();
    }

    public function get_name()
    {
        return parent::get_name();
    }

    public function get_icon()
    {
        return '<i class="fa fa-facebook-official"></i>';
    }

    public function get_icon_class()
    {
        return 'btn-face m-b-20';
    }

}
