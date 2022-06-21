<?php

namespace App\Core;

abstract class Provider
{
    abstract public function get_name();
    abstract public function get_url();
    abstract public function get_client_id();
}
