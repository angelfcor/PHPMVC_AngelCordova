<?php
/**
 * wwgen.php - Generador del Patron Work With
 * Uso: php wwgen.php <tabla> <modulo> <lista> <entidad>
 * Genera: modelo de datos (DAO), controlador de lista, controlador de
 * formulario, plantilla de lista y plantilla de formulario.
 */
require __DIR__ . '/vendor/autoload.php';

use Utilities\DotEnv;

if ($argc < 5) {
    fwrite(STDERR, "Uso: php wwgen.php <tabla> <modulo> <lista> <entidad>\n");
    exit(1);
}

list(, $table, $module, $listName, $entityName) = $argv;

$module = ucfirst($module);
$listName = ucfirst($listName);
$entityName = ucfirst($entityName);
$moduleLower = lcfirst($module);
$listLower = lcfirst($listName);
$entityLower = lcfirst($entityName);

(new DotEnv(__DIR__ . '/parameters.env'))->load();

$_dds = sprintf(
    "%s:host=%s;dbname=%s;port=%s;charset=utf8",
    $_ENV["DB_PROVIDER"],
    $_ENV["DB_SERVER"],
    $_ENV["DB_DATABASE"],
    $_ENV["DB_PORT"]
);

try {
    $pdo = new \PDO(
        $_dds,
        $_ENV["DB_USER"],
        $_ENV["DB_PSWD"],
        array(
            \PDO::ATTR_EMULATE_PREPARES => true,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        )
    );
    $stmt = $pdo->prepare(sprintf("DESC `%s`;", $table));
    $stmt->execute();
    $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
} catch (\Exception $ex) {
    fwrite(STDERR, "Error al inspeccionar la tabla " . $table . ": " . $ex->getMessage() . "\n");
    exit(1);
}

function wwPhpType($base)
{
    if (in_array($base, ["int", "bigint", "smallint", "tinyint"])) {
        return "int";
    }
    if (in_array($base, ["decimal", "float", "double"])) {
        return "float";
    }
    return "string";
}

function wwLabel($name)
{
    return ucwords(trim(preg_replace("/([A-Z])/", " $1", $name)));
}

$pkName = null;
$pkIsAI = false;
$fields = array();
$datetimeFields = array();

foreach ($columns as $col) {
    $name = $col["Field"];
    $type = $col["Type"];
    preg_match("/^([a-z]+)/i", $type, $m);
    $base = strtolower($m[1]);
    $len = 0;
    if (preg_match("/\((\d+)\)/", $type, $ml)) {
        $len = intval($ml[1]);
    }

    if ($col["Key"] === "PRI") {
        $pkName = $name;
        $pkIsAI = (strpos($col["Extra"], "auto_increment") !== false);
        continue;
    }

    if (in_array($base, ["datetime", "date", "timestamp"])) {
        $datetimeFields[] = $name;
        continue;
    }

    $field = array(
        "name" => $name,
        "base" => $base,
        "php" => wwPhpType($base)
    );

    if ($base === "char" && $len === 3 &&
        (stripos($name, "est") !== false || stripos($name, "status") !== false)) {
        $field["isStatus"] = true;
    }

    $fields[] = $field;
}

if ($pkName === null) {
    fwrite(STDERR, "La tabla " . $table . " no tiene llave primaria\n");
    exit(1);
}

foreach ($datetimeFields as $df) {
    fwrite(STDOUT, "Excluido del CRUD (datetime): " . $df . "\n");
}

$statusField = null;
foreach ($fields as $field) {
    if (isset($field["isStatus"])) {
        $statusField = $field["name"];
        break;
    }
}

$searchField = null;
foreach ($fields as $field) {
    if ($field["php"] === "string" && !isset($field["isStatus"])) {
        $searchField = $field["name"];
        break;
    }
}

$orderByList = array($pkName);
foreach ($fields as $field) {
    if (count($orderByList) < 3 && $field["base"] !== "text") {
        $orderByList[] = $field["name"];
    }
}

$alias = substr($table, 0, 1);
$listRoute = $module . "_" . $listName;
$formRoute = $module . "-" . $entityName;
$pkPhpType = $pkIsAI ? "int" : "string";

$paths = array(
    "dao" => sprintf("src/Dao/%s/%s.php", $module, $listName),
    "listController" => sprintf("src/Controllers/%s/%s.php", $module, $listName),
    "formController" => sprintf("src/Controllers/%s/%s.php", $module, $entityName),
    "listView" => sprintf("src/Views/templates/%s/%s.view.tpl", $moduleLower, $listLower),
    "formView" => sprintf("src/Views/templates/%s/%s.view.tpl", $moduleLower, $entityLower)
);

