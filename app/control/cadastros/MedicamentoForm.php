<?php

class MedicamentoForm extends TPage
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods

    function __construct()
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction( new TAction(['MedicamentoList', 'onReload'], ['register_state' => 'true']) );
        
        $this->setDatabase('clinica');              // defines the database
        $this->setActiveRecord('Medicamento');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Medicamento');
        $this->form->setFormTitle('Medicamento');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses( 2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8'] );
        

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $fabricante = new TEntry('fabricante');
        $composicao = new TEntry('composicao');
        $contraindicacoes = new TText('contraindicacoes');

        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Fabricante') ], [ $fabricante ] );
        $this->form->addFields( [ new TLabel('Composição') ], [ $composicao ] );
        $this->form->addFields( [ new TLabel('Contraindicações') ], [ $contraindicacoes ] );

        $nome->addValidation('Nome', new TRequiredValidator);

        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $fabricante->setSize('100%');
        $composicao->setSize('100%');
        $contraindicacoes->setSize('100%');

        $id->setEditable(FALSE);
        
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}