<?php

namespace Controllers;
use Views\Renderer;
use Dao\Profile\Me as MeDao;

class Me extends PublicController {
    public function run() :void {
        $tmpViewData = MeDao::getProfile();
        $tmpViewData["txtNombre"] = "";
        if ($this->isPostBack()) {
            $tmpViewData["txtNombre"] = $_POST["txtNombre"] ?? "";
            $tmpViewData["postMessage"] = $tmpViewData["txtNombre"] ? 
            sprintf("Bienvenido %s",$tmpViewData["txtNombre"]
            ) : "";
        }

        Renderer::render("me", $tmpViewData);

    }
}