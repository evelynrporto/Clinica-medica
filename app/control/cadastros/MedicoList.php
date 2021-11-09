<?php

class MedicoList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('clinica');            // defines the database
        $this->setActiveRecord('Medico');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        $this->addFilterField('especialidade_id', '=', 'especialidade_id'); // filterField, operator, formField
        $this->addFilterField('ativo', 'like', 'ativo');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Medico');
        $this->form->setFormTitle('Médico');
        

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $especialidade_id = new TDBUniqueSearch('especialidade_id', 'clinica', 'Especialidade', 'id', 'nome');
        $ativo = new TRadioGroup('ativo');
        $especialidade_id->setMinLength(0);

        $ativo->addItems( ['Y' => 'Ativo', 'N' => 'Inativo', '' => 'Ambos'] );
        $ativo->setLayout('horizontal');

        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Especialidade') ], [ $especialidade_id ] );
        $this->form->addFields( [ new TLabel('Ativo') ], [ $ativo ] );


        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $ativo->setSize('100%');
        $especialidade_id->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['MedicoForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        //$this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'center', '10%');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_contato = new TDataGridColumn('contato', 'Contato', 'left');
        $column_especialidade_id = new TDataGridColumn('{especialidade->nome}', 'Especialidade', 'left');
        $column_ativo = new TDataGridColumn('ativo', 'Situação', 'left');

        $column_id->setTransformer( function ($value, $object, $row) {
            if ($object->ativo == 'N')
            {
                $row->style= 'color: silver';
            }
            
            return $value;
        });
        
        $column_ativo->setTransformer( function ($value) {
            if ($value == 'Y')
            {
                $div = new TElement('span');
                $div->class="label label-success";
                $div->style="text-shadow:none; font-size:12px";
                $div->add('Ativo');
                return $div;
            }
            else
            {
                $div = new TElement('span');
                $div->class="label label-danger";
                $div->style="text-shadow:none; font-size:12px";
                $div->add('Inativo');
                return $div;
            }
        });

        $column_especialidade_id->enableAutoHide(500);
        $column_ativo->enableAutoHide(500);
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_contato);
        $this->datagrid->addColumn($column_especialidade_id);
        $this->datagrid->addColumn($column_ativo);


        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_nome->setAction(new TAction([$this, 'onReload']), ['order' => 'nome']);
        $column_ativo->setAction(new TAction([$this, 'onReload']), ['order' => 'ativo']);

        $action1 = new TDataGridAction(['MedicoForm', 'onEdit'], ['id'=>'{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        $action3 = new TDataGridAction([$this, 'onTurnOnOff'], ['id'=>'{id}']);

        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        $this->datagrid->addAction($action3 ,_t('Activate/Deactivate'), 'fa:power-off orange');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        $panel = new TPanelGroup('', 'white');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        // header actions
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table blue' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf red' );
        $panel->addHeaderWidget( $dropdown );
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
    public function onTurnOnOff($param)
    {
        try
        {
            TTransaction::open('clinica');
            $medico = Medico::find($param['id']);
            
            if ($medico instanceof Medico)
            {
                $medico->ativo = $medico->ativo == 'Y' ? 'N' : 'Y';
                $medico->store();
            }
            
            TTransaction::close();
            
            $this->onReload($param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}