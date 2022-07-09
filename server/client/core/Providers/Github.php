<?php

namespace App\Core\Providers;

class Github implements ProviderInterface
{

    private $name;
    private $client_id;
    private $client_secret;
    private $base_uri = "https://github.com/login/oauth/authorize";

    public function __construct($name, $client_id, $client_secret)
    {
        $this->name = $name;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
    }


    public function get_url()
    {
            $queryParams = http_build_query([
                'client_id' =>  $this->client_id,
                'redirect_uri' => 'http://localhost:8081/gth_callback',
                'response_type' => 'code',
                'scope' => 'public_profile,email',
                "state" => bin2hex(random_bytes(16))
            ]);
            return  $this->base_uri."?{$queryParams}";
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
        return '<i class="fa fa-github" aria-hidden="true"></i>';
    }

    public function get_icon_class()
    {
        return 'btn-face m-b-20';
    }
}
