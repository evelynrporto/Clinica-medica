<?php
class ConsultasReport extends TPage
{
    private $form;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->form = new BootstrapFormBuilder;
        $this->form->setFormTitle('Consultas');
        
        $medico_id = new TDBUniqueSearch('medico_id', 'clinica', 'Medico', 'id', 'nome');
        $estado_id = new TDBUniqueSearch('estado_id', 'clinica', 'Estado', 'id', 'nome');
        $inicio = new TDate('inicio');
        $fim = new TDate('fim');
        $output = new TRadioGroup('output');

        $medico_id->setSize(835);
        $estado_id->setSize(835);
        
        $this->form->addFields( [new TLabel('Médico')], [$medico_id] );
        $this->form->addFields( [new TLabel('Estado')], [$estado_id] );
        $this->form->addFields( [new TLabel('Data Inicio')], [$inicio], [new TLabel('Data Fim')], [$fim]);
        $this->form->addFields( [new TLabel('Formato')], [$output] );

        $inicio->addValidation('Inicio', new TRequiredValidator);
        $fim->addValidation('Fim', new TRequiredValidator);
        
        $output->setUseButton();
        $medico_id->setMinLength(0);
        $estado_id->setMinLength(0);
        
        $output->addItems( ['html' => 'HTML', 'pdf' => 'PDF', 'rtf' => 'RTF', 'xls' => 'XLS'] );
        $output->setValue( 'pdf' );
        $output->setLayout('horizontal');
        
        $this->form->addAction('Gerar', new TAction([$this, 'onGenerate']), 'fa:download blue');
        

        parent::add( $this->form );
    }
    
    public function onGenerate($param)
    {
        try
        {
            TTransaction::open('clinica');

            $this->form->validate(); // validate form data
            
            $data = $this->form->getData();
            
            $repository = new TRepository('Consulta');
            
            $criteria = new TCriteria;
            
            if ($data->medico_id)
            {
                $criteria->add( new TFilter('medico_id', '=', "{$data->medico_id}") );
            }
            
            if ($data->estado_id)
            {
                $criteria->add( new TFilter('medico_id', 'in', "(SELECT medico.id FROM medico, cidade WHERE cidade_id = cidade.id AND estado_id = {$data->estado_id})"));
            }

            if ($data->inicio)
            {
                $criteria->add( new TFilter('inicio', '>=', "{$data->inicio}"), TExpression::AND_OPERATOR);
                $criteria->add( new TFilter('fim', '<=', "{$data->fim}"));
            }

            $consultas = $repository->load($criteria);
            
            if ($consultas)
            {
                $widths = [40, 200, 80, 120, 80];
                
                switch ($data->output)
                {
                    case 'html':
                        $table = new TTableWriterHTML($widths);
                        break;
                    case 'pdf':
                        $table = new TTableWriterPDF($widths);
                        break;
                    case 'rtf':
                        $table = new TTableWriterRTF($widths);
                        break;
                    case 'xls':
                        $table = new TTableWriterXLS($widths);
                        break;
                }
                // id, nome, categoria, email, nascimento
            
                if (!empty($table))
                {
                    $table->addStyle('header', 'Helvetica', '16', 'B', '#ffffff', '#4B5D8E');
                    $table->addStyle('title',  'Helvetica', '10', 'B', '#ffffff', '#617FC3');
                    $table->addStyle('datap',  'Helvetica', '10', '',  '#000000', '#E3E3E3', 'LR');
                    $table->addStyle('datai',  'Helvetica', '10', '',  '#000000', '#ffffff', 'LR');
                    $table->addStyle('footer', 'Helvetica', '10', '',  '#2B2B2B', '#B4CAFF');
                }
                
                $table->setHeaderCallback( function($table) {
                    $table->addRow();
                    $table->addCell('Registros', 'center', 'header', 5);
                    
                    $table->addRow();
                    $table->addCell('Código', 'center', 'title');
                    $table->addCell('Descrição', 'left', 'title');
                    $table->addCell('Médico', 'center', 'title');
                    $table->addCell('Estado', 'center', 'title');   
                    $table->addCell('Data', 'left', 'title');            
                });
                
                $table->setFooterCallback( function ($table) {
                    $table->addRow();
                    $table->addCell(date('Y-m-d H:i:s'), 'center', 'footer', 5);
                });
                
                $colore = false;
                foreach ($consultas as $consulta)
                {
                    $style = $colore ? 'datap' : 'datai';
                    
                    $table->addRow();
                    $table->addCell( $consulta->id, 'center', $style);
                    $table->addCell( $consulta->titulo, 'left', $style);
                    $table->addCell( $consulta->medico->nome, 'left', $style);
                    $table->addCell( $consulta->medico->cidade->estado->nome, 'center', $style);
                    $table->addCell( date('d/m/Y', strtotime($consulta->inicio)), 'left', $style);
                    
                    $colore = !$colore;
                }
                
                $output = 'app/output/tabular.'.$data->output;
                
                if (!file_exists($output) OR is_writable($output))
                {
                    $table->save($output);
                    parent::openFile($output);
                    
                    new TMessage('info', 'Relatório gerado com sucesso');
                }
                else
                {
                    throw new Exception('Permissão negada: ' . $output);
                }
            }
            
            $this->form->setData($data);
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}