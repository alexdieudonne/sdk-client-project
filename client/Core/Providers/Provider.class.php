<?php

namespace App\Core\Providers;


abstract class Provider {
    private $name;
    private $client_id;
    private $client_secret;


    public function __construct($name, $client_id, $client_secret)
    {
        $this->set_name($name);
        $this->set_client_id($client_id);
        $this->set_client_secret($client_secret);
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

