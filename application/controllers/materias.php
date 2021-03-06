<?php

/**
 * Controlador para la gestión de materias
 */
class Materias extends CI_Controller{
  
  var $data = array(); //datos para mandar a las vistas
  
  function __construct() {
    parent::__construct();
    $this->load->library(array('session', 'ion_auth', 'form_validation'));
    //doy formato al mensaje de error de validación de formulario
    $this->form_validation->set_error_delimiters(ERROR_DELIMITER_START, ERROR_DELIMITER_END);
    //leo los datos del usuario logueado
    $this->data['usuarioLogin'] = $this->ion_auth->user()->row();
    //leo los mensajes generados en la página anterior
    $this->data['resultadoTipo'] = $this->session->flashdata('resultadoTipo');
    $this->data['resultadoOperacion'] = $this->session->flashdata('resultadoOperacion');
  }
  
  public function index(){
    //por defecto, muestro el listado de materias
    $this->listar();
  }
  
  /*
   * Muestra el listado de materias.
   */
  public function listar($pagInicio=0){
    if (!$this->ion_auth->logged_in()){
      $this->session->set_flashdata('resultadoOperacion', 'Debe iniciar sesión para realizar esta operación.');
      $this->session->set_flashdata('resultadoTipo', ALERT_WARNING);
      redirect('usuarios/login');
    }
    elseif (!$this->ion_auth->in_group(array('admin','decanos','docentes'))){
      $this->session->set_flashdata('resultadoOperacion', 'No tiene permisos para realizar esta operación.');
      $this->session->set_flashdata('resultadoTipo', ALERT_WARNING);
      redirect('/');
    }
    
    //cargo modelos, librerias, etc.
    $this->load->library('pagination');
    $this->load->model('Materia');
    $this->load->model('Gestor_materias','gm');
    
    //chequeo parámetros de entrada
    $pagInicio = (int)$pagInicio;
    
    //obtengo lista de materias
    $lista = $this->gm->listar($pagInicio, PER_PAGE);
    
    //genero la lista de links de paginación
    $this->pagination->initialize(array(
      'base_url' => site_url('materias/listar'),
      'total_rows' => $this->gm->cantidad()
    ));

    //envio datos a la vista
    $this->data['lista'] = &$lista; //array de datos de las materias
    $this->data['paginacion'] = $this->pagination->create_links(); //html de la barra de paginación
    $this->load->view('lista_materias', $this->data);
  }

  /*
   * Ver y editar datos relacionados a una materia
   */
  public function ver($idMateria=null, $pagInicio=0){
    if (!$this->ion_auth->logged_in()){
      $this->session->set_flashdata('resultadoOperacion', 'Debe iniciar sesión para realizar esta operación.');
      $this->session->set_flashdata('resultadoTipo', ALERT_WARNING);
      redirect('usuarios/login');
    }
    elseif (!$this->ion_auth->in_group(array('admin','decanos','docentes'))){
      $this->session->set_flashdata('resultadoOperacion', 'No tiene permisos para realizar esta operación.');
      $this->session->set_flashdata('resultadoTipo', ALERT_WARNING);
      redirect('materias/listar');
    }
    //cargo modelos, librerias, etc.
    $this->load->library('pagination');
    $this->load->model('Usuario');
    $this->load->model('Materia');
    $this->load->model('Gestor_materias','gm');
    
    //chequeo parámetros de entrada
    $pagInicio = (int)$pagInicio;
    $idMateria = (int)$idMateria;

    $materia = $this->gm->dame($idMateria);
    if (!$materia){
      $this->session->set_flashdata('resultadoOperacion', "No existe la materia seleccionada.");
      $this->session->set_flashdata('resultadoTipo', ALERT_ERROR);
      redirect('materias/listar');
    }
    //obtengo lista de datos de docentes
    $docentes = $materia->listarDocentes($pagInicio, PER_PAGE);
    $lista = array();
    foreach ($docentes as $i => $docente){
      $lista[$i] = array(
        'docente' => $docente,
        'datos' => $docente->dameDatosDocente($idMateria)
      );      
    }
    //genero la lista de links de paginación
    $this->pagination->initialize(array(
      'base_url' => site_url("materias/ver/$idMateria"),
      'total_rows' => $materia->cantidadDocentes(),
      'uri_segment' => 4
    ));
    $this->data['lista'] = &$lista;
    $this->data['materia'] = &$materia;
    $this->data['paginacion'] = $this->pagination->create_links(); //html de la barra de paginación
    $this->load->view('ver_materia', $this->data);
  }

