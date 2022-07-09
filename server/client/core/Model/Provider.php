<?php

namespace App\Model;

class Provider
{
    private $name_provider;
    private $icon;
    private $url;
    private $client_id;

    public function __construct($name_provider, $icon, $url, $client_id)
    {
        $this->name_provider = $name_provider;
        $this->icon = $icon;
        $this->url = $url;
        $this->client_id = $client_id;

        return $this;
    }
}