$existing = array();
foreach ($paths as $p) {
    if (file_exists($p)) {
        $existing[] = $p;
    }
}
if (count($existing) > 0) {
    fwrite(STDERR, "Los siguientes archivos ya existen, generacion abortada:\n  " . implode("\n  ", $existing) . "\n");
    exit(1);
}

function wwWriteFile($content, $path)
{
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($path, $content);
    fwrite(STDOUT, "Generado: " . $path . "\n");
}

/* ====== Fragments ====== */

$selectListCols = $alias . "." . $pkName;
foreach ($fields as $field) {
    $selectListCols .= ", " . $alias . "." . $field["name"];
}
if ($statusField !== null) {
    $selectListCols .= ", case when " . $alias . "." . $statusField . " = 'ACT' then 'Activo' when " . $alias . "." . $statusField . " = 'INA' then 'Inactivo' else 'Sin Asignar' end as " . $statusField . "Dsc";
}
$selectPlainCols = $pkName . ", " . implode(", ", array_map(function ($f) {
    return $f["name"];
}, $fields));

$orderByListPhp = '["' . implode('", "', $orderByList) . '", ""]';
$orderByListUrl = '["' . implode('", "', array_merge($orderByList, array("clear"))) . '"]';

if ($searchField !== null && $statusField !== null) {
    $filterBlock = '        if ($partialName != "") {
            $conditions[] = "' . $alias . '.' . $searchField . ' LIKE :partialName";
            $params["partialName"] = "%" . $partialName . "%";
        }
        if (!in_array($status, ["ACT", "INA", ""])) {
            throw new \Exception("Error Processing Request Status has invalid value");
        }
        if ($status != "") {
            $conditions[] = "' . $alias . '.' . $statusField . ' = :status";
            $params["status"] = $status;
        }';
} elseif ($searchField !== null) {
    $filterBlock = '        if ($partialName != "") {
            $conditions[] = "' . $alias . '.' . $searchField . ' LIKE :partialName";
            $params["partialName"] = "%" . $partialName . "%";
        }';
} elseif ($statusField !== null) {
    $filterBlock = '        if (!in_array($status, ["ACT", "INA", ""])) {
            throw new \Exception("Error Processing Request Status has invalid value");
        }
        if ($status != "") {
            $conditions[] = "' . $alias . '.' . $statusField . ' = :status";
            $params["status"] = $status;
        }';
} else {
    $filterBlock = '        // sin filtros disponibles';
}

/* ====== 1. Modelo de datos (DAO) ====== */

$insertArgs = $fields;
if (!$pkIsAI) {
    array_unshift($insertArgs, array("name" => $pkName, "php" => "string"));
}
$insertCols = implode(", ", array_map(function ($f) {
    return $f["name"];
}, $insertArgs));
$insertParams = implode(", ", array_map(function ($f) {
    return ":" . $f["name"];
}, $insertArgs));
$insertSignature = implode(",\n        ", array_map(function ($f) {
    return $f["php"] . " $" . $f["name"];
}, $insertArgs));
$insertCallArgs = implode(",\n            ", array_map(function ($f) {
    return '$' . $f["name"];
}, $insertArgs));
$insertAssignments = implode(",\n            ", array_map(function ($f) {
    return '"' . $f["name"] . '" => $' . $f["name"];
}, $insertArgs));

$updateArgs = array();
$updateArgs[] = array("name" => $pkName, "php" => $pkPhpType);
foreach ($fields as $f) {
    $updateArgs[] = $f;
}
$updateSet = implode(", ", array_map(function ($f) {
    return $f["name"] . " = :" . $f["name"];
}, $fields));
$updSignature = implode(",\n        ", array_map(function ($f) {
    return $f["php"] . " $" . $f["name"];
}, $updateArgs));
$updCallArgs = implode(",\n            ", array_map(function ($f) {
    return '$' . $f["name"];
}, $updateArgs));
$updAssignments = implode(",\n            ", array_map(function ($f) {
    return '"' . $f["name"] . '" => $' . $f["name"];
}, $updateArgs));

$daoTemplate = <<<'EOT'
<?php
namespace Dao\%MODULE%;

use Dao\Table;

