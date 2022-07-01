<?php

namespace App;

define('OAUTH_CLIENT_ID', '621f59c71bc35');
define('OAUTH_CLIENT_SECRET', '621f59c71bc36');
define('FACEBOOK_CLIENT_ID', '1311135729390173');
define('FACEBOOK_CLIENT_SECRET', 'fc5e25661fe961ab85d130779357541e');
define("GOOGLE_CLIENT_ID", "290775757105-ecgqinvpe3etk9r3n6s0lk346foc99at.apps.googleusercontent.com");
define("GOOGLE_CLIENT_SECRET", "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");


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

function login()
{
    $queryParams = http_build_query([
        'client_id' => OAUTH_CLIENT_ID,
        'redirect_uri' => 'http://localhost:8081/callback',
        'response_type' => 'code',
        'scope' => 'basic',
        "state" => bin2hex(random_bytes(16))
    ]);
    echo "
        <form action='/callback' method='post'>
            <input type='text' name='username'/>
            <input type='password' name='password'/>
            <input type='submit' value='Login'/>
        </form>
    ";
    echo "<a href=\"http://localhost:8080/auth?{$queryParams}\">Login with OauthServer</a>";
    $queryParams = http_build_query([
        'client_id' => FACEBOOK_CLIENT_ID,
        'redirect_uri' => 'http://localhost:8081/fb_callback',
        'response_type' => 'code',
        'scope' => 'public_profile,email',
        "state" => bin2hex(random_bytes(16))
    ]);
    echo "<a href=\"https://www.facebook.com/v2.10/dialog/oauth?{$queryParams}\">Login with Facebook</a>";
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
    ["code" => $code, "state" => $state] = $_GET;

    $specifParams = [
        'code' => $code,
        'grant_type' => 'authorization_code',
    ];

    $queryParams = http_build_query(array_merge([
        'client_id' => FACEBOOK_CLIENT_ID,
        'client_secret' => FACEBOOK_CLIENT_SECRET,
        'redirect_uri' => 'http://localhost:8081/fb_callback',
    ], $specifParams));
    $response = file_get_contents("https://graph.facebook.com/v2.10/oauth/access_token?{$queryParams}");
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
        'family_name' => $family_name,
        'given_name' => $given_name, 'name' => $name,
    ] = json_decode($apiResponse, true);

    echo "Hello $name";
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
    default:
        http_response_code(404);
        break;
}
