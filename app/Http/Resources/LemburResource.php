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
            'uuid' => $this->uuid,
            'user_id' => $this->user_id,
            'jenis_hari' => $this->jenis_hari,
            'department' => $this->department,
            'tanggal_lembur' => $this->tanggal_lembur,
            'keterangan' => $this->keterangan_lembur,
            'jam_mulai' => $this->mulai_jam_lembur,
            'jam_selesai' => $this->selesai_jam_lembur,
            'create_at' => $this->created_at->format('Y-m-d H:i:s'),

            'status_final' => $this->status_lembur,
            'status_manajer' => $this->persetujuan_manajer,
            'status_direksi' => $this->persetujuan_direksi,
            'alasan_penolakan' => $this->alasan,
            'file_final_url' => $this->file_url,

            'jam_mulai_aktual' => $this->jam_mulai_aktual,
            'jam_selesai_aktual' => $this->jam_selesai_aktual,
            'foto_mulai_path' => $this->foto_mulai_path,
            'foto_selesai_path' => $this->foto_selesai_path,
        ];
    }
}
