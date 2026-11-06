<?php
namespace Dao\Cursos;

use Dao\Table;

class Cursos extends Table
{
    public static function getCursos(
        string $partialName = "",
        string $status = "",
        string $orderBy = "",
        bool $orderDescending = false,
        int $page = 0,
        int $itemsPerPage = 10
    ) {
        $sqlstr = "SELECT c.cursoid, c.cursodsc, c.cursodescrip, c.cursoprecio, c.cursomodalidad, c.cursoest, case when c.cursoest = 'ACT' then 'Activo' when c.cursoest = 'INA' then 'Inactivo' else 'Sin Asignar' end as cursoestDsc FROM cursos c";
        $sqlstrCount = "SELECT COUNT(*) as count FROM cursos c";
        $conditions = [];
        $params = [];
        if ($partialName != "") {
            $conditions[] = "c.cursodsc LIKE :partialName";
            $params["partialName"] = "%" . $partialName . "%";
        }
        if (!in_array($status, ["ACT", "INA", ""])) {
            throw new \Exception("Error Processing Request Status has invalid value");
        }
        if ($status != "") {
            $conditions[] = "c.cursoest = :status";
            $params["status"] = $status;
        }
        if (count($conditions) > 0) {
            $sqlstr .= " WHERE " . implode(" AND ", $conditions);
            $sqlstrCount .= " WHERE " . implode(" AND ", $conditions);
        }
        if (!in_array($orderBy, ["cursoid", "cursodsc", "cursoprecio", ""])) {
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
        return ["cursos" => $registros, "total" => $numeroDeRegistros, "page" => $page, "itemsPerPage" => $itemsPerPage];
    }

    public static function getCursoById(int $cursoid)
    {
        $sqlstr = "SELECT cursoid, cursodsc, cursodescrip, cursoprecio, cursomodalidad, cursoest FROM cursos c WHERE c.cursoid = :cursoid";
        $params = ["cursoid" => $cursoid];
        return self::obtenerUnRegistro($sqlstr, $params);
    }

    public static function insertCurso(
        string $cursodsc,
        string $cursodescrip,
        float $cursoprecio,
        string $cursomodalidad,
        string $cursoest
    ) {
        $sqlstr = "INSERT INTO cursos (cursodsc, cursodescrip, cursoprecio, cursomodalidad, cursoest) VALUES (:cursodsc, :cursodescrip, :cursoprecio, :cursomodalidad, :cursoest)";
        $params = [
            "cursodsc" => $cursodsc,
            "cursodescrip" => $cursodescrip,
            "cursoprecio" => $cursoprecio,
            "cursomodalidad" => $cursomodalidad,
            "cursoest" => $cursoest
        ];
        return self::executeNonQuery($sqlstr, $params);
    }

    public static function updateCurso(
        int $cursoid,
        string $cursodsc,
        string $cursodescrip,
        float $cursoprecio,
        string $cursomodalidad,
        string $cursoest
    ) {
        $sqlstr = "UPDATE cursos SET cursodsc = :cursodsc, cursodescrip = :cursodescrip, cursoprecio = :cursoprecio, cursomodalidad = :cursomodalidad, cursoest = :cursoest WHERE cursoid = :cursoid";
        $params = [
            "cursoid" => $cursoid,
            "cursodsc" => $cursodsc,
            "cursodescrip" => $cursodescrip,
            "cursoprecio" => $cursoprecio,
            "cursomodalidad" => $cursomodalidad,
            "cursoest" => $cursoest
        ];
        return self::executeNonQuery($sqlstr, $params);
    }

    public static function deleteCurso(int $cursoid)
    {
        $sqlstr = "DELETE FROM cursos WHERE cursoid = :cursoid";
        $params = ["cursoid" => $cursoid];
        return self::executeNonQuery($sqlstr, $params);
    }
}