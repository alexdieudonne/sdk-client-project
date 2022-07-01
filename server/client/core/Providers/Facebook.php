<?php

namespace App\Core\Providers;

class Facebook implements ProviderInterface
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
            'client_id' => FACEBOOK_CLIENT_ID,
            'redirect_uri' => 'http://localhost:8081/fb_callback',
            'response_type' => 'code',
            'scope' => 'public_profile,email',
            "state" => bin2hex(random_bytes(16))
        ]);

        return  "https://www.facebook.com/v2.10/dialog/oauth?{$queryParams}";
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
        return '<i class="fa fa-facebook-official"></i>';
    }

    public function get_icon_class()
    {
        return 'btn-face m-b-20';
    }
}
