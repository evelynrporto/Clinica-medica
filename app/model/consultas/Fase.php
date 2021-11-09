<?php

class Fase extends TRecord
{
    const TABLENAME = 'fase';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('ordem');
        parent::addAttribute('consulta_id');
    }


}