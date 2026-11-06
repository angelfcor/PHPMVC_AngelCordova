<?php

namespace Controllers\Security;

use Controllers\PublicController;
use Views\Renderer;
use Dao\Security\Usuarios as UsuariosDao;
use Utilities\Site;
use Utilities\Validators;

class Usuario extends PublicController
{
    private $viewData = [];
    private $mode = "DSP";
    private $modeDescriptions = [
        "DSP" => "Detalle de %s %s",
        "INS" => "Nuevo Usuario",
        "UPD" => "Editar %s %s",
        "DEL" => "Eliminar %s %s"
    ];
    private $readonly = "";
    private $showCommitBtn = true;
    private $usuario = [
        "usercod" => 0,
        "useremail" => "",
        "username" => "",
        "userpswd" => "",
        "userStatus" => "ACT",
        "usertipo" => "NOR"
    ];
    private $usuario_xss_token = "";

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
            Renderer::render("security/usuario", $this->viewData);
        } catch (\Exception $ex) {
            Site::redirectToWithMsg(
                "index.php?page=Security_Usuarios",
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
                $this->usuario = UsuariosDao::getUsuarioById(intval($_GET["usercod"]));
                if (!$this->usuario) {
                    throw new \Exception("No se encontró el Usuario", 1);
                }
                $this->usuario["userStatus"] = $this->usuario["userest"];
            }
        } else {
            throw new \Exception("Formulario cargado en modalidad invalida", 1);
        }
    }

    private function validateData()
    {
        $errors = [];
        $this->usuario_xss_token = $_POST["usuario_xss_token"] ?? "";
        $this->usuario["usercod"] = intval($_POST["usercod"] ?? "");
        $this->usuario["useremail"] = strval($_POST["useremail"] ?? "");
        $this->usuario["username"] = strval($_POST["username"] ?? "");
        $this->usuario["userpswd"] = strval($_POST["userpswd"] ?? "");
        $this->usuario["userStatus"] = strval($_POST["userStatus"] ?? "");
        $this->usuario["usertipo"] = strval($_POST["usertipo"] ?? "");

        if (Validators::IsEmpty($this->usuario["useremail"])) {
            $errors["useremail_error"] = "El correo del usuario es requerido";
        }

        if (Validators::IsEmpty($this->usuario["username"])) {
            $errors["username_error"] = "El nombre del usuario es requerido";
        }

        if (Validators::IsEmpty($this->usuario["userpswd"])) {
            $errors["userpswd_error"] = "La contraseña del usuario es requerida";
        }

        if (!in_array($this->usuario["userStatus"], ["ACT", "INA"])) {
            $errors["userStatus_error"] = "El estado del usuario es invalido";
        }

        if (!in_array($this->usuario["usertipo"], ["NOR", "CON", "CLI"])) {
            $errors["usertipo_error"] = "El tipo de usuario es invalido";
        }

        if (count($errors) > 0) {
            foreach ($errors as $key => $value) {
                $this->usuario[$key] = $value;
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
        $result = UsuariosDao::insertUsuario(
            $this->usuario["useremail"],
            $this->usuario["username"],
            $this->usuario["userpswd"],
            $this->usuario["userStatus"],
            $this->usuario["usertipo"]
        );
        if ($result > 0) {
            Site::redirectToWithMsg(
                "index.php?page=Security_Usuarios",
                "Usuario creado exitosamente"
            );
        }
    }

    private function handleUpdate()
    {
        $result = UsuariosDao::updateUsuario(
            $this->usuario["usercod"],
            $this->usuario["useremail"],
            $this->usuario["username"],
            $this->usuario["userpswd"],
            $this->usuario["userStatus"],
            $this->usuario["usertipo"]
        );
        if ($result > 0) {
            Site::redirectToWithMsg(
                "index.php?page=Security_Usuarios",
                "Usuario actualizado exitosamente"
            );
        }
    }

    private function handleDelete()
    {
        $result = UsuariosDao::deleteUsuario($this->usuario["usercod"]);
        if ($result > 0) {
            Site::redirectToWithMsg(
                "index.php?page=Security_Usuarios",
                "Usuario Eliminado exitosamente"
            );
        }
    }

    private function setViewData(): void
    {
        $this->viewData["mode"] = $this->mode;
        $this->viewData["usuario_xss_token"] = $this->usuario_xss_token;
        $this->viewData["FormTitle"] = sprintf(
            $this->modeDescriptions[$this->mode],
            $this->usuario["usercod"],
            $this->usuario["username"]
        );
        $this->viewData["showCommitBtn"] = $this->showCommitBtn;
        $this->viewData["readonly"] = $this->readonly;

        $userStatusKey = "userStatus_" . strtolower($this->usuario["userStatus"]);
        $this->usuario[$userStatusKey] = "selected";
        $usertipoKey = "usertipo_" . strtolower($this->usuario["usertipo"]);
        $this->usuario[$usertipoKey] = "selected";

        $this->viewData["usuario"] = $this->usuario;
    }
}