class %LIST% extends Table
{
    public static function get%LIST%(
        string $partialName = "",
        string $status = "",
        string $orderBy = "",
        bool $orderDescending = false,
        int $page = 0,
        int $itemsPerPage = 10
    ) {
        $sqlstr = "SELECT %SELECT_COLS% FROM %TABLE% %ALIAS%";
        $sqlstrCount = "SELECT COUNT(*) as count FROM %TABLE% %ALIAS%";
        $conditions = [];
        $params = [];
%FILTER_BLOCK%
        if (count($conditions) > 0) {
            $sqlstr .= " WHERE " . implode(" AND ", $conditions);
            $sqlstrCount .= " WHERE " . implode(" AND ", $conditions);
        }
        if (!in_array($orderBy, %ORDER_LIST%)) {
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
        return ["%LISTLOWER%" => $registros, "total" => $numeroDeRegistros, "page" => $page, "itemsPerPage" => $itemsPerPage];
    }

    public static function get%ENTITY%ById(%PKPHP% $%PK%)
    {
        $sqlstr = "SELECT %SELECT_PLAIN% FROM %TABLE% %ALIAS% WHERE %ALIAS%.%PK% = :%PK%";
        $params = ["%PK%" => $%PK%];
        return self::obtenerUnRegistro($sqlstr, $params);
    }

    public static function insert%ENTITY%(
        %INSERT_SIG%
    ) {
        $sqlstr = "INSERT INTO %TABLE% (%INSERT_COLS%) VALUES (%INSERT_PARAMS%)";
        $params = [
            %INSERT_ASSIGNMENTS%
        ];
        return self::executeNonQuery($sqlstr, $params);
    }

    public static function update%ENTITY%(
        %UPD_SIG%
    ) {
        $sqlstr = "UPDATE %TABLE% SET %UPDATE_SET% WHERE %PK% = :%PK%";
        $params = [
            %UPD_ASSIGNMENTS%
        ];
        return self::executeNonQuery($sqlstr, $params);
    }

    public static function delete%ENTITY%(%PKPHP% $%PK%)
    {
        $sqlstr = "DELETE FROM %TABLE% WHERE %PK% = :%PK%";
        $params = ["%PK%" => $%PK%];
        return self::executeNonQuery($sqlstr, $params);
    }
}
EOT;

$daoCode = str_replace(
    array(
        "%MODULE%", "%LIST%", "%ENTITY%", "%TABLE%", "%ALIAS%", "%PK%",
        "%PKPHP%", "%SELECT_COLS%", "%SELECT_PLAIN%", "%ORDER_LIST%",
        "%FILTER_BLOCK%", "%LISTLOWER%", "%INSERT_COLS%", "%INSERT_PARAMS%",
        "%INSERT_SIG%", "%INSERT_ASSIGNMENTS%", "%UPDATE_SET%",
        "%UPD_SIG%", "%UPD_ASSIGNMENTS%"
    ),
    array(
        $module, $listName, $entityName, $table, $alias, $pkName,
        $pkPhpType, $selectListCols, $selectPlainCols, $orderByListPhp,
        $filterBlock, $listLower, $insertCols, $insertParams,
        $insertSignature, $insertAssignments, $updateSet,
        $updSignature, $updAssignments
    ),
    $daoTemplate
);

wwWriteFile($daoCode, $paths["dao"]);

/* ====== 2. Controlador de lista ====== */

$listControllerTemplate = <<<'EOT'
<?php

namespace Controllers\%MODULE%;

use Controllers\PublicController;
use Utilities\Context;
use Utilities\Paging;
use Dao\%MODULE%\%LIST% as Dao%LIST%;
use Views\Renderer;

class %LIST% extends PublicController
{
    private $partialName = "";
    private $status = "";
    private $orderBy = "";
    private $orderDescending = false;
    private $pageNumber = 1;
    private $itemsPerPage = 10;
    private $viewData = [];
    private $%LISTLOWER% = [];
    private $%LISTLOWER%Count = 0;
    private $pages = 0;

    public function run(): void
    {
        $this->getParamsFromContext();
        $this->getParams();
        $tmpList = Dao%LIST%::get%LIST%(
            $this->partialName,
            $this->status,
            $this->orderBy,
            $this->orderDescending,
            $this->pageNumber - 1,
            $this->itemsPerPage
        );
        $this->%LISTLOWER% = $tmpList["%LISTLOWER%"];
        $this->%LISTLOWER%Count = $tmpList["total"];
        $this->pages = $this->%LISTLOWER%Count > 0 ? ceil($this->%LISTLOWER%Count / $this->itemsPerPage) : 1;
        if ($this->pageNumber > $this->pages) {
            $this->pageNumber = $this->pages;
        }
        $this->setParamsToContext();
        $this->setParamsToDataView();
        Renderer::render("%MODULELOWER%/%LISTLOWER%", $this->viewData);
    }

    private function getParams(): void
    {
        $this->partialName = isset($_GET["partialName"]) ? $_GET["partialName"] : $this->partialName;
        $this->status = isset($_GET["status"]) && in_array($_GET["status"], ['ACT', 'INA', 'EMP']) ? $_GET["status"] : $this->status;
        if ($this->status === "EMP") {
            $this->status = "";
        }
        $this->orderBy = isset($_GET["orderBy"]) && in_array($_GET["orderBy"], %ORDER_LIST_URL%) ? $_GET["orderBy"] : $this->orderBy;
        if ($this->orderBy === "clear") {
            $this->orderBy = "";
        }
        $this->orderDescending = isset($_GET["orderDescending"]) ? boolval($_GET["orderDescending"]) : $this->orderDescending;
        $this->pageNumber = isset($_GET["pageNum"]) ? intval($_GET["pageNum"]) : $this->pageNumber;
        $this->itemsPerPage = isset($_GET["itemsPerPage"]) ? intval($_GET["itemsPerPage"]) : $this->itemsPerPage;
    }

