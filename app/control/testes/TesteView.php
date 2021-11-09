<?php

class TesteView extends TPage
{
    public function __construct()
    {
        parent::__construct();

        parent::add(new TLabel('Teste'));
    }
}