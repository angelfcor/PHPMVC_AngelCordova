<?php

namespace Controllers;
use Views\Renderer;
use Dao\Profile\Me as MeDao;
use Dao\Misc\Proyectos as ProyectosDao;

class Me extends PublicController {
    public function run() :void {
        $tmpViewData = MeDao::getProfile();
        $tmpViewData["txtNombre"] = "";
        $tmpProyecto = ProyectosDao::getAll();
        $tmpViewData["proyectos"] = $tmpProyecto;
        if ($this->isPostBack()) {
            $tmpViewData["txtNombre"] = $_POST["txtNombre"] ?? "";
            $tmpViewData["postMessage"] = $tmpViewData["txtNombre"] ? 
            sprintf("Bienvenido %s",$tmpViewData["txtNombre"]
            ) : "";

            ProyectosDao::crear($tmpViewData["txtNombre"], 'ACT','yo','fulano');
        }

        Renderer::render("me", $tmpViewData);

    }
}