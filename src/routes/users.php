<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

# Get users
$app->get('/users', function (Request $request, Response $response) {
    $db = new Db();
    $db = $db->connect();
    $users = $db->query("SELECT username FROM Users")->fetchAll(PDO::FETCH_OBJ);

    $response->getBody()->write(json_encode($users));
    return $response
        ->withStatus(200)
        ->withHeader("Content-Type","application/json");
    }
);

# User register
$app->post('/users', function (Request $request, Response $response) {

    $parsedBody = $request->getBody();
    $parsedBody = json_decode($parsedBody,true);
    if(!array_key_exists("username",$parsedBody) || !array_key_exists("password",$parsedBody)){
        $response_body = ["code"=>401,"message"=>"Missing fields"];
        $response->getBody()->write(json_encode($response_body));
        return $response
        ->withStatus($response_body["code"]);
    }
    $username = $parsedBody["username"];
    $password = $parsedBody["password"];
        
    $pdo = new Db();
    $pdo = $pdo->connect();
    $stmt = $pdo->prepare('INSERT INTO Users (username,password) VALUES(:username, :password)');
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    try{
        $result = $stmt->execute();
    }catch (Exception $e){
        $response_body = ["code"=>401,"message"=>$e->getMessage()];
        $response->getBody()->write(json_encode($response_body));
        return $response
        ->withStatus($response_body["code"]);
    }

    $response_body = ["code"=>200,"message"=>"Registration success"];
    $response->getBody()->write(json_encode($response_body));
    return $response
        ->withStatus($response_body["code"]);
} 
);


# User login
$app->post('/users/{username}', function (Request $request, Response $response) {

    $parsedBody = $request->getBody();
    $parsedBody = json_decode($parsedBody,true);
    if(!array_key_exists("username",$parsedBody) || !array_key_exists("password",$parsedBody)){
        $response_body = ["code"=>401,"message"=>"Missing fields"];
        $response->getBody()->write(json_encode($response_body));
        return $response
        ->withStatus($response_body["code"]);
    }
        
    $pdo = new Db();
    $pdo = $pdo->connect();
    $stmt = $pdo->prepare('SELECT * FROM Users WHERE username = :username AND password = :password');
    $username = $parsedBody["username"];
    $password = $parsedBody["password"];
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$user){
        $response_body = ["code"=>401,"message"=>"Username or password incorrect"];
        $response->getBody()->write(json_encode($response_body));
        return $response
            ->withStatus($response_body["code"]);
    }

    $response_body = ["code"=>200,"message"=>"Authentication success"];
    $response->getBody()->write(json_encode($response_body));
    return $response
        ->withStatus($response_body["code"]);

} 
);