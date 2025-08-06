<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;

class AzureFaceService
{
    protected $endpoint;
    protected $apiKey;
    protected $personGroupId;

    public function __construct()
    {
        $this->endpoint = config('services.azure.face_api_endpoint');
        $this->apiKey = config('services.azure.face_api_key');
        $this->personGroupId = config('services.azure.person_group_id');
    }

    public function createPerson(string $name): ?string
    {
        $response = Http::withHeaders(['Ocp-Apim-Subscription-Key' => $this->apiKey])
            ->post("{$this->endpoint}face/v1.0/persongroups/{$this->personGroupId}/persons", [
                'name' => $name,
            ]);

        if ($response->successful()) {
            return $response->json('personId');
        }
        return null;
    }

    public function addFaceToPerson(string $personId, UploadedFile $image): bool
    {
        $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->apiKey,
                'Content-Type' => 'application/octet-stream'
            ])
            ->withBody(file_get_contents($image->getRealPath()), 'application/octet-stream')
            ->post("{$this->endpoint}face/v1.0/persongroups/{$this->personGroupId}/persons/{$personId}/persistedFaces");

        return $response->successful();
    }

    public function trainPersonGroup(): void
    {
        Http::withHeaders(['Ocp-Apim-Subscription-Key' => $this->apiKey])
            ->post("{$this->endpoint}face/v1.0/persongroups/{$this->personGroupId}/train");
    }

    public function identify(string $faceId): ?string
    {
        $response = Http::withHeaders(['Ocp-Apim-Subscription-Key' => $this->apiKey])
            ->post("{$this->endpoint}face/v1.0/identify", [
                'personGroupId' => $this->personGroupId,
                'faceIds' => [$faceId],
                'maxNumOfCandidatesReturned' => 1,
                'confidenceThreshold' => 0.7,
            ]);

        if ($response->successful() && !empty($response->json('0.candidates'))) {
            return $response->json('0.candidates.0.personId');
        }
        return null;
    }
}
