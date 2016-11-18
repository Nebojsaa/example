<?php
namespace App\View\Cell;

use Cake\View\Cell;

/**
 * This Cell renders the dynamic menu
 */
class HeaderCell extends Cell
{

    public function display()
    {
        $this->set('pageTitle','Nebojsa');
    }

}