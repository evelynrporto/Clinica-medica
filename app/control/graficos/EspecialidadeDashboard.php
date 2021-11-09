<?php

class EspecialidadeDashboard extends TPage
{
    private $html; 

    function __construct()
    {
        parent::__construct();
      
        $this->html = new THtmlRenderer('app/resources/google_pie_chart.html');
        $data[] = ['Especialidade', 'Consultas'];
        TTransaction::open('clinica');
        $conn = TTransaction::get();

        $query = 'select count(e.nome), e.nome FROM 
                  consulta c, especialidade e, medico m 
                  WHERE c.medico_id = m.id AND m.especialidade_id = e.id 
                  group by e.nome';        
                          
        $results = $conn->query($query);

        TTransaction::close();

        foreach ($results as $result)
        {
            $data[]=[$result['nome'],$result['count']];
        }

        $this->html->enableSection('main', ['data'   => json_encode($data),
                                    'width'  => '100%', 'height' => '300px',
                                    'title'  => 'Consultas por especialidade', 'xtitle' => 'Consultas',
                                    'ytitle' => 'Especialidade', 'uniqid' => uniqid()]);
        parent::add($this->html);
    }
}