    private function getParamsFromContext(): void
    {
        $this->partialName = Context::getContextByKey("%LISTLOWER%_partialName");
        $this->status = Context::getContextByKey("%LISTLOWER%_status");
        $this->orderBy = Context::getContextByKey("%LISTLOWER%_orderBy");
        $this->orderDescending = boolval(Context::getContextByKey("%LISTLOWER%_orderDescending"));
        $this->pageNumber = intval(Context::getContextByKey("%LISTLOWER%_page"));
        $this->itemsPerPage = intval(Context::getContextByKey("%LISTLOWER%_itemsPerPage"));
        if ($this->pageNumber < 1) $this->pageNumber = 1;
        if ($this->itemsPerPage < 1) $this->itemsPerPage = 10;
    }

    private function setParamsToContext(): void
    {
        Context::setContext("%LISTLOWER%_partialName", $this->partialName, true);
        Context::setContext("%LISTLOWER%_status", $this->status, true);
        Context::setContext("%LISTLOWER%_orderBy", $this->orderBy, true);
        Context::setContext("%LISTLOWER%_orderDescending", $this->orderDescending, true);
        Context::setContext("%LISTLOWER%_page", $this->pageNumber, true);
        Context::setContext("%LISTLOWER%_itemsPerPage", $this->itemsPerPage, true);
    }

