<?php

namespace App\Services;

use Aws\Rekognition\RekognitionClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class AwsRekognitionService
{
    protected $client;
    protected $collectionId;

    public function __construct()
    {
        $this->client = new RekognitionClient([
            'region' => config('services.aws.region'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('services.aws.key'),
                'secret' => config('services.aws.secret'),
            ],
        ]);
        $this->collectionId = config('services.aws.collection_id');
        $this->createCollectionIfNotExists();
    }

    private function createCollectionIfNotExists()
    {
        try {
            $this->client->describeCollection(['CollectionId' => $this->collectionId]);
        } catch (\Aws\Rekognition\Exception\RekognitionException $e) {
            if ($e->getAwsErrorCode() === 'ResourceNotFoundException') {
                $this->client->createCollection(['CollectionId' => $this->collectionId]);
            }
        }
    }

    public function indexFace(UploadedFile $image, string $userId): ?string
    {
        try {
            $result = $this->client->indexFaces([
                'CollectionId' => $this->collectionId,
                'ExternalImageId' => 'user-' . $userId,
                'Image' => ['Bytes' => file_get_contents($image->getRealPath())],
                'MaxFaces' => 1,
                'QualityFilter' => 'AUTO',
            ]);

            if (!empty($result['FaceRecords'])) {
                return $result['FaceRecords'][0]['Face']['FaceId'];
            }
        } catch (\Exception $e) {
            Log::error('AWS Index Face Error: ' . $e->getMessage());
        }
        return null;
    }

    public function searchFace(UploadedFile $image): ?string
    {
        try {
            $result = $this->client->searchFacesByImage([
                'CollectionId' => $this->collectionId,
                'FaceMatchThreshold' => 85, // Ambang batas kemiripan 85%
                'Image' => ['Bytes' => file_get_contents($image->getRealPath())],
                'MaxFaces' => 1,
            ]);

            if (!empty($result['FaceMatches'])) {
                return $result['FaceMatches'][0]['Face']['FaceId'];
            }
        } catch (\Exception $e) {
            Log::error('AWS Search Face Error: ' . $e->getMessage());
        }
        return null;
    }
}
