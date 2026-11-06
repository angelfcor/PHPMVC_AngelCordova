<?php

namespace Controllers\Security;

use Controllers\PublicController;
use Views\Renderer;
use Dao\Security\Roles as RolesDao;
use Utilities\Site;
use Utilities\Validators;

class Role extends PublicController
{
    private $viewData = [];
    private $mode = "DSP";
    private $modeDescriptions = [
        "DSP" => "Detalle de %s %s",
        "INS" => "Nuevo Rol",
        "UPD" => "Editar %s %s",
        "DEL" => "Eliminar %s %s"
    ];
    private $readonly = "";
    private $showCommitBtn = true;
    private $role = [
        "rolescod" => "",
        "rolesdsc" => "",
        "roleStatus" => "ACT"
    ];
    private $role_xss_token = "";

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
            Renderer::render("security/role", $this->viewData);
        } catch (\Exception $ex) {
            Site::redirectToWithMsg(
                "index.php?page=Security_Roles",
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
                $this->role = RolesDao::getRoleById(strval($_GET["rolescod"]));
                if (!$this->role) {
                    throw new \Exception("No se encontró el Rol", 1);
                }
                $this->role["roleStatus"] = $this->role["rolesest"];
            }
        } else {
            throw new \Exception("Formulario cargado en modalidad invalida", 1);
        }
    }

    private function validateData()
    {
        $errors = [];
        $this->role_xss_token = $_POST["role_xss_token"] ?? "";
        $this->role["rolescod"] = strval($_POST["rolescod"] ?? "");
        $this->role["rolesdsc"] = strval($_POST["rolesdsc"] ?? "");
        $this->role["roleStatus"] = strval($_POST["roleStatus"] ?? "");

        if (Validators::IsEmpty($this->role["rolescod"])) {
            $errors["rolescod_error"] = "El código del rol es requerido";
        }

        if (Validators::IsEmpty($this->role["rolesdsc"])) {
            $errors["rolesdsc_error"] = "La descripción del rol es requerida";
        }

        if (!in_array($this->role["roleStatus"], ["ACT", "INA"])) {
            $errors["roleStatus_error"] = "El estado del rol es invalido";
        }

        if (count($errors) > 0) {
            foreach ($errors as $key => $value) {
                $this->role[$key] = $value;
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
        $result = RolesDao::insertRole(
            $this->role["rolescod"],
            $this->role["rolesdsc"],
            $this->role["roleStatus"]
        );
        if ($result > 0) {
            Site::redirectToWithMsg(
                "index.php?page=Security_Roles",
                "Rol creado exitosamente"
            );
        }
    }

    private function handleUpdate()
    {
        $result = RolesDao::updateRole(
            $this->role["rolescod"],
            $this->role["rolesdsc"],
            $this->role["roleStatus"]
        );
        if ($result > 0) {
            Site::redirectToWithMsg(
                "index.php?page=Security_Roles",
                "Rol actualizado exitosamente"
            );
        }
    }

    private function handleDelete()
    {
        $result = RolesDao::deleteRole($this->role["rolescod"]);
        if ($result > 0) {
            Site::redirectToWithMsg(
                "index.php?page=Security_Roles",
                "Rol Eliminado exitosamente"
            );
        }
    }

    private function setViewData(): void
    {
        $this->viewData["mode"] = $this->mode;
        $this->viewData["role_xss_token"] = $this->role_xss_token;
        $this->viewData["FormTitle"] = sprintf(
            $this->modeDescriptions[$this->mode],
            $this->role["rolescod"],
            $this->role["rolesdsc"]
        );
        $this->viewData["showCommitBtn"] = $this->showCommitBtn;
        $this->viewData["readonly"] = $this->readonly;
        $this->viewData["pk_readonly"] = $this->mode === "INS" ? "" : "readonly disabled";

        $roleStatusKey = "roleStatus_" . strtolower($this->role["roleStatus"]);
        $this->role[$roleStatusKey] = "selected";

        $this->viewData["role"] = $this->role;
    }
}
