<?php

class AtendimentosDashboard extends TPage
{
    private $html; 

    function __construct()
    {
        parent::__construct();
      
        $this->html = new THtmlRenderer('app/resources/google_column_chart.html');
        $data[] = ['Médico', 'Consultas'];
        TTransaction::open('clinica');
        $conn = TTransaction::get();
        
        $query = 'select count(m.nome), m.nome 
                  from consulta c, medico m
                  where c.medico_id = m.id 
                  group by m.nome';

        $results = $conn->query($query);
        TTransaction::close();

        foreach ($results as $result)
        {
            $data[]=[$result['nome'],$result['count']];
        }

        $this->html->enableSection('main', ['data'   => json_encode($data),
                                            'width'  => '100%',
                                            'height' => '300px',
                                            'title'  => 'Consultas por médico',
                                            'xtitle' => 'Consultas',
                                            'ytitle' => 'Médico',
                                            'uniqid' => uniqid()]);

        parent::add($this->html);
    }
}