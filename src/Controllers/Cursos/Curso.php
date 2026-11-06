<?php

namespace Controllers\Cursos;

use Controllers\PublicController;
use Views\Renderer;
use Dao\Cursos\Cursos as CursosDao;
use Utilities\Site;
use Utilities\Validators;

class Curso extends PublicController
{
    private $viewData = [];
    private $mode = "DSP";
    private $modeDescriptions = [
        "DSP" => "Detalle de %s %s",
        "INS" => "Nuevo Curso",
        "UPD" => "Editar %s %s",
        "DEL" => "Eliminar %s %s"
    ];
    private $readonly = "";
    private $showCommitBtn = true;
    private $curso = [
        "cursoid" => 0,
        "cursodsc" => "",
        "cursodescrip" => "",
        "cursoprecio" => 0,
        "cursomodalidad" => "",
        "cursoest" => "ACT"
    ];
    private $curso_xss_token = "";

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
            Renderer::render("cursos/curso", $this->viewData);
        } catch (\Exception $ex) {
            Site::redirectToWithMsg(
                "index.php?page=Cursos_Cursos",
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
                $this->curso = CursosDao::getCursoById(intval($_GET["cursoid"]));
                if (!$this->curso) {
                    throw new \Exception("No se encontró el Curso", 1);
                }
            }
        } else {
            throw new \Exception("Formulario cargado en modalidad invalida", 1);
        }
    }

    private function validateData()
    {
        $errors = [];
        $this->curso_xss_token = $_POST["curso_xss_token"] ?? "";
        $this->curso["cursoid"] = intval($_POST["cursoid"] ?? "");
        $this->curso["cursodsc"] = strval($_POST["cursodsc"] ?? "");
        $this->curso["cursodescrip"] = strval($_POST["cursodescrip"] ?? "");
        $this->curso["cursoprecio"] = floatval($_POST["cursoprecio"] ?? "");
        $this->curso["cursomodalidad"] = strval($_POST["cursomodalidad"] ?? "");
        $this->curso["cursoest"] = strval($_POST["cursoest"] ?? "");

        if (Validators::IsEmpty($this->curso["cursodsc"])) {
            $errors["cursodsc_error"] = "El campo cursodsc es requerido";
        }

        if (Validators::IsEmpty($this->curso["cursodescrip"])) {
            $errors["cursodescrip_error"] = "El campo cursodescrip es requerido";
        }

        if (Validators::IsEmpty($this->curso["cursoprecio"]) && $this->curso["cursoprecio"] <= 0) {
            $errors["cursoprecio_error"] = "El campo cursoprecio es requerido y debe ser un valor mayor a cero";
        }

        if (Validators::IsEmpty($this->curso["cursomodalidad"])) {
            $errors["cursomodalidad_error"] = "El campo cursomodalidad es requerido";
        }

        if (!in_array($this->curso["cursoest"], ["ACT", "INA"])) {
            $errors["cursoest_error"] = "El estado del curso es invalido";
        }
        if (count($errors) > 0) {
            foreach ($errors as $key => $value) {
                $this->curso[$key] = $value;
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
        $result = CursosDao::insertCurso(
            $this->curso["cursodsc"],
            $this->curso["cursodescrip"],
            $this->curso["cursoprecio"],
            $this->curso["cursomodalidad"],
            $this->curso["cursoest"]
        );
        if ($result > 0) {
            Site::redirectToWithMsg(
                "index.php?page=Cursos_Cursos",
                "Curso creado exitosamente"
            );
        }
    }

    private function handleUpdate()
    {
        $result = CursosDao::updateCurso(
            $this->curso["cursoid"],
            $this->curso["cursodsc"],
            $this->curso["cursodescrip"],
            $this->curso["cursoprecio"],
            $this->curso["cursomodalidad"],
            $this->curso["cursoest"]
        );
        if ($result > 0) {
            Site::redirectToWithMsg(
                "index.php?page=Cursos_Cursos",
                "Curso actualizado exitosamente"
            );
        }
    }

    private function handleDelete()
    {
        $result = CursosDao::deleteCurso($this->curso["cursoid"]);
        if ($result > 0) {
            Site::redirectToWithMsg(
                "index.php?page=Cursos_Cursos",
                "Curso Eliminado exitosamente"
            );
        }
    }

    private function setViewData(): void
    {
        $this->viewData["mode"] = $this->mode;
        $this->viewData["curso_xss_token"] = $this->curso_xss_token;
        $this->viewData["FormTitle"] = sprintf(
            $this->modeDescriptions[$this->mode],
            $this->curso["cursoid"],
            $this->curso["cursodsc"] ?? ""
        );
        $this->viewData["showCommitBtn"] = $this->showCommitBtn;
        $this->viewData["readonly"] = $this->readonly;

        $cursoestKey = "cursoest_" . strtolower($this->curso["cursoest"]);
        $this->curso[$cursoestKey] = "selected";

        $this->viewData["curso"] = $this->curso;
    }
}