<?php

namespace App\Core\Providers;

class Discord extends Provider implements ProviderInterface
{
    public $base_uri = "https://discord.com/api/oauth2/authorize";
    public $url_token = "https://discord.com/api/oauth2/token";


    public function get_url()
    {
        $queryParams = http_build_query([
            'client_id' =>  $this->get_client_id(),
            'redirect_uri' => 'http://localhost:8081/dscrd_callback',
            'response_type' => 'code',
            'scope' => 'identify email',
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
        return '<i class="fa fa-gamepad" aria-hidden="true"></i>';
    }

    public function get_icon_class()
    {
        return 'btn-face m-b-20';
    }
}
