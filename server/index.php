<?php

$route = $_SERVER["REQUEST_URI"];


function readDatabase($filename)
{
    return array_map(fn ($line) => json_decode($line, true), file($filename));
}

function writeDatabase($filename, $data)
{
    $data = array_map(fn ($line) => json_encode($line), $data);
    file_put_contents($filename, implode("\n", $data));
}


function insert($filename, $line)
{
    $data = readDatabase($filename);
    $data[] = $line;
    writeDatabase($filename, $data);
}

function findBy($filename, $criteria)
{
    $data = readDatabase($filename);
    $result = array_values(array_filter($data, fn ($line) => count(array_intersect_assoc($line, $criteria)) == count($criteria)));
    //  var_dump($result);
    return count($result) > 0 ? $result[0] : null;
}


function findTokenBy($criteria)
{
    return findBy("./data/tokens.db",  $criteria);
}

function findUserBy($criteria)
{
    return findBy("./data/user.db",  $criteria);
}

function findCodeBy($criteria)
{
    return findBy("./data/codes.db",  $criteria);
}


function findAppByName($name)
{
    return findAppBy(['name' => $name]);
}

function findAppBy($app)
{
    return findBy("./data/apps.db", $app);
}

function insertCode($code)
{
    insert("./data/codes.db", $code);
}

function insertToken($token)
{
    insert("./data/tokens.db", $token);
}


function insertApp($app)
{
    insert("./data/apps.db", $app);
}

function register()
{
    ['name' => $name, 'url' => $url, 'redirect_success' => $redirect] = $_POST;
    if (findAppByName($name)) {
        http_response_code(409);
        return;
    }
    $app = [
        "name" => $name, "url" => $url,
        "redirect_success" => $redirect,
        "client_id" => uniqid(),
        "client_secret" => bin2hex(random_bytes(16)),
    ];
    insertApp($app);
    http_response_code(201);
    echo json_encode($app);
}

function auth()
{
    ['client_id' => $client_id, 'scope' => $scope, 'redirect_uri' => $redirect,  'state' => $state] = $_GET;
    $app = findAppBy(['client_id' => $client_id, 'redirect_success' => $redirect]);

    if (!$app) {
        http_response_code(404);
        return;
    }
    echo "Name : " . $app['name'] . "<br>";
    echo "Scope : " . $scope . "<br>";
    echo "Url : " . $app['url'] . "<br>";
    echo "<a href='/auth-success?client_id=" . $client_id . "&state=" . $state . "'>Oui</a>";
    echo "<a href='/fail'>Non</a>";
}

function authSuccess()
{
    ['client_id' => $client_id, 'state' => $state] = $_GET;
    $app = findAppBy(['client_id' => $client_id]);
    if (!$app) {
        http_response_code(404);
        return;
    }
    $code = [
        "client_id" => $client_id,
        "code" => bin2hex(random_bytes(16)),
        "user_id" => bin2hex(random_bytes(16)),
        "expires_at" => time() + 3600,
    ];
    insertCode($code);
    header("Location: $app[redirect_success]?code=$code[code]&state=$state");
}

function token()
{

    $token = null;

    if ($_GET["grant_type"] == "authorization_code") {
        ['code' => $code, 'redirect_uri' => $redirect, 'client_id' => $clientId, 'client_secret' => $clientSecret] = $_GET;

       
        $app = findAppBy(['client_id' => $clientId, 'redirect_success' => $redirect, 'client_secret' => $clientSecret]);

        if (!$app) {
            http_response_code(404);
            return;
        }

        $code = findCodeBy(['code' => $code, 'client_id' => $clientId, 'client_secret' => $clientSecret]);
        if (!$code) {
            http_response_code(400);
            return;
        }
        if ($code['expires_at'] < time()) {
            http_response_code(400);
            return;
        }
        $token = [
            'token' => bin2hex(random_bytes(16)),
            'expires_at' => time() + (60 * 60 * 24 * 30),
            'user_id' => $code['user_id'],
            'client_id' => $code['client_id'],
        ];
    } else {
        ['username' => $user_id, 'client_id' => $clientId, 'password' => $password,] = $_GET;
        $user = findUserBy(['username' => $]);
        $token = [
            'token' => bin2hex(random_bytes(16)),
            'expires_at' => time() + (60 * 60 * 24 * 30),
            'user_id' => $user_id,
            'client_id' => $clientId,
        ];
    }
    insertToken($token);
    http_response_code(201);
    echo json_encode(["access_token" => $token['token'], "expires_in" => $token['expires_at']]);
}

function me()
{
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    [$type, $token] = explode(" ", $auth);
    if ($type !== "Bearer") {
        http_response_code(401);
        return;
    }
    $token = findTokenBy(["token" => $token]);
    if (!$token || $token['expires_at'] < time()) {
        http_response_code(401);
        return;
    }
    $code = findCodeBy(["code" => $token['code']]);
    if (!$code) {
        http_response_code(401);
        return;
    }
    echo json_encode([
        'user_id' => $token['user_id'],
        'lastname' => 'Doe',
        'firstname' => 'John'
    ]);
}

$route = $_SERVER["REQUEST_URI"];


switch (strtok($route, "?")) {
    case '/register':
        register();
        break;
    case '/auth':
        auth();
        break;
    case '/auth-success':
        authSuccess();
        break;
    case '/token':
        token();
        break;
    default:
        http_response_code(404);
        break;
}
