<?php 

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function authenticate_request($pdo, Request $request, Response $response){
    
    #Check if token exists in headers
    if(!$request->hasHeader('Authorization')){
        return null;
    }
    $token = $request->getHeader("Authorization")[0];

    #Check if token exists
    $stmt = $pdo->prepare('SELECT user_id, exp FROM Tokens WHERE token=:token');
    $stmt->bindParam(':token', $token);
    try{
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }catch(Exception $e){
        return null;
    }

    if(!$user){
        return null;
    }

    $user_id = $user["user_id"];
    $token_expiration = $user["exp"];

    #Check if token is expired
    if(date("Y-m-d H:i:s")>$token_expiration){
        return null;
    }

    #Return username and id for token
    return $user_id;
}

function generate_token($user_id, $time){
    $salt = "79sa3?4gf.d67a*48a.93q1!";
    $token = hash('sha256', $user_id . $time . $salt);
    return $token;
}

function create_login_token($user_id, $pdo, Response $response){
    
    #Check if token already exists
    $stmt = $pdo->prepare('SELECT user_id, token FROM Tokens WHERE user_id=:user_id');
    $stmt->bindParam(':user_id', $user_id);
    try{
        $stmt->execute();
        $token = $stmt->fetch(PDO::FETCH_ASSOC);
    }catch(Exception $e){
        return make_response_message(500,$e->getMessage(),$response);
    }

    #Generate new token if token does not exist
    if(!$token){
        $exp_date = date(("Y-m-d H:i:s"),strtotime("+3 day"));
        $token = generate_token($user_id,$exp_date);
        $stmt = $pdo->prepare('INSERT INTO Tokens VALUES (:user_id, :token, :exp)');
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':exp', $exp_date);

        try{
            $result = $stmt->execute();
        }catch(Exception $e){
            return make_response_message(400,"Token creation failed.",$response);
        }

        if(!$result){
            return make_response_message(400,"Token creation failed.",$response);
        }
        return make_response_json(["user_id"=>$user_id,"token"=>$token],$response);
    }

    #Refresh token if it exists
    return refresh_token($user_id,$pdo,$response);

}

function refresh_token($user_id, $pdo, Response $response){
    #Set new token and return token
    $exp_date = date(("Y-m-d H:i:s"),strtotime("+3 day"));
    $token = generate_token($user_id,$exp_date);
    $stmt = $pdo->prepare('UPDATE Tokens SET token=:token, exp=:exp WHERE user_id=:user_id');
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':token', $token);
    $stmt->bindParam(':exp', $exp_date);

    try{
        $result = $stmt->execute();
    }catch(Exception $e){
        return make_response_message(400,$e->getMessage(),$response);
    }
    if(!$result){
        return make_response_message(400,"Token creation failed.",$response);
    }
    return make_response_json(["user_id"=>$user_id,"token"=>$token],$response);
}