<?php

class Db {
    private $sqlite = "sqlite:../src/db/db.sqlite";

    public function connect(){
        $db = new PDO($this->sqlite);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    }


}