    private function setParamsToDataView(): void
    {
        $this->viewData["partialName"] = $this->partialName;
        $this->viewData["status"] = $this->status;
        $this->viewData["orderBy"] = $this->orderBy;
        $this->viewData["orderDescending"] = $this->orderDescending;
        $this->viewData["pageNum"] = $this->pageNumber;
        $this->viewData["itemsPerPage"] = $this->itemsPerPage;
        $this->viewData["%LISTLOWER%Count"] = $this->%LISTLOWER%Count;
        $this->viewData["pages"] = $this->pages;
        $this->viewData["%LISTLOWER%"] = $this->%LISTLOWER%;
        if ($this->orderBy !== "") {
            $orderByKey = "Order" . ucfirst($this->orderBy);
            $orderByKeyNoOrder = "OrderBy" . ucfirst($this->orderBy);
            $this->viewData[$orderByKeyNoOrder] = true;
            if ($this->orderDescending) {
                $orderByKey .= "Desc";
            }
            $this->viewData[$orderByKey] = true;
        }
        $statusKey = "status_" . ($this->status === "" ? "EMP" : $this->status);
        $this->viewData[$statusKey] = "selected";
        $pagination = Paging::getPagination(
            $this->%LISTLOWER%Count,
            $this->itemsPerPage,
            $this->pageNumber,
            "index.php?page=%LISTROUTE%",
            "%LISTROUTE%"
        );
        $this->viewData["pagination"] = $pagination;
    }
}
EOT;

$listControllerCode = str_replace(
    array("%MODULE%", "%MODULELOWER%", "%LIST%", "%LISTLOWER%", "%LISTROUTE%", "%ORDER_LIST_URL%"),
    array($module, $moduleLower, $listName, $listLower, $listRoute, $orderByListUrl),
    $listControllerTemplate
);
wwWriteFile($listControllerCode, $paths["listController"]);

/* ====== 3. Plantilla de lista ====== */

$sortableHeaders = "";
foreach ($orderByList as $col) {
    $ucName = ucfirst($col);
    $urlBase = $listRoute;
    $sortableHeaders .= '  <th class="left">
' .
        '    {{ifnot OrderBy' . $ucName . '}}
' .
        '    <a href="index.php?page=' . $listRoute . '&orderBy=' . $col . '&orderDescending=0">' . wwLabel($col) . ' <i class="fas fa-sort"></i></a>
' .
        '    {{endifnot OrderBy' . $ucName . '}}
' .
        '    {{if Order' . $ucName . 'Desc}}
' .
        '    <a href="index.php?page=' . $listRoute . '&orderBy=clear&orderDescending=0">' . wwLabel($col) . ' <i class="fas fa-sort-down"></i></a>
' .
        '    {{endif Order' . $ucName . 'Desc}}
' .
        '    {{if Order' . $ucName . '}}
' .
        '    <a href="index.php?page=' . $listRoute . '&orderBy=' . $col . '&orderDescending=1">' . wwLabel($col) . ' <i class="fas fa-sort-up"></i></a>
' .
        '    {{endif Order' . $ucName . '}}
' .
        '  </th>
';
}
$sortableHeaders = substr($sortableHeaders, 0, -1);

$plainHeaders = "";
if ($statusField !== null) {
    $plainHeaders .= '        <th>Estado</th>
';
}
$plainHeaders = substr($plainHeaders, 0, -1);

$dataRows = '        <td>{{' . $pkName . '}}</td>
';
foreach ($fields as $field) {
    if ($field["name"] === $statusField) {
        if ($statusField === $searchField) {
            $dataRows .= '        <td class="center"> <a class="link" href="index.php?page=' . $formRoute . '&mode=DSP&' . $pkName . '={{' . $pkName . '}}">{{' . $statusField . 'Dsc}}</a>
        </td>
';
        } else {
            $dataRows .= '        <td class="center">{{' . $statusField . 'Dsc}}</td>
';
        }
        continue;
    }
    $css = $field["php"] === "int" || $field["php"] === "float" ? ' class="right"' : ' class="left"';
    if ($field["name"] === $searchField) {
        $dataRows .= '        <td> <a class="link" href="index.php?page=' . $formRoute . '&mode=DSP&' . $pkName . '={{' . $pkName . '}}">{{' . $searchField . '}}</a>
        </td>
';
    } else {
        $dataRows .= '        <td' . $css . '>{{' . $field["name"] . '}}</td>
';
    }
}
$dataRows .= '        <td class="center">
          <a href="index.php?page=' . $formRoute . '&mode=UPD&' . $pkName . '={{' . $pkName . '}}">Editar</a>
          &nbsp;
          <a href="index.php?page=' . $formRoute . '&mode=DEL&' . $pkName . '={{' . $pkName . '}}">Eliminar</a>
        </td>';

$filterInputBlock = "";
if ($searchField !== null) {
    $filterInputBlock = '          <label class="col-3" for="partialName">' . wwLabel($searchField) . '</label>
          <input class="col-9" type="text" name="partialName" id="partialName" value="{{partialName}}" />
';
}
$statusSelectBlock = "";
if ($statusField !== null) {
    $statusSelectBlock = '          <label class="col-3" for="status">Estado</label>
          <select class="col-9" name="status" id="status">
              <option value="EMP" {{status_EMP}}>Todos</option>
              <option value="ACT" {{status_ACT}}>Activo</option>
              <option value="INA" {{status_INA}}>Inactivo</option>
          </select>
';
}

$listViewTemplate = <<<'EOT'
<h1>Trabajar con %LIST%</h1>
<section class="grid">
  <div class="row">
    <form class="col-12 col-m-8" action="index.php" method="get">
      <div class="flex align-center">
        <div class="col-8 row">
          <input type="hidden" name="page" value="%LISTROUTE%">
%FILTER_INPUTILLS%%STATUS_SELECT%
        </div>
        <div class="col-4 align-end">
          <button type="submit">Filtrar</button>
        </div>
      </div>
    </form>
  </div>
</section>
<section class="WWList">
  <table>
    <thead>
      <tr>
%SORTABLE_HEADERS%
%PLAIN_HEADERS%
        <th><a href="index.php?page=%FORMROUTE%&mode=INS">Nuevo</a></th>
      </tr>
    </thead>
    <tbody>
      {{foreach %LISTLOWER%}}
      <tr>
%DATA_ROWS%
      </tr>
      {{endfor %LISTLOWER%}}
    </tbody>
  </table>
  {{pagination}}
</section>
EOT;

$listViewCode = str_replace(
    array("%LIST%", "%LISTROUTE%", "%FORMROUTE%", "%LISTLOWER%", "%FILTER_INPUTILLS%", "%STATUS_SELECT%", "%SORTABLE_HEADERS%", "%PLAIN_HEADERS%", "%DATA_ROWS%"),
    array($listName, $listRoute, $formRoute, $listLower, $filterInputBlock, $statusSelectBlock, $sortableHeaders, $plainHeaders, $dataRows),
    $listViewTemplate
);
wwWriteFile($listViewCode, $paths["listView"]);

/* ====== 4. Controlador de formulario ====== */

$defaults = array();
if ($pkIsAI) {
    $defaults[] = '        "' . $pkName . '" => 0';
} else {
    $defaults[] = '        "' . $pkName . '" => ""';
}
foreach ($fields as $field) {
    if (isset($field["isStatus"])) {
        $defaults[] = '        "' . $field["name"] . '" => "ACT"';
    } elseif ($field["php"] === "string") {
        $defaults[] = '        "' . $field["name"] . '" => ""';
    } else {
        $defaults[] = '        "' . $field["name"] . '" => 0';
    }
}
$defaultsStr = implode(",\n", $defaults);

$validatePost = array();
if ($pkIsAI) {
    $validatePost[] = '        $this->' . $entityLower . '["' . $pkName . '"] = intval($_POST["' . $pkName . '"] ?? "");';
} else {
    $validatePost[] = '        $this->' . $entityLower . '["' . $pkName . '"] = strval($_POST["' . $pkName . '"] ?? "");';
}
foreach ($fields as $field) {
    if ($field["php"] === "int") {
        $validatePost[] = '        $this->' . $entityLower . '["' . $field["name"] . '"] = intval($_POST["' . $field["name"] . '"] ?? "");';
    } elseif ($field["php"] === "float") {
        $validatePost[] = '        $this->' . $entityLower . '["' . $field["name"] . '"] = floatval($_POST["' . $field["name"] . '"] ?? "");';
    } else {
        $validatePost[] = '        $this->' . $entityLower . '["' . $field["name"] . '"] = strval($_POST["' . $field["name"] . '"] ?? "");';
    }
}
$validatePostStr = implode("\n", $validatePost);

$validateRules = array();
if (!$pkIsAI) {
    $validateRules[] = '        if (Validators::IsEmpty($this->' . $entityLower . '["' . $pkName . '"])) {
            $errors["' . $pkName . '_error"] = "El campo ' . $pkName . ' es requerido";
        }';
}
foreach ($fields as $field) {
    $n = $field["name"];
    if (isset($field["isStatus"])) {
        $validateRules[] = '        if (!in_array($this->' . $entityLower . '["' . $n . '"], ["ACT", "INA"])) {
            $errors["' . $n . '_error"] = "El estado del ' . $entityLower . ' es invalido";
        }';
    } elseif ($field["php"] === "string") {
        $validateRules[] = '        if (Validators::IsEmpty($this->' . $entityLower . '["' . $n . '"])) {
            $errors["' . $n . '_error"] = "El campo ' . $n . ' es requerido";
        }';
    } else {
        $validateRules[] = '        if (Validators::IsEmpty($this->' . $entityLower . '["' . $n . '"]) && $this->' . $entityLower . '["' . $n . '"] <= 0) {
            $errors["' . $n . '_error"] = "El campo ' . $n . ' es requerido y debe ser un valor mayor a cero";
        }';
    }
}
$validateRulesStr = implode("\n\n", $validateRules);

