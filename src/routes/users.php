<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

# Get users
$app->get('/users', function (Request $request, Response $response) {
    $db = new Db();
    $db = $db->connect();
    $users = $db->query("SELECT id, username FROM Users")->fetchAll(PDO::FETCH_OBJ);

    return make_response_json($users,$response);
    }
);

# User register
$app->post('/users', function (Request $request, Response $response) {

    $parsedBody = $request->getBody();
    $parsedBody = json_decode($parsedBody,true);
    if(!array_key_exists("username",$parsedBody) || !array_key_exists("password",$parsedBody)){
        return make_response_message(401,"Missing fields.",$response);
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
        return make_response_message(401,$e->getMessage(),$response);
    }

    return make_response_message(200,"Registration success.",$response);
} 
);


# User login
$app->post('/users/{username}', function (Request $request, Response $response) {

    #Get username and password
    $parsedBody = $request->getBody();
    $parsedBody = json_decode($parsedBody,true);
    if(!array_key_exists("username",$parsedBody) || !array_key_exists("password",$parsedBody)){
        return make_response_message(401,"Missing fields.",$response);
    }
    $username = $parsedBody["username"];
    $password = $parsedBody["password"];
    
    #Check if username and password is correct
    $pdo = new Db();
    $pdo = $pdo->connect();
    $stmt = $pdo->prepare('SELECT * FROM Users WHERE username = :username AND password = :password');
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$user){
        return make_response_message(401,"Username or password incorrect.",$response);
    }

    #Return token if credentials are correct
    $user_id = $user["id"];
    return create_login_token($user_id, $pdo, $response);
    
} 
);