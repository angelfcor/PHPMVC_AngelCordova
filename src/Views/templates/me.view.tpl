<h1>Acerca de Yo</h1>
<section>
    <table>
        <tr>
            <th>Nombre</th>
            <td>{{nombre}} {{apellido}}</td>
        </tr>
        <tr>
            <th>Correo</th>
            <td>{{correo}}</td>
        </tr>
        <tr>
            <th>Teléfono</th>
            <td>{{telefono}}</td>
        </tr>
    </table>
</section>
<section>
    <form action="index.php?page=Me" method="POST">
        <div>
            <label for="txtNombre">Nombre Completo</label>
            <input 
                type="text" 
                id="txtNombre" 
                name="txtNombre" 
                value="{{txtNombre}}" 
                placeholder="Nombre Completo"/>
        </div>
            <button type="submit" name="btnEnviar">Enviar</button>
    </form>
</section>
{{if postMessage}}
<section>
    <em>
        {{postMessage}}
    </em>
</section>
{{endif postMessage}}

<!--Para ver los cambios: http://localhost/PHPMVC_AngelCordova/Index.php?page=Me-->
