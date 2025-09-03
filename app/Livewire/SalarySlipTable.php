<?php

namespace App\Livewire;

use App\Models\SalarySlip;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Salary Slips Management')]
class SalarySlipTable extends Component
{
    use WithPagination, WithFileUploads;

    public $perPage = 10;
    public $search = '';

    // Properti untuk modal
    public $showModal = false;
    public $slipId;
    public $userId, $period;
    public $salarySlipFile;
    public $existingFilePath; // Untuk menyimpan path file saat edit

    // Membersihkan properti dan membuka modal untuk 'Create'
    public function openModal()
    {
        $this->reset(['slipId', 'userId', 'period', 'salarySlipFile', 'existingFilePath']);
        $this->resetErrorBag();
        $this->showModal = true;
    }

    // Mengisi properti dengan data yang ada dan membuka modal untuk 'Edit'
    public function edit($id)
    {
        $slip = SalarySlip::findOrFail($id);
        $this->slipId = $slip->id;
        $this->userId = $slip->user_id;
        $this->period = $slip->period->format('Y-m');
        $this->existingFilePath = $slip->original_file_name; // Tampilkan nama file yang ada
        $this->salarySlipFile = null; // Reset input file
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function save()
    {
        // Aturan validasi dinamis: file wajib untuk 'create', opsional untuk 'update'
        $rules = [
            'userId' => 'required|exists:users,id',
            'period' => 'required|date_format:Y-m',
            'salarySlipFile' => 'required|file|mimes:pdf|max:2048',
        ];

        $validatedData = $this->validate($rules);

        $user = User::findOrFail($this->userId);
        $periodDate = \Carbon\Carbon::createFromFormat('Y-m', $this->period)->startOfMonth();

        $data = [
            'user_id' => $this->userId,
            'period' => $periodDate,
        ];

        // Jika ada file baru yang diunggah (baik saat create atau update)
        if ($this->salarySlipFile) {
            // Hapus file lama jika ini adalah proses update
            if ($this->slipId) {
                $oldSlip = SalarySlip::find($this->slipId);
                if ($oldSlip && Storage::disk('public')->exists($oldSlip->file_path)) {
                    Storage::disk('public')->delete($oldSlip->file_path);
                }
            }

            $fileName = Str::slug($user->name) . '_' . $periodDate->format('m-Y') . '_' . uniqid() . '.' . $this->salarySlipFile->getClientOriginalExtension();
            $data['file_path'] = $this->salarySlipFile->storeAs('salary_slips', $fileName, 'public');
        }

        // Gunakan updateOrCreate: update jika slipId ada, create jika tidak.
        SalarySlip::updateOrCreate(['id' => $this->slipId], $data);

        session()->flash('message', 'Salary slip successfully ' . ($this->slipId ? 'updated.' : 'uploaded.'));
        $this->closeModal();
    }

    public function delete($slipId)
    {
        $slip = SalarySlip::findOrFail($slipId);
        if (Storage::disk('public')->exists($slip->file_path)) {
            Storage::disk('public')->delete($slip->file_path);
        }
        $slip->delete();
        session()->flash('message', 'Salary slip successfully deleted.');
    }

    public function render()
    {
        $slips = SalarySlip::with('user')
            ->where(function ($query) {
                $query->whereHas('user', function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%');
                })->orWhere('file_path', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate($this->perPage);

        $users = User::orderBy('name')->get();

        return view('livewire.salarySlipTable.salary-slip-table', [
            'slips' => $slips,
            'users' => $users,
        ]);
    }
}
