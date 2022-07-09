<?php

namespace App\Core\Providers;

interface ProviderInterface
{
    public function get_name();
    public function get_icon();
    public function get_url();
    public function get_client_id();
    public function get_client_secret();
}
