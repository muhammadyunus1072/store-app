<?php

namespace App\Traits\Livewire;

use Livewire\Attributes\On;

trait WithDatatableHeader
{
    abstract public function getHeader($data);

    #[On('datatable-header-handler')]
    public function datatableHeaderHandler($data)
    {
        $this->getHeader($data);
    }
}
