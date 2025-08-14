<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Users Data Table')]
class UserTable extends Component
{

    public function render()
    {
        return view('livewire.user-table');
    }
}
