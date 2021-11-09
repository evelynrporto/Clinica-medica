<?php

class PacienteForm extends TPage
{
    protected $form; // form
    private $timeline;
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction( new TAction(['PacienteList', 'onReload'], ['register_state' => 'true']) );
        
        $this->setDatabase('clinica');              // defines the database
        $this->setActiveRecord('Paciente');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Paciente');
        $this->form->setFormTitle('Informações do Paciente');

        $this->form->appendPage('Paciente');
        
        $this->form->setColumnClasses( 2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8'] );$this->form->setClientValidation(true);
        

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $endereco = new TEntry('endereco');
        $bairro = new TEntry('bairro');
        $telefone = new TEntry('telefone');
        $email = new TEntry('email');
        $descricao = new TEntry('descricao');
        $cidade_id = new TDBUniqueSearch('cidade_id', 'clinica', 'Cidade', 'id', 'nome');
        $cidade_id->setMinLength(0);

        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Endereço') ], [ $endereco ] );
        $this->form->addFields( [ new TLabel('Bairro') ], [ $bairro ] );
        $this->form->addFields( [ new TLabel('Telefone') ], [ $telefone ] );
        $this->form->addFields( [ new TLabel('Email') ], [ $email ] );
        $this->form->addFields( [ new TLabel('Descrição') ], [ $descricao ] );
        $this->form->addFields( [ new TLabel('Cidade') ], [ $cidade_id ] );

        $nome->addValidation('Nome', new TRequiredValidator);
        $endereco->addValidation('Endereço', new TRequiredValidator);
        $bairro->addValidation('Bairro', new TRequiredValidator);
        $telefone->addValidation('Telefone', new TRequiredValidator);
        $email->addValidation('Email', new TEmailValidator);
        $cidade_id->addValidation('Cidade Id', new TRequiredValidator);


        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $endereco->setSize('100%');
        $bairro->setSize('100%');
        $telefone->setSize('100%');
        $email->setSize('100%');
        $descricao->setSize('100%');
        $cidade_id->setSize('100%');

        $id->setEditable(FALSE);
        
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        //$this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');

        $this->form->appendPage('Histórico');

         TTransaction::open('clinica');
         
         if (isset($param['key'])) {
         $consultas = Consulta::where('paciente_id','=', $param['key']) //pegar id selecionado
                     ->where('status', '=', 3)->load();
         $this->timeline = new TTimeline;
         foreach ($consultas as $consulta)
         {
            $this->timeline->addItem($consulta->id, $consulta->titulo, $consulta->diagnostico, $consulta->inicio, 'fa:arrow-left bg-green', 'left');
         }
        
         $this->timeline->setFinalIcon('fa:flag-checkered bg-red');
         //$action = new TAction([$this, 'onAction'], ['id' => '{id}']);
         //$this->timeline->addAction($action, 'Visualizar', 'fa:eye blue');
         TTransaction::close();

         $this->timeline->setUseBothSides();
         $this->timeline->setTimeDisplayMask('dd/mm/yyyy');
         $this->form->addContent([$this->timeline]);
        }
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }

    public static function onAction ($param)
    {
	}
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}