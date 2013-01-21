<?php

/**
 * 
 */
class Carreras extends CI_Controller{
  
  function __construct() {
    parent::__construct();
  }
  
  private function _datosDepartamentos(){
    $this->load->model('Departamento');
    $this->load->model('Gestor_departamentos','gd');
    $departamentos = $this->gd->listar(0, 255);
    $datos_departamentos = array();
    foreach ($departamentos as $i => $departamento) {
      $datos_departamentos[$i] = array(
        'IdDepartamento' => $departamento->IdDepartamento,
        'Nombre' => $departamento->Nombre);
    }
    return $datos_departamentos;
  }
  
  
  public function index(){
    $this->listar();
  }
  
  public function listar($idDepartamento=0, $pagInicio=0){
    if (!is_numeric($idDepartamento)){
      show_error('El Identificador de Departamento no es válido.');
      return;
    }
    if (!is_numeric($pagInicio)){
      show_error('El número de página es inválido.');
      return;
    }
    
    //VERIFICAR QUE EL USUARIO TIENE PERMISOS PARA CONTINUAR!!!!
    
    //cargo modelos, librerias, etc.
    $this->load->library('pagination');
    $this->load->model('Carrera');
    $this->load->model('Departamento');
    $this->load->model('Gestor_carreras','gc');
    $this->load->model('Gestor_departamentos','gd');
    if ($idDepartamento == 0){
      $cantidadCarreras = $this->gc->cantidad();
      $carreras = $this->gc->listar($pagInicio, 5);
    }
    else{
      $departamento = $this->gd->dame($idDepartamento);
      if ($departamento != FALSE){
        $cantidadCarreras = $departamento->cantidadCarreras();
        $carreras = $departamento->listarCarreras($pagInicio, 5);
        $data['departamento'] = array('Nombre' => $departamento->Nombre);
      }
      else{
        show_error('El Identificador de Departamento no es válido.');
        return;
      }
    }
    //genero la lista de links de paginación
    $config['base_url'] = site_url("carreras/listar/$idDepartamento");
    $config['total_rows'] = $cantidadCarreras;
    $config['per_page'] = 5;
    $config['uri_segment'] = 4;
    $this->pagination->initialize($config);
    
    //obtengo lista de carreras
    $tabla = array();
    foreach ($carreras as $i => $carrera) {
      $departamento = $this->gd->dame($carrera->IdDepartamento);
      $tabla[$i] = array(
        'IdCarrera' => $carrera->IdCarrera,
        'Nombre' => $carrera->Nombre,
        'Plan' => $carrera->Plan,
        'Departamento' => ($departamento!=FALSE)?$departamento->Nombre:''  
      );
    }

    //envio datos a la vista
    $data['tabla'] = $tabla; //array de datos de las Carreras
    $data['paginacion'] = $this->pagination->create_links(); //html de la barra de paginación
    $data['usuarioLogin'] = unserialize($this->session->userdata('usuarioLogin')); //objeto Persona (usuario logueado)
    $this->load->view('lista_carreras', $data);
  }


  public function nueva(){
    
    //VERIFICAR QUE EL USUARIO TIENE PERMISOS PARA CONTINUAR!!!!

    //si no recibimos ningún valor proveniente del formulario
    if(!$this->input->post('submit')){
      $data['usuarioLogin'] = unserialize($this->session->userdata('usuarioLogin')); //datos de session
      $data['departamentos'] = $this->_datosDepartamentos();
      $data['carrera'] = array(
        'IdCarrera' => 0,
        'IdDepartamento' => 0,
        'Nombre' => '',
        'Plan' => date('Y'));
      $data['link'] = site_url("carreras/nueva"); //hacia donde mandar los datos      
      $this->load->view('editar_carrera',$data); 
    }
    else{
      //verifico si los datos son correctos
      $this->form_validation->set_rules('IdDepartamento','ID Departamento','is_natural_no_zero');
      $this->form_validation->set_rules('Nombre','Nombre','required');
      $this->form_validation->set_rules('Plan','Plan','required|is_natural_no_zero|less_than[2100]|greater_than[1900]');
      $this->form_validation->set_error_delimiters('<small class="error">', '</small>'); //doy formato al mensaje de error      
      if($this->form_validation->run()==FALSE){
        //en caso de que los datos sean incorrectos, cargo el formulario nuevamente
        $data['usuarioLogin'] = unserialize($this->session->userdata('usuarioLogin')); //datos de session
        $data['departamentos'] = $this->_datosDepartamentos();
        $data['carrera'] = array(
          'IdCarrera' => 0,
          'IdDepartamento' => 0,
          'Plan' => $this->input->post('Plan'),
          'Nombre' => $this->input->post('Nombre'));
        $data['link'] = site_url("carreras/nueva"); //hacia donde mandar los datos
        $this->load->view('editar_carrera',$data);
      }
      else{
        //agrego carrera y cargo vista para mostrar resultado
        $this->load->model('Gestor_carreras','gc');
        $res = $this->gc->alta($this->input->post('IdDepartamento',TRUE), $this->input->post('Nombre',TRUE),$this->input->post('Plan',TRUE));
        $data['usuarioLogin'] = unserialize($this->session->userdata('usuarioLogin')); //datos de session
        $data['mensaje'] = (is_numeric($res))?"La operación se realizó con éxito. El ID de la nueva carrera es $res.":$res;
        $data['link'] = site_url("carreras"); //hacia donde redirigirse
        $this->load->view('resultado_operacion', $data);
      }
    }
  }


