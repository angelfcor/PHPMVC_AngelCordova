<section class="container-m row px-4 py-4">
  <h1>{{FormTitle}}</h1>
</section>
<section class="container-m row px-4 py-4">
  {{with curso}}
  <form action="index.php?page=Cursos-Curso&mode={{~mode}}&cursoid={{cursoid}}" method="POST" class="col-12 col-m-8 offset-m-2">
    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="cursoidD">Código</label>
      <input class="col-12 col-m-9" readonly disabled type="text" name="cursoidD" id="cursoidD" placeholder="Código" value="{{cursoid}}" />
    </div>
    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="cursodsc">Cursodsc</label>
      <input class="col-12 col-m-9" {{~readonly}} type="text" name="cursodsc" id="cursodsc" placeholder="Cursodsc" value="{{cursodsc}}" />
      {{if cursodsc_error}}
      <div class="col-12 col-m-9 offset-m-3 error">
        {{cursodsc_error}}
      </div>
      {{endif cursodsc_error}}
    </div>
    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="cursodescrip">Cursodescrip</label>
      <textarea class="col-12 col-m-9"  {{~readonly}} name="cursodescrip" id="cursodescrip" placeholder="Cursodescrip">{{cursodescrip}}</textarea>
      {{if cursodescrip_error}}
      <div class="col-12 col-m-9 offset-m-3 error">
        {{cursodescrip_error}}
      </div>
      {{endif cursodescrip_error}}
    </div>
    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="cursoprecio">Cursoprecio</label>
      <input class="col-12 col-m-9" {{~readonly}} type="number" name="cursoprecio" id="cursoprecio" placeholder="Cursoprecio" value="{{cursoprecio}}" />
      {{if cursoprecio_error}}
      <div class="col-12 col-m-9 offset-m-3 error">
        {{cursoprecio_error}}
      </div>
      {{endif cursoprecio_error}}
    </div>
    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="cursomodalidad">Cursomodalidad</label>
      <input class="col-12 col-m-9" {{~readonly}} type="text" name="cursomodalidad" id="cursomodalidad" placeholder="Cursomodalidad" value="{{cursomodalidad}}" />
      {{if cursomodalidad_error}}
      <div class="col-12 col-m-9 offset-m-3 error">
        {{cursomodalidad_error}}
      </div>
      {{endif cursomodalidad_error}}
    </div>
    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="cursoest">Cursoest</label>
      <select name="cursoest" id="cursoest" class="col-12 col-m-9" {{if ~readonly}} readonly disabled {{endif ~readonly}}>
        <option value="ACT" {{cursoest_act}}>Activo</option>
        <option value="INA" {{cursoest_ina}}>Inactivo</option>
      </select>
      {{if cursoest_error}}
      <div class="col-12 col-m-9 offset-m-3 error">
        {{cursoest_error}}
      </div>
      {{endif cursoest_error}}
    </div>
    <input type="hidden" name="cursoid" value="{{cursoid}}" />
    <input type="hidden" name="mode" value="{{~mode}}" />
    <input type="hidden" name="token" value="{{~curso_xss_token}}" />
    {{endwith curso}}
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
      window.location.assign("index.php?page=Cursos_Cursos");
    });
  });
</script>