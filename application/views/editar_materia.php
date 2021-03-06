<!DOCTYPE html>
<html lang="es">
<head>
  <?php include 'templates/head.php'?>
  <title><?php echo $tituloFormulario.' - '.NOMBRE_SISTEMA?></title>
</head>
<body>
  <div id="wrapper">
    <?php include 'templates/menu-nav.php'?>
    <div class="container">
      <div class="row">
        <!-- Title -->
        <div class="span12">
          <h3>Gestión de Departamentos, Carreras y Materias</h3>
          <p>Esta sección contiene las funcionalidades necesarias para la gestión de las materias pertenecientes a la facultad para la toma de encuestas.</p>
        </div>
      </div>
      
      <div class="row">
        <!-- SideBar -->
        <div class="span3" id="menu">
          <?php $item_submenu = 3;
            include 'templates/submenu-facultad.php';
          ?>
        </div>
        
        <!-- Main -->
        <div class="span9">
          <form action="<?php echo $urlFormulario?>" method="post">
            <div class="control-group">
              <div class="controls">
                <h4><?php echo $tituloFormulario?></h4>
              </div>
            </div>
            <input type="hidden" name="idMateria" value="<?php echo $materia->idMateria?>" required />
            <div class="control-group">
              <label class="control-label" for="campoNombre">Nombre: <span class="opcional" title="Campo obligatorio.">*</span></label>
              <div class="controls">
                <input class="input-block-level" id="campoNombre" type="text" name="nombre" maxlength="60" value="<?php echo $materia->nombre?>" required />
                <?php echo form_error('nombre'); ?>
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="campoCodigo">Código: <span class="opcional" title="Campo obligatorio.">*</span></label>
              <div class="controls">
                <input class="input-block-level" id="campoCodigo" type="text" name="codigo" maxlength="5" value="<?php echo $materia->codigo?>" required />
                <?php echo form_error('codigo'); ?>
              </div>
            </div>
            <div class="control-group">
              <label class="control-label">Opciones: </label>
              <div class="controls">
                <label class="checkbox"><input type="checkbox" name="publicarInformes" value="1" <?php echo ($materia->publicarInformes==RESPUESTA_SI)?'checked="checked"':''?> /> Los informes por Materia son Públicos</label>
                <?php echo form_error('publicarInformes')?>
                <label class="checkbox"><input type="checkbox" name="publicarHistoricos" value="1" <?php echo ($materia->publicarHistoricos==RESPUESTA_SI)?'checked="checked"':''?> /> Los informes Históricos por Materia son Públicos</label>
                <?php echo form_error('publicarHistoricos')?>
                <label class="checkbox"><input type="checkbox" name="publicarDevoluciones" value="1" <?php echo ($materia->publicarDevoluciones==RESPUESTA_SI)?'checked="checked"':''?> /> Los Planes de Mejoras de la Materia son Públicos</label>
                <?php echo form_error('publicarDevoluciones')?>
              </div>
            </div>
            <!-- Botones -->
            <div class="control-group">
              <div class="controls">
                <input class="btn btn-primary" type="submit" name="submit" value="Aceptar" />
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div id="push"></div><br />
  </div>
  <?php include 'templates/footer.php'?>
  
  <script src="<?php echo base_url('js/bootstrap-transition.min.js')?>"></script>
  <script src="<?php echo base_url('js/bootstrap-modal.min.js')?>"></script>
  <script src="<?php echo base_url('js/bootstrap-collapse.min.js')?>"></script>
  <script src="<?php echo base_url('js/bootstrap-dropdown.min.js')?>"></script>
  <script src="<?php echo base_url('js/bootstrap-alert.min.js')?>"></script>
  <script src="<?php echo base_url('js/formularios.js')?>"></script>
</body>
</html>
