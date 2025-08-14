<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public array $monthlySales = [];
    public float $monthlyTarget = 0;
    public array $statistics = [];
    public array $mapMarkers = [];

    public function mount()
    {
        // Data untuk Chart 1 (Monthly Sales)
        $this->monthlySales = [168, 385, 201, 298, 187, 195, 291, 110, 215, 390, 280, 112];

        // Data untuk Chart 2 (Monthly Target)
        $this->monthlyTarget = 75.55;

        // Data untuk Chart 3 (Statistics - Sales & Revenue)
        $this->statistics = [
            [ 'name' => 'Sales', 'data' => [180, 190, 170, 160, 175, 165, 170, 205, 230, 210, 240, 235] ],
            [ 'name' => 'Revenue', 'data' => [40, 30, 50, 40, 55, 40, 70, 100, 110, 120, 150, 140] ],
        ];

        // Data untuk Peta (Map Markers)
        $this->mapMarkers = [
            [ 'name' => 'Indonesia', 'coords' => [-2.5489, 118.0149] ],
            [ 'name' => 'United Kingdom', 'coords' => [55.3781, 3.436] ],
            [ 'name' => 'United States', 'coords' => [37.0902, -95.7129] ],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
