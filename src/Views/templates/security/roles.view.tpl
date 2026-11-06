<h1>Trabajar con Roles</h1>
<section class="grid">
  <div class="row">
    <form class="col-12 col-m-8" action="index.php" method="get">
      <div class="flex align-center">
        <div class="col-8 row">
          <input type="hidden" name="page" value="Security_Roles">
          <label class="col-3" for="partialName">Código o Descripción</label>
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
          {{ifnot OrderByRolescod}}
          <a href="index.php?page=Security_Roles&orderBy=rolescod&orderDescending=0">Código <i class="fas fa-sort"></i></a>
          {{endifnot OrderByRolescod}}
          {{if OrderRolescodDesc}}
          <a href="index.php?page=Security_Roles&orderBy=clear&orderDescending=0">Código <i class="fas fa-sort-down"></i></a>
          {{endif OrderRolescodDesc}}
          {{if OrderRolescod}}
          <a href="index.php?page=Security_Roles&orderBy=rolescod&orderDescending=1">Código <i class="fas fa-sort-up"></i></a>
          {{endif OrderRolescod}}
        </th>
        <th class="left">
          {{ifnot OrderByRolesdsc}}
          <a href="index.php?page=Security_Roles&orderBy=rolesdsc&orderDescending=0">Descripción <i class="fas fa-sort"></i></a>
          {{endifnot OrderByRolesdsc}}
          {{if OrderRolesdscDesc}}
          <a href="index.php?page=Security_Roles&orderBy=clear&orderDescending=0">Descripción <i class="fas fa-sort-down"></i></a>
          {{endif OrderRolesdscDesc}}
          {{if OrderRolesdsc}}
          <a href="index.php?page=Security_Roles&orderBy=rolesdsc&orderDescending=1">Descripción <i class="fas fa-sort-up"></i></a>
          {{endif OrderRolesdsc}}
        </th>
        <th>Estado</th>
        <th><a href="index.php?page=Security_Role&mode=INS">Nuevo</a></th>
      </tr>
    </thead>
    <tbody>
      {{foreach roles}}
      <tr>
        <td> <a class="link" href="index.php?page=Security_Role&mode=DSP&rolescod={{rolescod}}">{{rolescod}}</a>
        </td>
        <td>{{rolesdsc}}</td>
        <td class="center">{{roleStatusDsc}}</td>
        <td class="center">
          <a href="index.php?page=Security_Role&mode=UPD&rolescod={{rolescod}}">Editar</a>
          &nbsp;
          <a href="index.php?page=Security_Role&mode=DEL&rolescod={{rolescod}}">Eliminar</a>
        </td>
      </tr>
      {{endfor roles}}
    </tbody>
  </table>
  {{pagination}}
</section>
