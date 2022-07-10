<?php

namespace App\Core;


class Factory
{
    public static function get_providers()
    {
        $providersFile = "providers.json";
        if (!file_exists($providersFile)) {
            die("Le fichier " . $providersFile . " n'existe pas");
        }
        $providers_file = file_get_contents($providersFile);
        $jsonIterator = json_decode($providers_file, TRUE);

        $all_providers = [];
        for ($i = 0; $i < count($jsonIterator['providers']); $i++) {

            $classNameUp = ucfirst($jsonIterator['providers'][$i]['name']);
            $class_string = '\App\Core\Providers\\' . $classNameUp;

            try {
                $provider = new $class_string($jsonIterator['providers'][$i]['name'], $jsonIterator['providers'][$i]['client_id'], $jsonIterator['providers'][$i]['client_secret']);
                array_push($all_providers, $provider);
            } catch (\Exception $e) {
                var_dump($e);
            }
        }

        return $all_providers;
    }
}
