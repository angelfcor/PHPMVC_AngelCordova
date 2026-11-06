<h1>Trabajar con Cursos</h1>
<section class="grid">
  <div class="row">
    <form class="col-12 col-m-8" action="index.php" method="get">
      <div class="flex align-center">
        <div class="col-8 row">
          <input type="hidden" name="page" value="Cursos_Cursos">
          <label class="col-3" for="partialName">Cursodsc</label>
          <input class="col-9" type="text" name="partialName" id="partialName" value="{{partialName}}" />
          <label class="col-3" for="status">Estado</label>
          <select class="col-9" name="status" id="status">
              <option value="EMP" {{status_EMP}}>Todos</option>
              <option value="ACT" {{status_ACT}}>Activo</option>
              <option value="INA" {{status_INA}}>Inactivo</option>
          </select>

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
  <th class="left">
    {{ifnot OrderByCursoid}}
    <a href="index.php?page=Cursos_Cursos&orderBy=cursoid&orderDescending=0">Cursoid <i class="fas fa-sort"></i></a>
    {{endifnot OrderByCursoid}}
    {{if OrderCursoidDesc}}
    <a href="index.php?page=Cursos_Cursos&orderBy=clear&orderDescending=0">Cursoid <i class="fas fa-sort-down"></i></a>
    {{endif OrderCursoidDesc}}
    {{if OrderCursoid}}
    <a href="index.php?page=Cursos_Cursos&orderBy=cursoid&orderDescending=1">Cursoid <i class="fas fa-sort-up"></i></a>
    {{endif OrderCursoid}}
  </th>
  <th class="left">
    {{ifnot OrderByCursodsc}}
    <a href="index.php?page=Cursos_Cursos&orderBy=cursodsc&orderDescending=0">Cursodsc <i class="fas fa-sort"></i></a>
    {{endifnot OrderByCursodsc}}
    {{if OrderCursodscDesc}}
    <a href="index.php?page=Cursos_Cursos&orderBy=clear&orderDescending=0">Cursodsc <i class="fas fa-sort-down"></i></a>
    {{endif OrderCursodscDesc}}
    {{if OrderCursodsc}}
    <a href="index.php?page=Cursos_Cursos&orderBy=cursodsc&orderDescending=1">Cursodsc <i class="fas fa-sort-up"></i></a>
    {{endif OrderCursodsc}}
  </th>
  <th class="left">
    {{ifnot OrderByCursoprecio}}
    <a href="index.php?page=Cursos_Cursos&orderBy=cursoprecio&orderDescending=0">Cursoprecio <i class="fas fa-sort"></i></a>
    {{endifnot OrderByCursoprecio}}
    {{if OrderCursoprecioDesc}}
    <a href="index.php?page=Cursos_Cursos&orderBy=clear&orderDescending=0">Cursoprecio <i class="fas fa-sort-down"></i></a>
    {{endif OrderCursoprecioDesc}}
    {{if OrderCursoprecio}}
    <a href="index.php?page=Cursos_Cursos&orderBy=cursoprecio&orderDescending=1">Cursoprecio <i class="fas fa-sort-up"></i></a>
    {{endif OrderCursoprecio}}
  </th>
        <th>Estado</th>
        <th><a href="index.php?page=Cursos-Curso&mode=INS">Nuevo</a></th>
      </tr>
    </thead>
    <tbody>
      {{foreach cursos}}
      <tr>
        <td>{{cursoid}}</td>
        <td> <a class="link" href="index.php?page=Cursos-Curso&mode=DSP&cursoid={{cursoid}}">{{cursodsc}}</a>
        </td>
        <td class="left">{{cursodescrip}}</td>
        <td class="right">{{cursoprecio}}</td>
        <td class="left">{{cursomodalidad}}</td>
        <td class="center">{{cursoestDsc}}</td>
        <td class="center">
          <a href="index.php?page=Cursos-Curso&mode=UPD&cursoid={{cursoid}}">Editar</a>
          &nbsp;
          <a href="index.php?page=Cursos-Curso&mode=DEL&cursoid={{cursoid}}">Eliminar</a>
        </td>
      </tr>
      {{endfor cursos}}
    </tbody>
  </table>
  {{pagination}}
</section>