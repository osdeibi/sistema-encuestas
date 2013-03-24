<!DOCTYPE html>
<html lang="es">
<head>
  <?php include 'templates/head.php'?>
  <title>Generar Informe por Clave de acceso - <?php echo NOMBRE_SISTEMA?></title>
  <style>
    .form-horizontal .controls {margin-left: 90px;}
    .form-horizontal .control-label {width: 70px; float: left;}
  </style>
</head>
<body>
  <div id="wrapper">
    <?php include 'templates/menu-nav.php'?>
    <div class="container">
      <div class="row">
        <!-- Titulo -->
        <div class="span12">
          <h3>Informes por Encuesta</h3>
          <p>Esta sección permite acceder a un informe que contiene todas las respuestas dadas por un encuestado para una materia de una carrera y encuesta en particular.</p>
        </div>
      </div>
      
      <div class="row">
        <!-- Main -->
        <div id="contenedor" class="span12">
          <h4>Solicitar informe por clave de acceso</h4>
          <form class="form-horizontal" action="<?php echo site_url('informes/clave')?>" method="post">
            
            <div class="control-group">
              <label class="control-label" for="buscarCarrera">Carrera:</label>
              <div class="controls">
                <input class="input-block-level" id="buscarCarrera" name="buscarCarrera" type="text" autocomplete="off" data-provide="typeahead" value="<?php echo set_value('buscarCarrera')?>" required>
                <input type="hidden" name="idCarrera" value="<?php echo set_value('idCarrera')?>" required/>
                <?php echo form_error('idCarrera')?>
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="buscarMateria">Materia:</label>
              <div class="controls">
                <input class="input-block-level" id="buscarMateria" name="buscarMateria" type="text" autocomplete="off" data-provide="typeahead" value="<?php echo set_value('buscarMateria')?>" required>
                <input type="hidden" name="idMateria" value="<?php echo set_value('idMateria')?>" required/>
                <?php echo form_error('idMateria')?>
              </div>
            </div>
            <div class="control-group">  
              <label class="control-label" for="buscarEncuesta">Año:</label>
              <div class="controls">
                <input class="input-block-level" id="buscarEncuesta" name="buscarEncuesta" type="text" autocomplete="off" data-provide="typeahead" value="<?php echo set_value('buscarEncuesta')?>" required>
                <input type="hidden" name="idEncuesta" value="<?php echo set_value('idEncuesta')?>" required/>
                <?php echo form_error('idEncuesta')?>
                <input type="hidden" name="idFormulario" value="<?php echo set_value('idFormulario')?>" required/>
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="listaClaves">Accesos: </label>
              <div class="controls">
                <select id="listaClaves" name="idClave" size="3" required>
                </select>
                <?php echo form_error('idClave')?>
              </div>
            </div>
            <div class="control-group">
              <div class="controls">
                <label class="checkbox"><input type="checkbox" name="indicesSecciones" value="1" <?php echo set_checkbox('indicesSecciones', '1', TRUE)?> />Incluir promedio de índices de secciones</label>
                <label class="checkbox"><input type="checkbox" name="indicesDocentes" value="1" <?php echo set_checkbox('indicesDocentes', '1', TRUE)?> />Incluir promedio de índices para cada docente</label>
                <label class="checkbox"><input type="checkbox" name="indiceGlobal" value="1" <?php echo set_checkbox('indiceGlobal', '1', TRUE)?> />Incluir indice general</label>
              </div>
            </div>
            <div class="controls btn-group">
              <input class="btn btn-primary" type="submit" name="submit" value="Aceptar" />
            </div>
          </form>
        </div>
      </div>
    </div>
    <div id="push"></div><br />
  </div>
  <?php include 'templates/footer.php'?>  
  
  <!-- Le javascript -->
  <script src="<?php echo base_url('js/bootstrap-transition.js')?>"></script>
  <script src="<?php echo base_url('js/bootstrap-modal.js')?>"></script>
  <script src="<?php echo base_url('js/bootstrap-collapse.js')?>"></script>
  <script src="<?php echo base_url('js/bootstrap-dropdown.js')?>"></script>
  <script src="<?php echo base_url('js/bootstrap-alert.js')?>"></script>
  <script src="<?php echo base_url('js/bootstrap-typeahead.js')?>"></script>
  <script src="<?php echo base_url('js/formularios.js')?>"></script>
  <script src="<?php echo base_url('js/autocompletar.js')?>"></script>
  <script>
    autocompletar_carrera("<?php echo site_url('carreras/buscarAJAX')?>");
    autocompletar_encuesta("<?php echo site_url('encuestas/buscarAJAX')?>");
    autocompletar_materia("<?php echo site_url('carreras/buscarMateriasAJAX')?>");
    
    //listar claves de acceso al elegir la encuesta
    $('#buscarEncuesta').change(function(){
      $('#listaClaves').empty();
      var idEncuesta = $('#buscarEncuesta').siblings('input[name="idEncuesta"]').val();
      var idFormulario = $('#buscarEncuesta').siblings('input[name="idFormulario"]').val();
      var idMateria = $('#buscarMateria').siblings('input[name="idMateria"]').val();
      var idCarrera = $('#buscarCarrera').siblings('input[name="idCarrera"]').val();
      //si no se ingreso algun campo, terminar sin buscar nada
      if (idEncuesta==''||idFormulario==''||idMateria==''||idCarrera=='') return;
      $.ajax({
        type: "POST", 
        url: "<?php echo site_url('claves/listarClavesMateriaAJAX')?>", 
        data: { 
          idEncuesta: idEncuesta,
          idFormulario: idFormulario,
          idMateria: idMateria,
          idCarrera: idCarrera
        }
      }).done(function(msg){
        var filas = msg.split("\n");
        for (var i=0; i<filas.length-1; i++){
          if (filas[i].length<5) continue;
          //separo datos en columnas
          var columnas = filas[i].split("\t");
          var id = columnas[0];
          var datos = columnas[1]+" - "+columnas[3];
          //agregar fila a la lista desplegable
          $('#listaClaves').append('<option value="'+id+'">'+datos+'</option>');
        }
        $('#listaClaves').children().first().attr('selected','');
      });
    });
  </script>
</body>
</html>