  /*
   * Recepción del formulario para agregar  materia
   * POST: nombre, codigo, publicarInforme, publicarDevoluciones, publicarHistorico
   */
  public function nueva(){
    //verifico si el usuario tiene permisos para continuar
    if (!$this->ion_auth->logged_in()){
      $this->session->set_flashdata('resultadoOperacion', 'Debe iniciar sesión para realizar esta operación.');
      $this->session->set_flashdata('resultadoTipo', ALERT_WARNING);
      redirect('usuarios/login');
    }
    elseif (!$this->ion_auth->is_admin()){
      $this->session->set_flashdata('resultadoOperacion', 'No tiene permisos para realizar esta operación.');
      $this->session->set_flashdata('resultadoTipo', ALERT_WARNING);
      redirect('materias/listar');
    }
    
    //cargo modelos y librerias necesarias
    $this->load->model('Materia');
    $this->load->model('Gestor_materias','gm');
    
    //leo los datos POST
    $this->Materia->nombre = $this->input->post('nombre',TRUE);
    $this->Materia->codigo = $this->input->post('codigo', TRUE);
    $this->Materia->publicarInformes = ($this->input->post('publicarInformes')) ? RESPUESTA_SI : RESPUESTA_NO;
    $this->Materia->publicarHistoricos = ($this->input->post('publicarHistoricos')) ? RESPUESTA_SI : RESPUESTA_NO;
    $this->Materia->publicarDevoluciones = ($this->input->post('publicarDevoluciones')) ? RESPUESTA_SI : RESPUESTA_NO;
    
    //verifico datos POST
    $this->form_validation->set_rules('nombre','Nombre','alpha_dash_space|max_length[60]|required');
    $this->form_validation->set_rules('codigo','Código','alpha_numeric|required|max_length[5]');
    if($this->form_validation->run()){
      //agrego materia y cargo vista para mostrar resultado
      $res = $this->gm->alta( $this->Materia->nombre, 
                              $this->Materia->codigo, 
                              $this->Materia->publicarInformes, 
                              $this->Materia->publicarHistoricos, 
                              $this->Materia->publicarDevoluciones);
      //si la operación se realizó con éxito
      if (is_numeric($res)){
        $this->session->set_flashdata('resultadoOperacion', 'La operación se realizó con éxito.');
        $this->session->set_flashdata('resultadoTipo', ALERT_SUCCESS);
        redirect('materias/listar');
      }
      $this->data['resultadoOperacion'] = $res;
      $this->data['resultadoTipo'] = ALERT_ERROR;
    }
    //en caso de que los datos sean incorrectos, vuelvo a la pagina de edicion
    $this->data['materia'] = &$this->Materia;
    $this->data['tituloFormulario'] = 'Nueva Materia';
    $this->data['urlFormulario'] = site_url('materias/nueva');
    $this->load->view('editar_materia', $this->data);
  }

