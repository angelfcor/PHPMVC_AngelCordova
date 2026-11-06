<?php
namespace Dao\Security;

use Dao\Table;

class Usuarios extends Table
{
    public static function getUsuarios(
        string $partialName = "",
        string $status = "",
        string $orderBy = "",
        bool $orderDescending = false,
        int $page = 0,
        int $itemsPerPage = 10
    ) {
        $sqlstr = "SELECT u.usercod, u.useremail, u.username, u.userpswd, u.userfching, u.userpswdest, u.userest, u.usertipo, case when u.userest = 'ACT' then 'Activo' when u.userest = 'INA' then 'Inactivo' else 'Sin Asignar' end as userStatusDsc
        FROM usuario u";
        $sqlstrCount = "SELECT COUNT(*) as count FROM usuario u";
        $conditions = [];
        $params = [];
        if ($partialName != "") {
            $conditions[] = "(u.username LIKE :partialName OR u.useremail LIKE :partialName)";
            $params["partialName"] = "%" . $partialName . "%";
        }
        if (!in_array($status, ["ACT", "INA", ""])) {
            throw new \Exception("Error Processing Request Status has invalid value");
        }
        if ($status != "") {
            $conditions[] = "u.userest = :status";
            $params["status"] = $status;
        }
        if (count($conditions) > 0) {
            $sqlstr .= " WHERE " . implode(" AND ", $conditions);
            $sqlstrCount .= " WHERE " . implode(" AND ", $conditions);
        }
        if (!in_array($orderBy, ["usercod", "useremail", "username", ""])) {
            throw new \Exception("Error Processing Request OrderBy has invalid value");
        }
        if ($orderBy != "") {
            $sqlstr .= " ORDER BY " . $orderBy;
            if ($orderDescending) {
                $sqlstr .= " DESC";
            }
        }
        $numeroDeRegistros = self::obtenerUnRegistro($sqlstrCount, $params)["count"];
        $pagesCount = ceil($numeroDeRegistros / $itemsPerPage);
        if ($page > $pagesCount - 1) {
            $page = $pagesCount - 1;
        }
        if ($page < 0) {
            $page = 0;
        }
        $sqlstr .= " LIMIT " . $page * $itemsPerPage . ", " . $itemsPerPage;

        $registros = self::obtenerRegistros($sqlstr, $params);
        return ["usuarios" => $registros, "total" => $numeroDeRegistros, "page" => $page, "itemsPerPage" => $itemsPerPage];
    }

    public static function getUsuarioById(int $usercod)
    {
        $sqlstr = "SELECT u.usercod, u.useremail, u.username, u.userpswd, u.userfching, u.userpswdest, u.userest, u.usertipo FROM usuario u WHERE u.usercod = :usercod";
        $params = ["usercod" => $usercod];
        return self::obtenerUnRegistro($sqlstr, $params);
    }

    public static function insertUsuario(
        string $useremail,
        string $username,
        string $userpswd,
        string $userStatus = "ACT",
        string $usertipo = "NOR"
    ) {
        $sqlstr = "INSERT INTO usuario (useremail, username, userpswd, userfching, userpswdest, userest, usertipo) VALUES (:useremail, :username, :userpswd, NOW(), 'ACT', :userStatus, :usertipo)";
        $params = [
            "useremail" => $useremail,
            "username" => $username,
            "userpswd" => $userpswd,
            "userStatus" => $userStatus,
            "usertipo" => $usertipo
        ];
        return self::executeNonQuery($sqlstr, $params);
    }

    public static function updateUsuario(
        int $usercod,
        string $useremail,
        string $username,
        string $userpswd,
        string $userStatus,
        string $usertipo
    ) {
        $sqlstr = "UPDATE usuario SET useremail = :useremail, username = :username, userpswd = :userpswd, userest = :userStatus, usertipo = :usertipo WHERE usercod = :usercod";
        $params = [
            "usercod" => $usercod,
            "useremail" => $useremail,
            "username" => $username,
            "userpswd" => $userpswd,
            "userStatus" => $userStatus,
            "usertipo" => $usertipo
        ];
        return self::executeNonQuery($sqlstr, $params);
    }

    public static function deleteUsuario(int $usercod)
    {
        $sqlstr = "DELETE FROM usuario WHERE usercod = :usercod";
        $params = ["usercod" => $usercod];
        return self::executeNonQuery($sqlstr, $params);
    }
}
