<?php

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';
require '../src/db/db.php';

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Server running");
    return $response;
});


#Users
require "../src/routes/users.php";
#Chats
require "../src/routes/chats.php";

$app->run();