<?php
class RegistrosReport extends TPage
{
    private $form;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->form = new BootstrapFormBuilder;
        $this->form->setFormTitle('Registros');
        
        $paciente_id = new TDBUniqueSearch('paciente_id', 'clinica', 'Paciente', 'id', 'nome');
        $status = new THidden('status');
        $output = new TRadioGroup('output');
        
        $this->form->addFields( [new TLabel('Paciente')], [$paciente_id] );
        $this->form->addFields( [new THidden('Status')], [$status] );
        $this->form->addFields( [new TLabel('Formato')], [$output] );
        
        $output->setUseButton();
        $status->setValue(3);
        $status->setEditable(FALSE);
        $paciente_id->setSize('50%');
        $paciente_id->setMinLength(0);
        
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
            
            $data = $this->form->getData();
            
            $repository = new TRepository('Consulta');
            
            $criteria = new TCriteria;
            
            if ($data->paciente_id)
            {
                $criteria->add( new TFilter('paciente_id', '=', "{$data->paciente_id}") );
            }
            
            if ($data->status)
            {
                $criteria->add( new TFilter('status', '=', $data->status) );
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
                    $table->addCell('Paciente', 'left', 'title');
                    $table->addCell('Médico', 'center', 'title');
                    $table->addCell('Diagnóstico', 'center', 'title');
                    $table->addCell('Data', 'left', 'title');            
                });
                
                $table->setFooterCallback( function ($table) {
                    $table->addRow();
                    $table->addCell(date('d/m/Y H:i:s'), 'center', 'footer', 5);
                });
                
                $colore = false;
                foreach ($consultas as $consulta)
                {
                    $style = $colore ? 'datap' : 'datai';
                    
                    $table->addRow();
                    $table->addCell( $consulta->id, 'center', $style);
                    $table->addCell( $consulta->paciente->nome, 'left', $style);
                    $table->addCell( $consulta->medico->nome, 'left', $style);
                    $table->addCell( $consulta->diagnostico, 'left', $style);
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