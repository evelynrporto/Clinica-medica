<?php
/**
 * Medico Active Record
 * @author  <your-name-here>
 */
class Medico extends TRecord
{
    const TABLENAME = 'medico';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('endereco');
        parent::addAttribute('contato');
        parent::addAttribute('formacao');
        parent::addAttribute('hora_inicio');
        parent::addAttribute('hora_fim');
        parent::addAttribute('ativo');
        parent::addAttribute('especialidade_id');
        parent::addAttribute('userid');
        parent::addAttribute('cidade_id');
    }
    
    public function get_especialidade()
    {
        return Especialidade::find($this->especialidade_id);
    }

    public function get_cidade()
    {
        return Cidade::find($this->cidade_id);
    }
}