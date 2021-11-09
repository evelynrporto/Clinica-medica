<?php

class KanbanView extends TPage
{
	public function __construct($param)
	{
		parent::__construct();
		
		$kanban = new TKanban;
		$kanban->addStage(1, 'Aguardando');
		$kanban->addStage(2, 'Em atendimento');
		$kanban->addStage(3, 'Atendimento concluído');
		$kanban->addStage(4, 'Não compareceu');

		if (TSession::getValue('medico_id')){
		TTransaction::open('clinica');
			for ($n=0; $n<5; $n++) {
				$consultas = Consulta::where('status', '=', $n)
									 ->where('medico_id', '=', TSession::getValue('medico_id'))->load();
				if ($consultas) {
					foreach ($consultas as $consulta){
						$kanban->addItem($consulta->id, $n, $consulta->titulo, $consulta->descricao, $consulta->cor, $consulta);
					}
				}
			}
			$kanban->addItemAction( 'Editar', new TAction(['RegistroForm', 'onEdit']), 'far:edit blue');
		} else {
			TTransaction::open('clinica');
			for ($n=0; $n<5; $n++) {
				$consultas2 = Consulta::where('status', '=', $n)->load();
				if ($consultas2) {
					foreach ($consultas2 as $consulta1) {
						$kanban->addItem($consulta1->id, $n, $consulta1->titulo, $consulta1->descricao, $consulta1->cor, $consulta1);
					}
				}
			}
		}	
		TTransaction::close();			
		
		$kanban->addItemAction('Visualizar', new TAction([$this, 'onViewItem']), 'fa:eye blue');
		$kanban->setItemDropAction(new TAction([__CLASS__, 'onUpdateItemDrop']));
		parent::add($kanban);
		//$this->kanban->addStageAction('Exclui', new TAction([$this, 'onDeleteFase'], ['register_state' => 'false']),   'far:trash-alt red fa-fw');
    } 
	
    /**
     * Update item on drop
     */
	public static function onUpdateItemDrop($param)
	{
        try
        {
            if (empty($param['order']))
            {
                return;
			}  
                // open a transaction with database 'clinica'
                TTransaction::open('clinica');
                foreach ($param['order'] as $key => $id)
				{
					$sequence = ++ $key;

					$consulta = new Consulta($id);
					$consulta->item_order = $sequence;
					$consulta->status = $param['stage_id'];
					$consulta->store();
				}
				TTransaction::close();		

    		} catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
	}

	public function onLoad($param)
	{
	}

	public function onViewItem ($param){
		try
        {
            if (isset($param['key']))
            {
                // get the parameter $key
                $key = $param['key'];
                
                // open a transaction with database 'clinica'
                TTransaction::open('clinica');
                
                $object = new Consulta($key);

				new TMessage('info', 'Título: ' . $object->titulo . '<br>'
								   . 'Paciente: ' . $object->paciente->nome . '<br>'
								   . 'Médico: ' . $object->medico->nome);		
				
				TTransaction::close();
			}
		}catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
	}
}