$insCall = array();
foreach ($insertArgs as $f) {
    $insCall[] = '            $this->' . $entityLower . '["' . $f["name"] . '"]';
}
$insCallStr = implode(",\n", $insCall);
$updCall = array();
foreach ($updateArgs as $f) {
    $updCall[] = '            $this->' . $entityLower . '["' . $f["name"] . '"]';
}
$updCallStr = implode(",\n", $updCall);

$statusSelectedBlock = "";
if ($statusField !== null) {
    $statusSelectedBlock = '
        $' . $statusField . 'Key = "' . $statusField . '_" . strtolower($this->' . $entityLower . '["' . $statusField . '"]);
        $this->' . $entityLower . '[$' . $statusField . 'Key] = "selected";
';
}

$formControllerTemplate = <<<'EOT'
<?php

namespace Controllers\%MODULE%;

use Controllers\PublicController;
use Views\Renderer;
use Dao\%MODULE%\%LIST% as %DAO%Dao;
use Utilities\Site;
use Utilities\Validators;

class %ENTITY% extends PublicController
{
    private $viewData = [];
    private $mode = "DSP";
    private $modeDescriptions = [
        "DSP" => "Detalle de %s %s",
        "INS" => "Nuevo %ENTITY%",
        "UPD" => "Editar %s %s",
        "DEL" => "Eliminar %s %s"
    ];
    private $readonly = "";
    private $showCommitBtn = true;
    private $%ENTITYLOWER% = [
%DEFAULTS%
    ];
    private $%ENTITYLOWER%_xss_token = "";

    public function run(): void
    {
        try {
            $this->getData();
            if ($this->isPostBack()) {
                if ($this->validateData()) {
                    $this->handlePostAction();
                }
            }
            $this->setViewData();
            Renderer::render("%MODULELOWER%/%ENTITYLOWER%", $this->viewData);
        } catch (\Exception $ex) {
            Site::redirectToWithMsg(
                "index.php?page=%LISTROUTE%",
                $ex->getMessage()
            );
        }
    }

