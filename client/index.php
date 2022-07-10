<?php

namespace App;

define('OAUTH_CLIENT_ID', '621f59c71bc35');
define('OAUTH_CLIENT_SECRET', '621f59c71bc36');
define("GOOGLE_CLIENT_ID", "290775757105-ecgqinvpe3etk9r3n6s0lk346foc99at.apps.googleusercontent.com");
define("GOOGLE_CLIENT_SECRET", "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");

function findObjectByName($array, $name)
{
    foreach ($array as $element) {
        if ($name == $element->get_name()) {
            return $element;
        }
    }
    return false;
}

function myAutoloader($class)
{
    // $class => CleanWords();
    $class = str_replace("App\\", "", $class);
    $class = str_replace("\\", "/", $class);

    if (file_exists($class . ".class.php")) {
        include $class . ".class.php";
    }
    if (file_exists($class . ".php")) {
        include $class . ".php";
    }
}

spl_autoload_register("App\myAutoloader");


function view($view)
{
    // echo "views/" . $view . ".view.php";
    return __DIR__ . "/views/" . $view . ".view.php";
}


// Exchange code for token then get user info
function callback()
{
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        ["username" => $username, "password" => $password] = $_POST;
        $specifParams = [
            'username' => $username,
            'password' => $password,
            'grant_type' => 'password',
        ];
    } else {
        ["code" => $code, "state" => $state] = $_GET;

        $specifParams = [
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];
    }

    $queryParams = http_build_query(array_merge([
        'client_id' => OAUTH_CLIENT_ID,
        'client_secret' => OAUTH_CLIENT_SECRET,
        'redirect_uri' => 'http://localhost:8081/callback',
    ], $specifParams));
    $response = file_get_contents("http://server:8080/token?{$queryParams}");
    $token = json_decode($response, true);

    $context = stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer {$token['access_token']}"
        ]
    ]);
    $response = file_get_contents("http://server:8080/me", false, $context);
    $user = json_decode($response, true);
    echo "Hello {$user['lastname']} {$user['firstname']}";
}



function fbcallback()
{
    $providers = \App\Core\Factory::get_providers();
    $facebook_provider = findObjectByName($providers, 'Facebook');
    $secret_id = $facebook_provider->get_client_secret();
    $client_id = $facebook_provider->get_client_id();
    $token_url = $facebook_provider->url_token;


    if (isset($_GET["error"])) {
        http_response_code(401);
        die("User doesn't allow.");
    } else {
        ["code" => $code, "state" => $state] = $_GET;
        $specifParams = [
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];
        $queryParams = http_build_query(array_merge([
            'client_id' => $client_id,
            'client_secret' => $secret_id,
            'redirect_uri' => 'http://localhost:8081/fb_callback',
        ], $specifParams));

        $response = file_get_contents($token_url . "?{$queryParams}");
        $token = json_decode($response, true);
        $context = stream_context_create([
            'http' => [
                'header' => "Authorization: Bearer {$token['access_token']}"
            ]
        ]);
        $response = file_get_contents("https://graph.facebook.com/v2.10/me", false, $context);
        $user = json_decode($response, true);
        echo "Hello {$user['name']}";
    }
}

function ggcallback()
{
    ["code" => $code, "state" => $state, "scope" => $scope] = $_GET;

    $data = http_build_query([
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'scope' => 'profile',
        'redirect_uri' => 'http://localhost:8081/gg_callback',
        "grant_type" => 'authorization_code'
    ]);

    $authorizationContext = stream_context_create([
        'http' => [
            'method' => 'POST',
            'protocol_version' => 1.1,
            'header' => [
                'Host:  oauth2.googleapis.com',
                'Content-type: application/x-www-form-urlencoded'
            ],
            'content' => $data
        ]
    ]);

    $authorizationToken = file_get_contents("https://oauth2.googleapis.com/token", false, $authorizationContext);

    [
        'access_token' => $access_token, 'expires_in' => $expires_in,
        'scope' => $scope, 'token_type' => $token_type, 'id_token' => $id_token
    ] = json_decode($authorizationToken, true);

    $apiContext = stream_context_create([
        'http' => [
            'method' => 'GET',
            'protocol_version' => 1.1,
            'header' => [
                "Authorization: Bearer $access_token"
            ],
        ]
    ]);

    $apiResponse = file_get_contents("https://openidconnect.googleapis.com/v1/userinfo", false, $apiContext);

    //  api response into variables
    [
        'sub' => $sub, 'picture' => $picture,
        'family_name' => $family_name, 'email_verified' => $email_verified,
        'given_name' => $given_name, 'name' => $name,
    ] = json_decode($apiResponse, true);

    if (isset($email_verified) === true) {
        // Redirect to our app
        echo "Hello $name";
    } else {
        echo "Wrong credentials";
    }
}