  /*
   * Recepción del formulario para eliminar una materia
   * POST: idMateria
   */
  public function eliminar(){
    //verifico si el usuario tiene permisos para continuar
    if (!$this->ion_auth->logged_in()){
      $this->session->set_flashdata('resultadoOperacion', 'Debe iniciar sesión para realizar esta operación.');
      $this->session->set_flashdata('resultadoTipo', ALERT_WARNING);
      redirect('usuarios/login');
    }
    elseif (!$this->ion_auth->is_admin()){
      $this->session->set_flashdata('resultadoOperacion', 'No tiene permisos para realizar esta operación.');
      $this->session->set_flashdata('resultadoTipo', ALERT_WARNING);
      redirect('materias/listar');
    }
    //verifico datos POST
    $this->form_validation->set_rules('idMateria','Materia','is_natural_no_zero|required');
    if($this->form_validation->run()){
      $this->load->model('Gestor_materias','gm');
      //doy de baja y cargo vista para mostrar resultado
      $res = $this->gm->baja((int)$this->input->post('idMateria'));
      if ($res == PROCEDURE_SUCCESS){
        $this->session->set_flashdata('resultadoOperacion', 'La materia se eliminó con éxito.');
        $this->session->set_flashdata('resultadoTipo', ALERT_SUCCESS);
      }
      else{
        $this->session->set_flashdata('resultadoOperacion', $res);
        $this->session->set_flashdata('resultadoTipo', ALERT_ERROR);
      }
    }
    redirect('materias/listar');
  }

  /*
   * Recepción del formulario para modificar los datos de una materia
   * POST: idMateria, nombre, codigo, publicarInforme, publicarDevoluciones, publicarHistorico
   */
  public function modificar($idMateria=null){
    //verifico si el usuario tiene permisos para continuar
    if (!$this->ion_auth->logged_in()){
      $this->session->set_flashdata('resultadoOperacion', 'Debe iniciar sesión para realizar esta operación.');
      $this->session->set_flashdata('resultadoTipo', ALERT_WARNING);
      redirect('usuarios/login');
    }
    elseif (!$this->ion_auth->is_admin()){
      $this->session->set_flashdata('resultadoOperacion', 'No tiene permisos para realizar esta operación.');
      $this->session->set_flashdata('resultadoTipo', ALERT_WARNING);
      redirect('materias/listar');
    }
    //cargo modelos, librerias, etc.
    $this->load->model('Materia');    
    $this->load->model('Gestor_materias','gm');
    
    //leo los datos POST
    $this->Materia->idMateria = (int)$this->input->post('idMateria');
    $this->Materia->nombre = $this->input->post('nombre',TRUE);
    $this->Materia->codigo = $this->input->post('codigo', TRUE);
    $this->Materia->publicarInformes = ($this->input->post('publicarInformes')) ? RESPUESTA_SI : RESPUESTA_NO;
    $this->Materia->publicarHistoricos = ($this->input->post('publicarHistoricos')) ? RESPUESTA_SI : RESPUESTA_NO;
    $this->Materia->publicarDevoluciones = ($this->input->post('publicarDevoluciones')) ? RESPUESTA_SI : RESPUESTA_NO;
    
    //verifico datos POST
    $this->form_validation->set_rules('idMateria', 'Materia','is_natural_no_zero|required');
    $this->form_validation->set_rules('nombre','Nombre','alpha_dash_space|max_length[60]|required');
    $this->form_validation->set_rules('codigo','Código','alpha_numeric|required|max_length[5]');        
    if($this->form_validation->run()){
      //modifico Materia y cargo vista para mostrar resultado
      $res = $this->gm->modificar($this->Materia->idMateria, 
                                  $this->Materia->nombre, 
                                  $this->Materia->codigo, 
                                  $this->Materia->publicarInformes, 
                                  $this->Materia->publicarHistoricos, 
                                  $this->Materia->publicarDevoluciones);
      if ($res == PROCEDURE_SUCCESS){
        $this->session->set_flashdata('resultadoOperacion', 'La modificación de la materia se realizó con éxito.');
        $this->session->set_flashdata('resultadoTipo', ALERT_SUCCESS);
        redirect('materias/listar');
      }
      $this->data['resultadoOperacion'] = $res;
      $this->data['resultadoTipo'] = ALERT_ERROR;
    }
    //en caso de que los datos sean incorrectos, vuelvo a la pagina de edicion
    if ($idMateria == null) redirect('materias/nueva');
    $this->Materia = $this->gm->dame((int)$idMateria);
    if (!$this->Materia){
        $this->session->set_flashdata('resultadoOperacion', "No existe la materia seleccionada.");
        $this->session->set_flashdata('resultadoTipo', ALERT_ERROR);
        redirect('materias/listar');
    }
    $this->data['materia'] = &$this->Materia;
    $this->data['tituloFormulario'] = 'Modificar Materia';
    $this->data['urlFormulario'] = site_url('materias/modificar/'.$idMateria);
    $this->load->view('editar_materia', $this->data);
  }

