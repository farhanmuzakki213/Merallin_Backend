<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LemburResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'jenis_hari' => $this->jenis_hari,
            'department' => $this->department,
            'tanggal_lembur' => $this->tanggal_lembur,
            'keterangan' => $this->keterangan_lembur,
            'jam_mulai' => $this->mulai_jam_lembur,
            'jam_selesai' => $this->selesai_jam_lembur,
            'diajukan_pada' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
