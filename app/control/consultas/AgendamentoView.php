<?php

class AgendamentoView extends TPage
{
    private $fc;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $options = ['register_state' => 'false'];
        
        $this->fc = new TFullCalendar(date('Y-m-d'), 'month');
        $this->fc->setReloadAction(new TAction(array($this, 'getEvents')));
        $this->fc->setEventClickAction(new TAction(array('AgendamentoForm', 'onEdit'), $options));
        $this->fc->setEventUpdateAction(new TAction(array('AgendamentoForm', 'onUpdateEvent'), $options));
        if (!TSession::getValue('medico_id')){
        $this->fc->setDayClickAction(new TAction(array('AgendamentoForm', 'onStartEdit'), $options));
        }
        $this->fc->setOption('businessHours', [ [ 'dow' => [ 1, 2, 3, 4, 5 ], 'start' => '08:00', 'end' => '18:00' ]]);
        parent::add( $this->fc );
    }
    
    /**
     * Output events as an json
     */
    public static function getEvents($param=NULL)
    {
        $return = array();
        try
        {
            TTransaction::open('clinica');
            if (TSession::getValue('medico_id')){
            $events = Consulta::where('inicio', '>=', $param['start'])
                               ->where('fim', '<=', $param['end'])
                               ->where('medico_id', '=', TSession::getValue('medico_id'))->load();
            }
            else {
                $events = Consulta::where('inicio', '>=', $param['start'])
                               ->where('fim', '<=', $param['end'])->load();
            }

            if ($events)
            {
                foreach ($events as $event)
                {
                    $event_array = $event->toArray();
                    $event_array['start'] = str_replace( ' ', 'T', $event_array['inicio']);
                    $event_array['end']   = str_replace( ' ', 'T', $event_array['fim']);
                    $event_array['color'] = $event_array['cor'];
                    
                    $popover_content = $event->render("<b>T??tulo</b>: {titulo} <br> <b>Descri????o</b>: {descricao}");
                    $event_array['title'] = TFullCalendar::renderPopover($event_array['titulo'], 'Consulta', $popover_content);
                    
                    $return[] = $event_array;
                }
            }
            TTransaction::close();
            echo json_encode($return);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Reconfigure the callendar
     */
    public function onReload($param = null)
    {
        if (isset($param['view']))
        {
            $this->fc->setCurrentView($param['view']);
        }
        
        if (isset($param['date']))
        {
            $this->fc->setCurrentDate($param['date']);
        }
    }
}