<section class="container-m row px-4 py-4">
  <h1>{{FormTitle}}</h1>
</section>
<section class="container-m row px-4 py-4">
  {{with role}}
  <form action="index.php?page=Security_Role&mode={{~mode}}&rolescod={{rolescod}}" method="POST" class="col-12 col-m-8 offset-m-2">
    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="rolescod">Código</label>
      <input class="col-12 col-m-9" {{~pk_readonly}} type="text" name="rolescod" id="rolescod" placeholder="Código del Rol" value="{{rolescod}}" />
      {{if rolescod_error}}
      <div class="col-12 col-m-9 offset-m-3 error">
        {{rolescod_error}}
      </div>
      {{endif rolescod_error}}
    </div>
    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="rolesdsc">Descripción</label>
      <input class="col-12 col-m-9" {{~readonly}} type="text" name="rolesdsc" id="rolesdsc" placeholder="Descripción del Rol" value="{{rolesdsc}}" />
      {{if rolesdsc_error}}
      <div class="col-12 col-m-9 offset-m-3 error">
        {{rolesdsc_error}}
      </div>
      {{endif rolesdsc_error}}
    </div>
    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="roleStatus">Estado</label>
      <select name="roleStatus" id="roleStatus" class="col-12 col-m-9" {{if ~readonly}} readonly disabled {{endif ~readonly}}>
        <option value="ACT" {{roleStatus_act}}>Activo</option>
        <option value="INA" {{roleStatus_ina}}>Inactivo</option>
      </select>
      {{if roleStatus_error}}
      <div class="col-12 col-m-9 offset-m-3 error">
        {{roleStatus_error}}
      </div>
      {{endif roleStatus_error}}
    </div>
    <input type="hidden" name="mode" value="{{~mode}}" />
    <input type="hidden" name="rolescod" value="{{rolescod}}" />
    <input type="hidden" name="token" value="{{~role_xss_token}}" />
    {{endwith role}}
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
      window.location.assign("index.php?page=Security_Roles");
    });
  });
</script>
