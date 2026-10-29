<?php

namespace Dao\Misc;

use Dao\Table;

//OWASP
//que es un soft delete

const PROYECTOS_INSERT = "INSERT INTO proyectos (name, status, owner, client, create_time) values(:name, :status, :owner, :client, now())";
const PROYECTOS_UPDATE = "UPDATE proyectos set name = :name, status = :status, owner = :owner, client =: client where id =:id;";
const PROYECTOS_DELETE = "DELETE FROM proyectos set name = :name, status = :status, owner = :owner, client =: client where id =:id;";
const PROYECTOS_SELECT_ALL = "SELECT * FROM proyectos;";
const PROYECTOS_SELECT_BYID = "SELECT * FROM proyectos where id =:id;";

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

    public static function getAll() : array
    {
        return self::obtenerRegistros(PROYECTOS_SELECT_ALL,[]);
    }
    public static function getById(int $id) : array
    {
        return self::obtenerUnRegistro(PROYECTOS_SELECT_BYID, ["id" => $id]);
    }

    public static function update (
        string $name,
        string $status,
        string $owner,
        string $client,
        int $id
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