function gth_callback()
{
    $providers = \App\Core\Factory::get_providers();
    $github_provider = findObjectByName($providers, 'Github');
    $secret_id = $github_provider->get_client_secret();
    $client_id = $github_provider->get_client_id();
    $token_url = $github_provider->url_token;


    if (isset($_GET["error"])) {
        http_response_code(401);
        die("User doesn't allow.");
    } else {
        ["code" => $code, "state" => $state] = $_GET;
        $specifParams = [
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];
        $queryParams = http_build_query(array_merge([
            'client_id' => $client_id,
            'client_secret' => $secret_id,
            'redirect_uri' => 'http://localhost:8081/gth_callback',
            'code' => $code,
        ], $specifParams));

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Content-type: application/json\r\n" . "Accept: application/json\r\n",
            ]
        ]);
        $response = file_get_contents($token_url . "?{$queryParams}", false, $context);

        $token = json_decode($response, true);
        $context = stream_context_create([
            'http' => array(
                'method' => "GET",
                'header' => "Accept-language: en\r\n" .
                    "Authorization: token {$token['access_token']} \r\n" .
                    "User-Agent: request"
            )
        ]);

        $rsp = file_get_contents("https://api.github.com/user", false, $context);
        $response = file_get_contents("https://api.github.com/user", false, $context);
        $error = error_get_last();
        $user = json_decode($response, true);

        echo "Hello {$user['login']}";
    }
}


function dscrd_callback()
{
    $providers = \App\Core\Factory::get_providers();
    $discord_provider = findObjectByName($providers, 'Discord');
    $secret_id = $discord_provider->get_client_secret();
    $client_id = $discord_provider->get_client_id();
    $token_url = $discord_provider->url_token;

    if (isset($_GET["error"])) {
        http_response_code(401);
        die("User doesn't allow.");
    } else {
        ["code" => $code, "state" => $state] = $_GET;

        $queryParams = http_build_query(array_merge([
            'client_id' => $client_id,
            'client_secret' => $secret_id,
            'redirect_uri' => 'http://localhost:8081/dscrd_callback',
            'scope' => 'identify email',
            'code' => $code,
            'grant_type' => 'client_credentials',
        ]));

        $context = stream_context_create([
            'http' => array(
                'method' => "POST",
                'header' => "Content-type: application/x-www-form-urlencoded \r\n" .
                    "Content-length: " . strlen($queryParams) . "\r\n" .
                    "User-Agent: request",
                'content' => $queryParams
            )
        ]);

        $response = file_get_contents($token_url, false, $context);

        $token = json_decode($response, true);


        $context = stream_context_create([
            'http' => array(
                'method' => "GET",
                'header' => "Accept-language: en\r\n" .
                    "Authorization: Bearer {$token['access_token']} \r\n" .
                    "User-Agent: request"
            )
        ]);
        $response = file_get_contents("https://discord.com/api/users/@me", false, $context);
        $user = json_decode($response, true);

        echo "Hello username {$user['username']} \n" . "email {$user['email']}";
    }
}


$route = $_SERVER["REQUEST_URI"];

switch (strtok($route, "?")) {
    case '/login':
        require view('login');
        break;
    case '/callback':
        callback();
        break;
    case '/fb_callback':
        fbcallback();
        break;
    case '/gg_callback':
        ggcallback();
        break;
    case '/gth_callback':
        gth_callback();
        break;
    case '/dscrd_callback':
        dscrd_callback();
        break;
    default:
        http_response_code(404);
        break;
}
