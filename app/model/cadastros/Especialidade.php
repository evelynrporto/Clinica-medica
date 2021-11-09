<?php
/**
 * Estado Active Record
 * @author  <your-name-here>
 */
class Especialidade extends TRecord
{
    const TABLENAME = 'especialidade';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
    }


}