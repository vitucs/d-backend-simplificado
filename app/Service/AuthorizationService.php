<?php

declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Facades\Log;

class AuthorizationService
{
    private GuzzleClient $client;
    private string $url;

    public function __construct(
        ConfigRepository $config
    ) {
        $this->client = new GuzzleClient([
            'timeout' => 5.0,
        ]);
        
        $this->url = $config->get('services.authorization.url');
    }

    public function isAuthorized(): bool
    {
        try {
            $response = $this->client->get($this->url);

            $data = json_decode($response->getBody()->getContents(), true);
            return isset($data['data']['authorization']) && $data['data']['authorization'] === true;

        } catch (\Throwable $e) {
            Log::error('Falha ao comunicar com o serviço de autorização', [
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}