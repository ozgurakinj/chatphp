<?php 

use Psr\Http\Message\ResponseInterface as Response;

function make_response_message(int $code, string $message, Response $response){
    $response_body = ["code"=>$code,"message"=>$message];
    $response->getBody()->write(json_encode($response_body));
    return $response
        ->withStatus($code);
}
function make_response_json($json, Response $response){
    $response->getBody()->write(json_encode($json));
    return $response
        ->withStatus(200)
        ->withHeader("Content-Type","application/json");
    }