  /*
   * Recepción del formulario para crear una asociacion entre un docente y una materia
   * POST: idDocente, idMateria, tipoAcceso, ordenFormulario, cargo
   */
  public function asociarDocente(){
    //verifico si el usuario tiene permisos para continuar
    if (!$this->ion_auth->logged_in()){
      $this->session->set_flashdata('resultadoOperacion', 'Debe iniciar sesión para realizar esta operación.');
      $this->session->set_flashdata('resultadoTipo', ALERT_WARNING);
      redirect('usuarios/login');
    }
    //verifico datos POST
    $this->form_validation->set_rules('idDocente','Docente','is_natural_no_zero|required');
    $this->form_validation->set_rules('idMateria','Materia','is_natural_no_zero|required');
    $this->form_validation->set_rules('tipoAcceso','Tipo de acceso','required|alpha|exact_length[1]');
    $this->form_validation->set_rules('ordenFormulario','Orden en formulario','is_natural_no_zero|required');
    $this->form_validation->set_rules('cargo','Cargo','alpha_dash_space|max_length[40]');
    $idMateria = (int)$this->input->post('idMateria');
    if($this->form_validation->run()){
      $this->load->model('Materia');
      $this->load->model('Carrera');
      $this->load->model('Gestor_materias','gm');
      $materia = $this->gm->dame($idMateria);
      if (!$materia){
        $this->session->set_flashdata('resultadoOperacion', 'Los datos ingresados son incorrectos.');
        $this->session->set_flashdata('resultadoTipo', ALERT_ERROR);
        redirect('materias/ver/'.$idMateria);
      }
      
      $pass = false;
      $carreras = $materia->listarCarreras(); //listar las carreras a la que pertenece la materia
      //verifico si el usuario es un director de alguna carrera a la que pertenece la materia o un jefe de depto
      foreach ($carreras as $carrera) {
        if($carrera->idOrganizador == $this->data['usuarioLogin']->id) {$pass=true; break;}
      }
        
      if (!($this->ion_auth->is_admin() || $pass )){
        $this->session->set_flashdata('resultadoOperacion', 'No tiene permisos para realizar esta operación.');
        $this->session->set_flashdata('resultadoTipo', ALERT_WARNING);
        redirect('materias/ver/'.$idMateria);
      }
      
      //creo la asociacion y cargo vista para mostrar resultado
      $res = $materia->asociarDocente((int)$this->input->post('idDocente'),
                                            $this->input->post('tipoAcceso'), 
                                            (int)$this->input->post('ordenFormulario'),
                                            $this->input->post('cargo', TRUE));
      if ($res == PROCEDURE_SUCCESS){
        $this->session->set_flashdata('resultadoOperacion', 'La operación se realizó con éxito.');
        $this->session->set_flashdata('resultadoTipo', ALERT_SUCCESS);
      }
      else{
        $this->session->set_flashdata('resultadoOperacion', $res);
        $this->session->set_flashdata('resultadoTipo', ALERT_ERROR);
      }
    }
    redirect('materias/ver/'.$idMateria);
  }

