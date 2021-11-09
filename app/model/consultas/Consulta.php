<?php

class Consulta extends TRecord
{
    const TABLENAME = 'consulta';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('inicio');
        parent::addAttribute('fim');
        parent::addAttribute('titulo');
        parent::addAttribute('descricao');
        parent::addAttribute('cor');
        parent::addAttribute('status');
        parent::addAttribute('paciente_id');
        parent::addAttribute('medico_id');
        parent::addAttribute('system_user_id');
        parent::addAttribute('diagnostico');
    }

    public function get_paciente()
    {
        return Paciente::find($this->paciente_id);
    }

    public function get_medico()
    {
        return Medico::find($this->medico_id);
    }
}