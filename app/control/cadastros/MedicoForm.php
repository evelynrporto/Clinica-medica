<?php

class MedicoForm extends TPage
{
    private $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction( new TAction(['MedicoList', 'onReload'], ['register_state' => 'true']) );
        
        $this->setDatabase('clinica');              // defines the database
        $this->setActiveRecord('Medico');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Medico');
        $this->form->setFormTitle('Médico');
        
        $this->form->setColumnClasses( 2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8'] );$this->form->setClientValidation(true);
        

        // create the form fields
        $id               = new TEntry('id');
        $nome             = new TEntry('nome');
        $endereco         = new TEntry('endereco');
        $contato          = new TEntry('contato');
        $formacao         = new TEntry('formacao');
        $hora_inicio      = new TTime('hora_inicio');
        $hora_fim         = new TTime('hora_fim');
        $dias_atendimento = new TDBMultiSearch('dias_atendimento', 'clinica', 'Dia', 'id', 'descricao');
        $ativo            = new TRadioGroup('ativo'); 
        $userid           = new TDBCombo('userid', 'permission', 'SystemUser', 'id', 'name', 'name');
        $especialidade_id = new TDBUniqueSearch('especialidade_id', 'clinica', 'Especialidade', 'id', 'nome');
        $cidade_id = new TDBUniqueSearch('cidade_id', 'clinica', 'Cidade', 'id', 'nome');

        $cidade_id->setMinLength(0);
        $especialidade_id->setMinLength(0);
        $dias_atendimento->setMinLength(0);
        $dias_atendimento->setSize('100%', 60);

        //$dias = ['1' => 'Segunda', '2' => 'Terça', '3' => 'Quarta', '4' => 'Quinta', '5' => 'Sexta', '6' => 'Sábado', '7' => 'Domingo'];
        //$dias_atendimento->addItems($dias);
        
        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Endereço') ], [ $endereco ] );
        $this->form->addFields( [ new TLabel('Cidade') ], [ $cidade_id ] );
        $this->form->addFields( [ new TLabel('Contato') ], [ $contato ] );
        $this->form->addFields( [ new TLabel('Formação') ], [ $formacao ] );
        $this->form->addFields( [ new TLabel('Hora Inicio') ], [ $hora_inicio ] );
        $this->form->addFields( [ new TLabel('Hora Fim') ], [ $hora_fim ] );
        $this->form->addFields( [ new TLabel('Dias de atendimento') ], [ $dias_atendimento ] );
        $this->form->addFields( [ new TLabel('Ativo') ], [ $ativo ] );
        $this->form->addFields( [ new TLabel('Especialidade') ], [ $especialidade_id ] );
        $this->form->addFields( [ new TLabel('Usuário') ], [ $userid ] );

        $nome->addValidation('Nome', new TRequiredValidator);
        $especialidade_id->addValidation('Especialidade Id', new TRequiredValidator);
        $ativo->addValidation('Ativo', new TRequiredValidator);
        $endereco->addValidation('Endereço', new TRequiredValidator);

        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $endereco->setSize('100%');
        $cidade_id->setSize('100%');
        $contato->setSize('100%');
        $formacao->setSize('100%');
        $hora_inicio->setSize('100%');
        $hora_fim->setSize('100%');
        $dias_atendimento->setSize('100%');
        $ativo->setSize('100%');
        $ativo->addItems( ['Y' => 'Sim', 'N' => 'Não'] );
        $ativo->setLayout('horizontal');
        $ativo->setValue('Y');
        $especialidade_id->setSize('100%');

        $id->setEditable(FALSE);
        
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        //$this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }

    public function onSave( $param )
    {
        try
        {
            TTransaction::open('clinica'); // open a transaction
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new Medico;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            MedicoDias::where('medico_id', '=', $object->id)->delete();
            
            if ($data->dias_atendimento)
            {
                foreach ($data->dias_atendimento as $dia_id)
                {
                    $pp = new MedicoDias;
                    $pp->medico_id = $object->id;
                    $pp->dia_id  = $dia_id;
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

    public function onEdit ($param)
    {
        try
        {
            if (isset($param['key']))
            {
                // get the parameter $key
                $key = $param['key'];
                
                // open a transaction with database 'clinica'
                TTransaction::open('clinica');
                
                $object = new Medico($key);
                $object->dias_atendimento = MedicoDias::where('medico_id', '=', $object->id)->getIndexedArray('dia_id');

                $this->form->setData($object);

                $data = new stdClass;
                $data->id             = $object->id;
                $data->nome           = $object->nome;
                $data->endereco       = $object->endereco;  
                $data->contato        = $object->contato; 
                $data->formacao       = $object->formacao; 
                $data->hora_inicio     = $object->hora_inicio;
                $data->hora_fim        = $object->hora_fim;
                $data->especialidade_id= $object->especialidade_id;
                $data->ativo           = $object->ativo;
                $data->cidade_id       = $object->cidade_id;
                $data->userid          = $object->userid;
                TForm::sendData('form_Medico', $data);
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
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}