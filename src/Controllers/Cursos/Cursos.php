<?php

namespace Controllers\Cursos;

use Controllers\PublicController;
use Utilities\Context;
use Utilities\Paging;
use Dao\Cursos\Cursos as DaoCursos;
use Views\Renderer;

class Cursos extends PublicController
{
    private $partialName = "";
    private $status = "";
    private $orderBy = "";
    private $orderDescending = false;
    private $pageNumber = 1;
    private $itemsPerPage = 10;
    private $viewData = [];
    private $cursos = [];
    private $cursosCount = 0;
    private $pages = 0;

    public function run(): void
    {
        $this->getParamsFromContext();
        $this->getParams();
        $tmpList = DaoCursos::getCursos(
            $this->partialName,
            $this->status,
            $this->orderBy,
            $this->orderDescending,
            $this->pageNumber - 1,
            $this->itemsPerPage
        );
        $this->cursos = $tmpList["cursos"];
        $this->cursosCount = $tmpList["total"];
        $this->pages = $this->cursosCount > 0 ? ceil($this->cursosCount / $this->itemsPerPage) : 1;
        if ($this->pageNumber > $this->pages) {
            $this->pageNumber = $this->pages;
        }
        $this->setParamsToContext();
        $this->setParamsToDataView();
        Renderer::render("cursos/cursos", $this->viewData);
    }

    private function getParams(): void
    {
        $this->partialName = isset($_GET["partialName"]) ? $_GET["partialName"] : $this->partialName;
        $this->status = isset($_GET["status"]) && in_array($_GET["status"], ['ACT', 'INA', 'EMP']) ? $_GET["status"] : $this->status;
        if ($this->status === "EMP") {
            $this->status = "";
        }
        $this->orderBy = isset($_GET["orderBy"]) && in_array($_GET["orderBy"], ["cursoid", "cursodsc", "cursoprecio", "clear"]) ? $_GET["orderBy"] : $this->orderBy;
        if ($this->orderBy === "clear") {
            $this->orderBy = "";
        }
        $this->orderDescending = isset($_GET["orderDescending"]) ? boolval($_GET["orderDescending"]) : $this->orderDescending;
        $this->pageNumber = isset($_GET["pageNum"]) ? intval($_GET["pageNum"]) : $this->pageNumber;
        $this->itemsPerPage = isset($_GET["itemsPerPage"]) ? intval($_GET["itemsPerPage"]) : $this->itemsPerPage;
    }

    private function getParamsFromContext(): void
    {
        $this->partialName = Context::getContextByKey("cursos_partialName");
        $this->status = Context::getContextByKey("cursos_status");
        $this->orderBy = Context::getContextByKey("cursos_orderBy");
        $this->orderDescending = boolval(Context::getContextByKey("cursos_orderDescending"));
        $this->pageNumber = intval(Context::getContextByKey("cursos_page"));
        $this->itemsPerPage = intval(Context::getContextByKey("cursos_itemsPerPage"));
        if ($this->pageNumber < 1) $this->pageNumber = 1;
        if ($this->itemsPerPage < 1) $this->itemsPerPage = 10;
    }

    private function setParamsToContext(): void
    {
        Context::setContext("cursos_partialName", $this->partialName, true);
        Context::setContext("cursos_status", $this->status, true);
        Context::setContext("cursos_orderBy", $this->orderBy, true);
        Context::setContext("cursos_orderDescending", $this->orderDescending, true);
        Context::setContext("cursos_page", $this->pageNumber, true);
        Context::setContext("cursos_itemsPerPage", $this->itemsPerPage, true);
    }

    private function setParamsToDataView(): void
    {
        $this->viewData["partialName"] = $this->partialName;
        $this->viewData["status"] = $this->status;
        $this->viewData["orderBy"] = $this->orderBy;
        $this->viewData["orderDescending"] = $this->orderDescending;
        $this->viewData["pageNum"] = $this->pageNumber;
        $this->viewData["itemsPerPage"] = $this->itemsPerPage;
        $this->viewData["cursosCount"] = $this->cursosCount;
        $this->viewData["pages"] = $this->pages;
        $this->viewData["cursos"] = $this->cursos;
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
            $this->cursosCount,
            $this->itemsPerPage,
            $this->pageNumber,
            "index.php?page=Cursos_Cursos",
            "Cursos_Cursos"
        );
        $this->viewData["pagination"] = $pagination;
    }
}