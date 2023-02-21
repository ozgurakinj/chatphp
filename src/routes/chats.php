<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

# Retrieve chats for a user
$app->get('/chats', function (Request $request, Response $response) {
    if(!$request->hasHeader('username')){
        $response_body = ["code"=>401,"message"=>"Authentication missing"];
        $response->getBody()->write(json_encode($response_body));
        return $response
        ->withStatus($response_body["code"]);
    }

    $username = $request->getHeader("username")[0];

    $pdo = new Db();
    $pdo = $pdo->connect();

    $stmt = $pdo->prepare('SELECT id FROM Users WHERE username = :username');
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user_id = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$user_id){
        $response_body = ["code"=>401,"message"=>"User does not exists"];
        $response->getBody()->write(json_encode($response_body));
        return $response
        ->withStatus($response_body["code"]);
    }

    $stmt = $pdo->prepare('SELECT * FROM (SELECT d.id, user2, u1, username AS u2 FROM (SELECT Chats.id, user2, Users.username AS u1 FROM Chats, Users WHERE Chats.user1=Users.id) d, Users WHERE d.user2=Users.id) z WHERE z.u1=? OR z.u2=?');
    $stmt = $pdo->prepare('SELECT * FROM (SELECT d.id AS chat_id, user1 AS user1_id, user1_username, user2, username AS user2_username FROM (SELECT Chats.id, user2, user1, Users.username AS user1_username FROM Chats, Users WHERE Chats.user1=Users.id) d, Users WHERE d.user2=Users.id) z WHERE z.user1_username=? OR z.user2_username=?');
    $stmt->bindParam(1, $username);
    $stmt->bindParam(2, $username);
    $stmt->execute();
    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($chats as $chat){
        $chat_id = $chat["chat_id"];
        $stmt = $pdo->prepare('SELECT message FROM Messages WHERE chat_id=? ORDER BY id DESC LIMIT 1');
        $stmt->bindParam(1, $chat_id);
        $stmt->execute();
        $chat = $stmt->fetch();
        if($chat){
            $chats[array_search($chat,$chats)] += ["last_message" => $chat[0]];
        }else{
            $chats[array_search($chat,$chats)] += ["last_message" => ""];
        }
    }

    $response->getBody()->write(json_encode($chats));
    return $response
        ->withStatus(200)
        ->withHeader("Content-Type","application/json");
    }
);

# Retrieve messages in a chat
$app->get('/chats/{id}', function (Request $request, Response $response) {
   
    $chat_id = $request->getAttribute("id");

    $pdo = new Db();
    $pdo = $pdo->connect();

    $stmt = $pdo->prepare('SELECT * FROM Chats WHERE id= :chatid');
    $stmt->bindParam(':chatid', $chat_id);
    $stmt->execute();
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$chat){
        $response_body = ["code"=>401,"message"=>"No chat found for this id."];
        $response->getBody()->write(json_encode($response_body));
        return $response
            ->withStatus($response_body["code"]);
    }

    $stmt = $pdo->prepare('SELECT id, timestamp, sender, message FROM Messages WHERE chat_id= :chatid ORDER BY id DESC');
    $stmt->bindParam(':chatid', $chat_id);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(!$messages){
        $response_body = ["code"=>401,"message"=>"No message found for this chat."];
        $response->getBody()->write(json_encode($response_body));
        return $response
            ->withStatus($response_body["code"]);
    }

    $response->getBody()->write(json_encode($messages));
    return $response
        ->withStatus(200)
        ->withHeader("Content-Type","application/json");
    }
);

#Send message
$app->post('/chats/{id}', function (Request $request, Response $response) {
    

    }
);