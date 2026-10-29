<?php

namespace Dao\Misc;

use Dao\Table;

const PROYECTOS_INSERT = "INSERT INTO proyectos (name, status, owner, client, create_time) values(:name, :status, :owner, :client, now())";

class Proyectos extends Table {
    //CRUD
    //Create,Read, Update, Delete

    private function __construct()
    {
        throw new \Exception('Not implemented');
    }

    public static function crear (
        string $name,
        string $status,
        string $owner,
        string $client
    ) : int {

    
        return self::executeNonQuery(
            PROYECTOS_INSERT,
            [
                "name" => $name,
                "status" => $status,
                "owner" => $owner,
                "client" => $client
            ]
        );
    }
}