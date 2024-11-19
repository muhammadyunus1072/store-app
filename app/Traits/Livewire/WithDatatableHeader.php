<?php

namespace App\Traits\Livewire;

use Livewire\Attributes\On;

trait WithDatatableHeader
{
    abstract public function getHeaderData();

    #[On('on-search-updated')]
    #[On('datatable-add-filter')]
    public function addFilter($filter)
    {
        foreach ($filter as $key => $value) {
            $this->$key = $value;
        }
    }

    public function render()
    {
        return view('livewire.livewire-datatable-header', [
            'data' => $this->getHeaderData(),
        ]);
    }
}
