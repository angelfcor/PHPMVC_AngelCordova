<?php

namespace Controllers\Security;

use Controllers\PublicController;
use Views\Renderer;
use Dao\Security\Funciones as FuncionesDao;
use Utilities\Site;
use Utilities\Validators;

class Funcion extends PublicController
{
    private $viewData = [];
    private $mode = "DSP";
    private $modeDescriptions = [
        "DSP" => "Detalle de %s %s",
        "INS" => "Nueva Función",
        "UPD" => "Editar %s %s",
        "DEL" => "Eliminar %s %s"
    ];
    private $readonly = "";
    private $showCommitBtn = true;
    private $funcion = [
        "fncod" => "",
        "fndsc" => "",
        "fntyp" => "FNC",
        "fnStatus" => "ACT"
    ];
    private $funcion_xss_token = "";

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
            Renderer::render("security/funcion", $this->viewData);
        } catch (\Exception $ex) {
            Site::redirectToWithMsg(
                "index.php?page=Security_Funciones",
                $ex->getMessage()
            );
        }
    }

    private function getData()
    {
        $this->mode = $_GET["mode"] ?? "NOF";
        if (isset($this->modeDescriptions[$this->mode])) {
            $this->readonly = ($this->mode === "DEL" || $this->mode === "UPD") ? "readonly" : "";
            $this->showCommitBtn = $this->mode !== "DSP";
            if ($this->mode !== "INS") {
                $this->funcion = FuncionesDao::getFuncionById(strval($_GET["fncod"]));
                if (!$this->funcion) {
                    throw new \Exception("No se encontró la Función", 1);
                }
                $this->funcion["fnStatus"] = $this->funcion["fnest"];
            }
        } else {
            throw new \Exception("Formulario cargado en modalidad invalida", 1);
        }
    }

    private function validateData()
    {
        $errors = [];
        $this->funcion_xss_token = $_POST["funcion_xss_token"] ?? "";
        $this->funcion["fncod"] = strval($_POST["fncod"] ?? "");
        $this->funcion["fndsc"] = strval($_POST["fndsc"] ?? "");
        $this->funcion["fntyp"] = strval($_POST["fntyp"] ?? "");
        $this->funcion["fnStatus"] = strval($_POST["fnStatus"] ?? "");

        if (Validators::IsEmpty($this->funcion["fncod"])) {
            $errors["fncod_error"] = "El código de la función es requerido";
        }

        if (Validators::IsEmpty($this->funcion["fndsc"])) {
            $errors["fndsc_error"] = "La descripción de la función es requerida";
        }

        if (Validators::IsEmpty($this->funcion["fntyp"])) {
            $errors["fntyp_error"] = "El tipo de la función es requerido";
        }

        if (!in_array($this->funcion["fnStatus"], ["ACT", "INA"])) {
            $errors["fnStatus_error"] = "El estado de la función es invalido";
        }

        if (count($errors) > 0) {
            foreach ($errors as $key => $value) {
                $this->funcion[$key] = $value;
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
        $result = FuncionesDao::insertFuncion(
            $this->funcion["fncod"],
            $this->funcion["fndsc"],
            $this->funcion["fntyp"],
            $this->funcion["fnStatus"]
        );
        if ($result > 0) {
            Site::redirectToWithMsg(
                "index.php?page=Security_Funciones",
                "Función creada exitosamente"
            );
        }
    }

    private function handleUpdate()
    {
        $result = FuncionesDao::updateFuncion(
            $this->funcion["fncod"],
            $this->funcion["fndsc"],
            $this->funcion["fntyp"],
            $this->funcion["fnStatus"]
        );
        if ($result > 0) {
            Site::redirectToWithMsg(
                "index.php?page=Security_Funciones",
                "Función actualizada exitosamente"
            );
        }
    }

    private function handleDelete()
    {
        $result = FuncionesDao::deleteFuncion($this->funcion["fncod"]);
        if ($result > 0) {
            Site::redirectToWithMsg(
                "index.php?page=Security_Funciones",
                "Función Eliminada exitosamente"
            );
        }
    }

    private function setViewData(): void
    {
        $this->viewData["mode"] = $this->mode;
        $this->viewData["funcion_xss_token"] = $this->funcion_xss_token;
        $this->viewData["FormTitle"] = sprintf(
            $this->modeDescriptions[$this->mode],
            $this->funcion["fncod"],
            $this->funcion["fndsc"]
        );
        $this->viewData["showCommitBtn"] = $this->showCommitBtn;
        $this->viewData["readonly"] = $this->readonly;
        $this->viewData["pk_readonly"] = $this->mode === "INS" ? "" : "readonly disabled";

        $fnStatusKey = "fnStatus_" . strtolower($this->funcion["fnStatus"]);
        $this->funcion[$fnStatusKey] = "selected";

        $this->viewData["funcion"] = $this->funcion;
    }
}
