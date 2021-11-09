<?php

class PacienteView extends TPage
{

    public function __construct($param)
    {
        parent::__construct();
        
        // create the HTML Renderer
        $this->html = new THtmlRenderer('app/resources/paciente.html');
    }
    public function onReload($param){
        try
        {

            if (isset($param['key'])){

            $key = $param['key'];

            TTransaction::open('clinica');
            $paciente = new Paciente($key);
            $paciente_id = $paciente->id;
            
            // define replacements for the main section
            $replace = array();
            $replace['id']       = $paciente->id;
            $replace['nome']     = $paciente->nome;
            $replace['endereco'] = $paciente->endereco;
            $replace['email']    = $paciente->email;
            $replace['bairro']   = $paciente->bairro;
            $replace['telefone'] = $paciente->telefone;
            
            // replace the main section variables
            $this->html->enableSection('main', $replace);
            
            $cards = new TCardView;
            $cards->setUseButton();
            //$datagrid = new BootstrapDatagridWrapper(new TDataGrid);
            //$datagrid->width = '100%';

            $consultas = Consulta::where('paciente_id', '=', $paciente_id)
                                 ->where('status', '=', 3)->load();      

            foreach ($consultas as $consulta)
            {
                $objConsulta              = new StdClass;
                $objConsulta->titulo      = $consulta->titulo;
                $objConsulta->descricao   = $consulta->descricao;
                $objConsulta->diagnostico = $consulta->diagnostico;
                $objConsulta->inicio      = date('d/m/Y', strtotime($consulta->inicio));
                $objConsulta->medico      = $consulta->medico->nome;
                $objConsulta->cor         = $consulta->cor;
                
                $cards->addItem($objConsulta);
            }
               
        $replace['cards'] = $cards;
        $replace['class'] = get_class($cards);
        $cards->setTitleAttribute('titulo');
        $cards->setColorAttribute('cor');
        $cards->setItemTemplate('<b>Descrição</b>: {descricao} <br>
                                 <b>Diagnóstico</b>: {diagnostico} <br>
                                 <b>Médico</b>: {medico} <br>
                                 <b>Data</b>: {inicio}');

            /*
            $datagrid->addColumn(new TDataGridColumn('medico_id', 'Médico', 'center', '10%'));
            $datagrid->addColumn(new TDataGridColumn('titulo', 'Nome', 'left', '70%'));

            $datagrid->createModel();

            foreach ($consultas as $consulta){
                $objConsulta = new StdClass;
                $objConsulta->medico_id = $consulta->medico->nome;
                $objConsulta->titulo = $consulta->titulo;

                $datagrid->addItem($objConsulta);
            }
            
            TTransaction::close();

            $replace = array();
            $replace['datagrid'] = $datagrid;
            $replace['class'] = get_class($datagrid);
            */
            $this->html->enableSection('consultas', $replace);
            // wrap the page content using vertical box
            // vertical box container
            $container = new TVBox;
            $container->style = 'width: 100%';
            //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
            $container->add($this->html);
    
            parent::add($container);            
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}