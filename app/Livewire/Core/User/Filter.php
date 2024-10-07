<?php

namespace App\Livewire\Core\User;

use App\Repositories\Core\User\RoleRepository;
use Livewire\Component;

class Filter extends Component
{
    public $roles = [];
    public $role;

    public function mount()
    {
        $this->roles = RoleRepository::getIdAndNames()->toArray();
    }

    public function updated()
    {
        $this->dispatch('datatable-add-filter', [
            'role' => $this->role
        ]);
    }

    public function render()
    {
        return view('livewire.core.user.filter');
    }
}
