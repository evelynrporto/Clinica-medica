<?php

class AtendimentosHorasDashboard extends TPage
{
    private $html; 

    function __construct()
    {
        parent::__construct();
      
        $this->html = new THtmlRenderer('app/resources/google_column_chart.html');
        $data[] = ['Horas', 'Consultas'];
        TTransaction::open('clinica');
        $conn = TTransaction::get();

        $query = 'select count(c.inicio), CAST(c.inicio as time) FROM 
                  consulta c
                  group by CAST(c.inicio as time)';        
                          
        $results = $conn->query($query);

        TTransaction::close();

        foreach ($results as $result)
        {
            $data[]=[date('h:i',strtotime($result['inicio'])),$result['count']];
        }

        $this->html->enableSection('main', ['data'   => json_encode($data),
                                    'width'  => '100%', 'height' => '300px',
                                    'title'  => 'Consultas por hora', 'xtitle' => 'Horas',
                                    'ytitle' => 'Consultas', 'uniqid' => uniqid()]);
        parent::add($this->html);
    }
}