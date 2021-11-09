<?php

class Atividade extends TRecord
{
    const TABLENAME = 'atividade';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('conteudo');
        parent::addAttribute('cor');
        parent::addAttribute('ordem');
        parent::addAttribute('fase_id');
    }


}