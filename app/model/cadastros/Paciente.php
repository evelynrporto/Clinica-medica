<?php
/**
 * Medico Active Record
 * @author  <your-name-here>
 */
class Paciente extends TRecord
{
    const TABLENAME = 'paciente';
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
        parent::addAttribute('bairro');
        parent::addAttribute('telefone');
        parent::addAttribute('email');
        parent::addAttribute('descricao');
        parent::addAttribute('cidade_id');
    }
    
    public function get_cidade()
    {
        return Cidade::find($this->cidade_id);
    }

}