    private function getData()
    {
        $this->mode = $_GET["mode"] ?? "NOF";
        if (isset($this->modeDescriptions[$this->mode])) {
            $this->readonly = $this->mode === "DEL" ? "readonly" : "";
            $this->showCommitBtn = $this->mode !== "DSP";
            if ($this->mode !== "INS") {
                $this->%ENTITYLOWER% = %DAO%Dao::get%ENTITY%ById(%PK_CAST%($_GET["%PK%"]));
                if (!$this->%ENTITYLOWER%) {
                    throw new \Exception("No se encontró el %ENTITY%", 1);
                }
            }
        } else {
            throw new \Exception("Formulario cargado en modalidad invalida", 1);
        }
    }

    private function validateData()
    {
        $errors = [];
        $this->%ENTITYLOWER%_xss_token = $_POST["%ENTITYLOWER%_xss_token"] ?? "";
%VALIDATE_POST%

%VALIDATE_RULES%
        if (count($errors) > 0) {
            foreach ($errors as $key => $value) {
                $this->%ENTITYLOWER%[$key] = $value;
            }
            return false;
        }
        return true;
    }

    private function handlePostAction()
    {
        switch ($this->mode) {
            case "INS":
                $this->handleInsert();
                break;
            case "UPD":
                $this->handleUpdate();
                break;
            case "DEL":
                $this->handleDelete();
                break;
            default:
                throw new \Exception("Modo invalido", 1);
                break;
        }
    }

    private function handleInsert()
    {
        $result = %DAO%Dao::insert%ENTITY%(
%INS_CALL%
        );
        if ($result > 0) {
            Site::redirectToWithMsg(
                "index.php?page=%LISTROUTE%",
                "%ENTITY% creado exitosamente"
            );
        }
    }

    private function handleUpdate()
    {
        $result = %DAO%Dao::update%ENTITY%(
%UPD_CALL%
        );
        if ($result > 0) {
            Site::redirectToWithMsg(
                "index.php?page=%LISTROUTE%",
                "%ENTITY% actualizado exitosamente"
            );
        }
    }

    private function handleDelete()
    {
        $result = %DAO%Dao::delete%ENTITY%($this->%ENTITYLOWER%["%PK%"]);
        if ($result > 0) {
            Site::redirectToWithMsg(
                "index.php?page=%LISTROUTE%",
                "%ENTITY% Eliminado exitosamente"
            );
        }
    }

    private function setViewData(): void
    {
        $this->viewData["mode"] = $this->mode;
        $this->viewData["%ENTITYLOWER%_xss_token"] = $this->%ENTITYLOWER%_xss_token;
        $this->viewData["FormTitle"] = sprintf(
            $this->modeDescriptions[$this->mode],
            $this->%ENTITYLOWER%["%PK%"],
            $this->%ENTITYLOWER%["%SEARCHFIELD%"] ?? ""
        );
        $this->viewData["showCommitBtn"] = $this->showCommitBtn;
        $this->viewData["readonly"] = $this->readonly;
%PK_READONLY%%STATUS_SELECTED%
        $this->viewData["%ENTITYLOWER%"] = $this->%ENTITYLOWER%;
    }
}
EOT;

$pkReadonlyBlock = $pkIsAI ? '' : '        $this->viewData["pk_readonly"] = $this->mode === "INS" ? "" : "readonly disabled";
';
$pkCast = $pkIsAI ? "intval" : "strval";
$fallbackSearch = $searchField !== null ? $searchField : $pkName;

$formControllerCode = str_replace(
    array("%MODULE%", "%MODULELOWER%", "%ENTITY%", "%ENTITYLOWER%", "%LIST%", "%DAO%", "%LISTROUTE%", "%DEFAULTS%", "%VALIDATE_POST%", "%VALIDATE_RULES%", "%INS_CALL%", "%UPD_CALL%", "%PK%", "%PK_CAST%", "%PK_READONLY%", "%STATUS_SELECTED%", "%SEARCHFIELD%"),
    array($module, $moduleLower, $entityName, $entityLower, $listName, $listName, $listRoute, $defaultsStr, $validatePostStr, $validateRulesStr, $insCallStr, $updCallStr, $pkName, $pkCast, $pkReadonlyBlock, $statusSelectedBlock, $fallbackSearch),
    $formControllerTemplate
);
wwWriteFile($formControllerCode, $paths["formController"]);

/* ====== 5. Plantilla de formulario ====== */

$pkBlock = '';
if ($pkIsAI) {
    $pkBlock = '    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="' . $pkName . 'D">Código</label>
      <input class="col-12 col-m-9" readonly disabled type="text" name="' . $pkName . 'D" id="' . $pkName . 'D" placeholder="Código" value="{{' . $pkName . '}}" />
    </div>
';
} else {
    $pkBlock = '    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="' . $pkName . '">Código</label>
      <input class="col-12 col-m-9" {{~pk_readonly}} type="text" name="' . $pkName . '" id="' . $pkName . '" placeholder="Código" value="{{' . $pkName . '}}" />
      {{if ' . $pkName . '_error}}
      <div class="col-12 col-m-9 offset-m-3 error">
        {{' . $pkName . '_error}}
      </div>
      {{endif ' . $pkName . '_error}}
    </div>
';
}

