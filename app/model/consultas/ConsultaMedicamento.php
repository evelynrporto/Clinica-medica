<?php

class ConsultaMedicamento extends TRecord
{
    const TABLENAME = 'consulta_medicamento';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('consulta_id');
        parent::addAttribute('medicamento_id');
    }


}