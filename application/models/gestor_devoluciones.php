<?php


/**
 * 
 */
class Gestor_devoluciones extends CI_Model{

	function __construct() {
		parent::__construct();
	}
  
  /**
   * Obtener el listado de devoluciones de una carrera. Devuleve un array de objetos.
   *
   * @access public
   * @param item inicial del listado a mostrar
   * @param cantidad de items a mostrar (tamaño de página)
   * @return array
   */
  public function listar($idCarrera, $pagInicio, $pagLongitud){
    $idCarrera = $this->db->escape($idCarrera);
    $pagInicio = $this->db->escape($pagInicio);
    $pagLongitud = $this->db->escape($pagLongitud);
    $query = $this->db->query("call esp_listar_devoluciones_carrera($idCarrera, $pagInicio, $pagLongitud)");
    $data = $query->result('Devolucion');
    $query->free_result();
    return $data;
  }
  
  
  /**
   * Obtener la cantidad de devoluciones de una Carrera
   *
   * @access public
   * @return int
   */  
  public function cantidad($idCarrera){
    $idMateria = $this->db->escape($idCarrera);
    $query = $this->db->query("call esp_cantidad_devoluciones_carrera($idCarrera)");
    $data=$query->row();
    $query->free_result();
    return ($data)?$data->cantidad:0;
  }


  /**
   * Da de Alta una devolucion. Devuleve el id en caso de éxito o un mensaje en caso de error.
   *
   * @access  public
   * @param identificador del formulario que se usará en la encuesta
   * @param año lectivo a la que se refiere la encuesta
   * @param cuatrimestre o periodo al que se refiere la encuesta
   * @return  string
   */
  public function alta($idMateria, $idEncuesta, $idFormulario, $fortalezas, $debilidades, $alumnos, $docentes, $mejoras){
    $idMateria = $this->db->escape($idMateria);
    $idEncuesta = $this->db->escape($idEncuesta);
    $idFormulario = $this->db->escape($idFormulario);
    $fortalezas = $this->db->escape($fortalezas);
    $debilidades = $this->db->escape($debilidades);
    $alumnos = $this->db->escape($alumnos);
    $docentes = $this->db->escape($docentes);
    $mejoras = $this->db->escape($mejoras);
    $query = $this->db->query("call esp_alta_devolucion($idMateria, $idEncuesta, $idFormulario, $fortalezas, $debilidades, $alumnos, $docentes, $mejoras)");
    $data = $query->row();
    $query->free_result();
    return ($data)?$data->mensaje:'No se pudo conectar con la base de datos.';
  }
  
    
  /**
   * Obtener una devolucion a partir de su id. Devuleve un objeto en caso de éxito, o FALSE en caso de error.
   *
   * @access public
   * @param identificador de devolucion
   * @param identificador de materia
   * @param identificador de encuesta
   * @param identificador de formulario
   * @return object
   */
  public function dame($idMateria, $idEncuesta, $idFormulario){
    $idMateria = $this->db->escape($idMateria);
    $idEncuesta = $this->db->escape($idEncuesta);
    $idFormulario = $this->db->escape($idFormulario);
    $query = $this->db->query("call esp_dame_devolucion($idMateria, $idEncuesta, $idFormulario)");
    $data = $query->result('Devolucion');
    $query->free_result();
    return ($data != FALSE)?$data[0]:FALSE;
  }
}
?>