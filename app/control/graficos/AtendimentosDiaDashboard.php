<?php

class AtendimentosDiaDashboard extends TPage
{
    private $html; 

    function __construct()
    {
        parent::__construct();
      
        $this->html = new THtmlRenderer('app/resources/google_column_chart.html');
        $data[] = ['Dias', 'Consultas'];
        TTransaction::open('clinica');
        $conn = TTransaction::get();

        $query = 'select count(c.inicio), CAST(c.inicio AS DATE) FROM 
                  consulta c
                  group by CAST (c.inicio AS DATE)';        
                          
        $results = $conn->query($query);

        TTransaction::close();

        foreach ($results as $result)
        {
            $data[]=[date('d/m/Y',strtotime($result['inicio'])),$result['count']];
        }

        $this->html->enableSection('main', ['data'   => json_encode($data),
                                    'width'  => '100%', 'height' => '300px',
                                    'title'  => 'Consultas por dia', 'xtitle' => 'Consultas',
                                    'ytitle' => 'Dias', 'uniqid' => uniqid()]);
        parent::add($this->html);
    }
}