  public function eliminar($IdCarrera=0){ //PASAR DATOS POR POST!!!!
    if (!is_numeric($IdCarrera)){
      show_error('El ID Carrera es inválido.');
      return;
    }

    //VERIFICAR QUE EL USUARIO TIENE PERMISOS PARA CONTINUAR!!!!

    //doy de baja y cargo vista para mostrar resultado
    $this->load->model('Gestor_carreras','gc');
    $res = $this->gc->baja($IdCarrera);
    $data['usuarioLogin'] = unserialize($this->session->userdata('usuarioLogin')); //datos de session
    $data['mensaje'] = (strcmp($res, 'ok')==0)?'La operación se realizó con éxito.':$res;
    $data['link'] = site_url("carreras"); //link para boton aceptar/continuar
    $this->load->view('resultado_operacion', $data);
  }


  public function modificar($IdCarrera=0){ //PASAR DATOS POR POST!!!!
    if (!is_numeric($IdCarrera)){
      show_error('El ID Carrera es inválido.');
      return;
    }
    
    //VERIFICAR QUE EL USUARIO TIENE PERMISOS PARA CONTINUAR!!!!
    
    //cargo modelos, librerias, etc.
    $this->load->model('Carrera');
    $this->load->model('Gestor_carreras','gc');
    
    //si no recibimos ningún valor proveniente del formulario
    if(!$this->input->post('submit')){
      //si el departamento no existe mostrar mensaje
      $carrera = $this->gc->dame($IdCarrera);
      if ($carrera != FALSE){
        $data['usuarioLogin'] = unserialize($this->session->userdata('usuarioLogin')); //datos de session
        $data['departamentos'] = $this->_datosDepartamentos();
        $data['carrera'] = array(
          'IdCarrera' => $carrera->IdCarrera,
          'IdDepartamento' => $carrera->IdDepartamento,
          'Nombre' => $carrera->Nombre,
          'Plan' => $carrera->Plan);
        $data['link'] = site_url("carreras/modificar"); //hacia donde mandar los datos      
        $this->load->view('editar_carrera',$data); 
      }
      else{
        show_error('El ID Carrera es inválido.');
      }
    }
    else{
      //verifico si los datos son correctos
      $this->form_validation->set_rules('IdDepartamento','ID Departamento','is_natural_no_zero');
      $this->form_validation->set_rules('Nombre','Nombre','required');
      $this->form_validation->set_rules('Plan','Plan','required|is_natural_no_zero|less_than[2100]|greater_than[1900]');
      $this->form_validation->set_error_delimiters('<small class="error">', '</small>'); //doy formato al mensaje de error      
      if($this->form_validation->run()==FALSE){
        //en caso de que los datos sean incorrectos, cargo el formulario nuevamente
        $data['usuarioLogin'] = unserialize($this->session->userdata('usuarioLogin')); //datos de session
        $data['departamentos'] = $this->_datosDepartamentos();
        $data['carrera'] = array(
          'IdCarrera' => $this->input->post('IdCarrera'),
          'IdDepartamento' => $this->input->post('IdDepartamento'),
          'Plan' => $this->input->post('Plan'),
          'Nombre' => $this->input->post('Nombre'));
        $data['link'] = site_url("carreras/modificar"); //hacia donde mandar los datos
        $this->load->view('editar_carrera',$data);
      }
      else{
        //agrego carrera y cargo vista para mostrar resultado
        $res = $this->gc->modificar($this->input->post('IdCarrera',TRUE),$this->input->post('IdDepartamento',TRUE), $this->input->post('Nombre',TRUE),$this->input->post('Plan',TRUE));
        $data['usuarioLogin'] = unserialize($this->session->userdata('usuarioLogin')); //datos de session
        $data['mensaje'] = (strcmp($res, 'ok')==0)?'La operación se realizó con éxito.':$res;
        $data['link'] = site_url("carreras"); //hacia donde redirigirse
        $this->load->view('resultado_operacion', $data);
      }
    }
  }


  //funcion para responder solicitudes AJAX
  public function buscar(){
    $buscar = $this->input->post('Buscar');
    $this->load->model('Carrera');
    $this->load->model('Gestor_carreras','gc');
    $carreras = $this->gc->buscar($buscar);
    foreach ($carreras as $carrera) {
      echo  "$carrera->IdCarrera\t".
            "$carrera->Nombre\t".
            "$carrera->Plan\t".
            "\n";
    }
  }
  
  
  public function tmp(){
    $data['usuarioLogin'] = unserialize($this->session->userdata('usuarioLogin')); //datos de session
    $this->load->view('tmp',$data);
  }
  
}

?>