<?php

class RegistroForm extends TPage
{
    protected $form; // form
    
    // trait with onSave, onClear, onEdit
    use Adianti\Base\AdiantiStandardFormTrait;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    public function __construct()
    {
        parent::__construct();
        parent::setTargetContainer("adianti_right_panel");
        
        $this->setDatabase('clinica');    // defines the database
        $this->setActiveRecord('Consulta');   // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Registro');
        $this->form->setFormTitle('Registro');

        // create the form fields
        $id              = new THidden('id');
        $diagnostico     = new THtmlEditor('diagnostico');
        $medicamento    = new TDBMultiSearch('medicamento', 'clinica', 'Medicamento', 'id', 'nome');
        
        $id->setEditable(FALSE);
        $medicamento->setSize('100%', 60);
        $diagnostico->setSize('100%', 250);
        $diagnostico->setOption('placeholder', 'Digite o diagnóstico aqui...');
        $medicamento->setMinLength(0);
        
        if (TSession::getValue('medico_id')) {
        // add the form fields
        $this->form->addFields( [new THidden('Id:', null, null, 'b')]);
        $this->form->addFields( [$id] );
        $this->form->addFields( [$diagnostico] );
        $this->form->addFields( [new TLabel('Medicamento:', null, null, 'b')]);
        $this->form->addFields( [$medicamento] );
        
        // define the form action
        $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:save green');
        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        $this->setAfterSaveAction( new TAction( ['KanbanView', 'onLoad'] ) );
        $this->setUseMessages(FALSE);
        
        TScript::create('$("body").trigger("click")');
        TScript::create('$("[name=nome]").focus()');
        }
        parent::add($this->form);
    }

    public function onSave( $param )
    {
        try
        {
            TTransaction::open('clinica'); // open a transaction
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new Consulta;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            ConsultaMedicamento::where('consulta_id', '=', $object->id)->delete();
            
            if ($data->medicamento)
            {
                foreach ($data->medicamento as $medicamento_id)
                {
                    $pp = new ConsultaMedicamento;
                    $pp->consulta_id = $object->id;
                    $pp->medicamento_id  = $medicamento_id;
                    $pp->store();
                }
            }
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }

    public function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                // get the parameter $key
                $key = $param['key'];
                
                // open a transaction with database 'clinica'
                TTransaction::open('clinica');
                
                $object = new Consulta($key);
                $object->medicamento = ConsultaMedicamento::where('consulta_id', '=', $object->id)->getIndexedArray('medicamento_id');

                $this->form->setData($object);

                $data = new stdClass;
                //$data->id             = $object->id;
                $data->diagnostico    = $object->diagnostico;
                TForm::sendData('form_Registro', $data);
                // fill the form with the active record data
                //$this->form->setData($data);
                
                // close the transaction
                TTransaction::close();              
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
        public function onStartEdit($param)
    {
        $data = new stdClass;
        $data->fase_id = $param['id'];
        $data->ordem = 999;
        $this->form->setData($data);
    }
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}