$fieldBlocks = '';
foreach ($fields as $field) {
    $n = $field["name"];
    if (isset($field["isStatus"])) {
        $fieldBlocks .= '    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="' . $n . '">' . wwLabel($n) . '</label>
      <select name="' . $n . '" id="' . $n . '" class="col-12 col-m-9" {{if ~readonly}} readonly disabled {{endif ~readonly}}>
        <option value="ACT" {{' . $n . '_act}}>Activo</option>
        <option value="INA" {{' . $n . '_ina}}>Inactivo</option>
      </select>
      {{if ' . $n . '_error}}
      <div class="col-12 col-m-9 offset-m-3 error">
        {{' . $n . '_error}}
      </div>
      {{endif ' . $n . '_error}}
    </div>
';
    } elseif ($field["base"] === "text") {
        $fieldBlocks .= '    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="' . $n . '">' . wwLabel($n) . '</label>
      <textarea class="col-12 col-m-9"  {{~readonly}} name="' . $n . '" id="' . $n . '" placeholder="' . wwLabel($n) . '">{{' . $n . '}}</textarea>
      {{if ' . $n . '_error}}
      <div class="col-12 col-m-9 offset-m-3 error">
        {{' . $n . '_error}}
      </div>
      {{endif ' . $n . '_error}}
    </div>
';
    } else {
        $inputType = "text";
        if ($field["php"] === "int" || $field["php"] === "float") {
            $inputType = "number";
        } elseif (stripos($n, "pswd") !== false || stripos($n, "clave") !== false) {
            $inputType = "password";
        }
        $fieldBlocks .= '    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="' . $n . '">' . wwLabel($n) . '</label>
      <input class="col-12 col-m-9" {{~readonly}} type="' . $inputType . '" name="' . $n . '" id="' . $n . '" placeholder="' . wwLabel($n) . '" value="{{' . $n . '}}" />
      {{if ' . $n . '_error}}
      <div class="col-12 col-m-9 offset-m-3 error">
        {{' . $n . '_error}}
      </div>
      {{endif ' . $n . '_error}}
    </div>
';
    }
}

$hiddenBlock = $pkIsAI ? '    <input type="hidden" name="' . $pkName . '" value="{{' . $pkName . '}}" />
' : '';
$hiddenBlock .= '    <input type="hidden" name="mode" value="{{~mode}}" />
    <input type="hidden" name="token" value="{{~' . $entityLower . '_xss_token}}" />
';

$formViewTemplate = <<<'EOT'
<section class="container-m row px-4 py-4">
  <h1>{{FormTitle}}</h1>
</section>
<section class="container-m row px-4 py-4">
  {{with %ENTITYLOWER%}}
  <form action="index.php?page=%FORMROUTE%&mode={{~mode}}&%PK%={{%PK%}}" method="POST" class="col-12 col-m-8 offset-m-2">
%PK_BLOCK%%FIELD_BLOCKS%%HIDDEN_BLOCK%    {{endwith %ENTITYLOWER%}}
    <div class="row my-4 align-center flex-end">
      {{if showCommitBtn}}
      <button class="primary col-12 col-m-2" type="submit" name="btnConfirmar">Confirmar</button>
      &nbsp;
      {{endif showCommitBtn}}
      <button class="col-12 col-m-2"type="button" id="btnCancelar">
        {{if showCommitBtn}}
        Cancelar
        {{endif showCommitBtn}}
        {{ifnot showCommitBtn}}
        Regresar
        {{endifnot showCommitBtn}}
      </button>
    </div>
    </div>
  </form>
</section>

<script>
  document.addEventListener("DOMContentLoaded", ()=>{
    const btnCancelar = document.getElementById("btnCancelar");
    btnCancelar.addEventListener("click", (e)=>{
      e.preventDefault();
      e.stopPropagation();
      window.location.assign("index.php?page=%LISTROUTE%");
    });
  });
</script>
EOT;

$formViewCode = str_replace(
    array("%ENTITYLOWER%", "%FORMROUTE%", "%PK%", "%PK_BLOCK%", "%FIELD_BLOCKS%", "%HIDDEN_BLOCK%", "%LISTROUTE%"),
    array($entityLower, $formRoute, $pkName, $pkBlock, $fieldBlocks, $hiddenBlock, $listRoute),
    $formViewTemplate
);
wwWriteFile($formViewCode, $paths["formView"]);

fwrite(STDOUT, "Listo. Pastra: 5 archivos generados para la tabla " . $table . ", ruta " . $listRoute . ", formulario " . $formRoute . "\n");