  /*
   * Recepción del formulario para eliminar una asociacion entre un docente y una materia
   * POST: IdDocente, IdMateria
   */
  public function desasociarDocente(){
    //verifico si el usuario tiene permisos para continuar
    if (!$this->ion_auth->logged_in()){
      $this->session->set_flashdata('resultadoOperacion', 'Debe iniciar sesión para realizar esta operación.');
      $this->session->set_flashdata('resultadoTipo', ALERT_WARNING);
      redirect('usuarios/login');
    }
    //verifico datos POST
    $this->form_validation->set_rules('idDocente','Materia','is_natural_no_zero|required');
    $this->form_validation->set_rules('idMateria','Carrera','is_natural_no_zero|required');
    $idMateria = (int)$this->input->post('idMateria');
    if($this->form_validation->run()){
      $this->load->model('Materia');
      $this->load->model('Carrera');
      $this->load->model('Gestor_materias','gm');
      $materia = $this->gm->dame($idMateria);
      if (!$materia){
        $this->session->set_flashdata('resultadoOperacion', 'Los datos ingresados son incorrectos.');
        $this->session->set_flashdata('resultadoTipo', ALERT_ERROR);
        redirect('materias/ver/'.$idMateria);
      }
      
      $pass = false;
      $carreras = $materia->listarCarreras(); //listar las carreras a la que pertenece la materia
      //verifico si el usuario es un director de alguna carrera a la que pertenece la materia o un jefe de depto
      foreach ($carreras as $carrera) {
        if($carrera->idOrganizador == $this->data['usuarioLogin']->id) {$pass=true; break;}
      }
      if (!($this->ion_auth->is_admin() || $pass )){
        $this->session->set_flashdata('resultadoOperacion', 'No tiene permisos para realizar esta operación.');
        $this->session->set_flashdata('resultadoTipo', ALERT_WARNING);
        redirect('materias/ver/'.$idMateria);
      }
      
      //creo la asociacion y cargo vista para mostrar resultado
      $res = $materia->desasociarDocente((int)$this->input->post('idDocente'));
      if ($res == PROCEDURE_SUCCESS){
        $this->session->set_flashdata('resultadoOperacion', 'La operación se realizó con éxito.');
        $this->session->set_flashdata('resultadoTipo', ALERT_SUCCESS);
      }
      else{
        $this->session->set_flashdata('resultadoOperacion', $res);
        $this->session->set_flashdata('resultadoTipo', ALERT_ERROR);
      }
    }
    redirect('materias/ver/'.$idMateria);
  }

  /*
   * Funcion para responder solicitudes AJAX. Obtiene cuantas claves de acceso se crearon en el cuatrimestre anterior
   * POST: idMateria, idCarrera
   */
  public function cantidadClavesAJAX(){
    $this->form_validation->set_rules('idMateria','Materia','is_natural_no_zero|required');
    $this->form_validation->set_rules('idCarrera','Carrera','is_natural_no_zero|required');
    if($this->form_validation->run()){
      $this->load->model('Materia');
      $this->load->model('Gestor_materias','gm');
      $idMateria = (int)$this->input->post('idMateria');
      $idCarrera = (int)$this->input->post('idCarrera');
      $materia = $this->gm->dame($idMateria);
      if ($materia){
        echo $materia->dameCantidadClaves($idCarrera);
      }
    }
    return;
  }

  /*
   * Funcion para responder solicitudes AJAX
   * POST: buscar
   */
  public function buscarAJAX(){
    $this->form_validation->set_rules('buscar','Buscar','required');
    if($this->form_validation->run()){
      $buscar = $this->input->post('buscar');
      $this->load->model('Materia');
      $this->load->model('Gestor_materias','gm');
      $materias = $this->gm->buscar($buscar);
      echo "\n";
      foreach ($materias as $materia) {
        echo  "$materia->idMateria\t".
              "$materia->nombre\t".
              "$materia->codigo\t\n";
      }
    }
  }
}
?>