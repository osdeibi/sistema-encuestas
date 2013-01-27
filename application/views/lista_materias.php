<!DOCTYPE html>

<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="es"> <!--<![endif]-->
<head>
  <?php include 'elements/head.php'?> 
  <title>Lista Materias</title>
  <style>
    .button-group li a{
      margin-right: 5px;
    }
  </style>
</head>
<body>
  <!-- Header -->
  <div class="row">
    <div class="twelve columns">
      <?php include 'elements/header.php'?>
    </div>
  </div>
  
  <!-- Main Section -->
  <div class="row">
    <!-- Main Section -->  
    <div id="Main" class="nine columns push-three">
      <div class="row">
        <div class="twelve columns">
          <h3>Materias</h3>
          <?php if(count($tabla)== 0):?>
            <p>No se encontraron materias.</p>
          <?php else:?>
            <table class="twelve">
              <thead>
                <th>Nombre</th>
                <th>Codigo</th>
                <th>Alumnos</th>
                <th>Acciones</th>
              </thead>
              <?php foreach($tabla as $fila): ?>  
                <tr>
                  <td><a href="<?php echo site_url("materias/ver/".$fila['IdMateria'])?>"><?php echo $fila['Nombre']?></a></td>
                  <td><?php echo $fila['Codigo']?></td>
                  <td><?php echo $fila['Alumnos']?></td>
                  <td><a class="eliminar" href="" value="<?php echo $fila['IdMateria']?>">Eliminar</a></td>
                </tr>
              <?php endforeach ?>
            </table>
          <?php endif ?>
          <?php echo $paginacion ?>
        </div>
      </div>
      <div class="row">
        <div class="three mobile-one columns">
          <a class="button" data-reveal-id="modalNueva">Nueva Materia</a>
        </div>       
      </div>
    </div>

    <!-- Nav Sidebar -->
    <div class="three columns pull-nine">
      <!-- Panel de navegación -->
      <?php include 'elements/nav-sidebar.php'?>
    </div>    
  </div>

  <!-- Footer -->    
  <div class="row">    
    <?php include 'elements/footer.php'?>
  </div>
  
  
  <!-- ventana modal para agregar una materia -->
  <div id="modalNueva" class="reveal-modal medium">
    <?php
      //a donde mandar los datos editados para darse de alta
      $link = site_url('materias/nueva');  
      $materia = array('IdMateria' => 0, 'Nombre' => '', 'Codigo' => '');
      include 'elements/form-editar-materia.php'; 
    ?>
    <a class="close-reveal-modal">&#215;</a>
  </div>
  
  <!-- ventana modal para desasociar materias a la carrera -->
  <div id="modalEliminar" class="reveal-modal medium">
    <form action="<?php echo site_url('materias/eliminar')?>" method="post">
      <h3>Eliminar materia</h3>
      <p>¿Desea continuar?</p>
      <input type="hidden" name="IdMateria" value="" />
      <div class="row">         
        <div class="ten columns centered">
          <div class="six mobile-one columns push-one-mobile">
            <input class="button cancelar" type="button" value="Cancelar"/>
          </div>
          <div class="six mobile-one columns pull-one-mobile ">
            <input class="button" type="submit" name="submit" value="Aceptar" />
          </div>
        </div>
      </div>
    </form>
    <a class="close-reveal-modal">&#215;</a>
  </div>
    
  <!-- Included JS Files (Compressed) -->
  <script src="<?php echo base_url()?>js/foundation/foundation.min.js"></script>
  <!-- Initialize JS Plugins -->
  <script src="<?php echo base_url()?>js/foundation/app.js"></script>
  
  <script>
    $('.cancelar').click(function(){
      $('.cancelar').trigger('reveal:close'); //cerrar ventana
    });
    
    $('.eliminar').click(function(){
      IdMateria = $(this).attr('value');
      $('#modalEliminar input[name="IdMateria"]').val(IdMateria);
      $("#modalEliminar").reveal();
      return false;
    });
  </script>
</body>
</html>