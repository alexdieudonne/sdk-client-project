<?php

namespace App\Core\Providers;

class Github extends Provider implements ProviderInterface
{

    public  $url_token = "https://github.com/login/oauth/access_token";
    private $base_uri = "https://github.com/login/oauth/authorize";


    public function get_url()
    {
            $queryParams = http_build_query([
                'client_id' =>   $this->get_client_id(),
                'redirect_uri' => 'http://localhost:8081/gth_callback',
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
        return '<i class="fa fa-github" aria-hidden="true"></i>';
    }

    public function get_icon_class()
    {
        return 'btn-face m-b-20';
    }
}
