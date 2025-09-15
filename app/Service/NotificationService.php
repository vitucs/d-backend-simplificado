<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\NotificationException;
use App\Models\User;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Throwable;

class NotificationService
{
    private GuzzleClient $client;
    private string $notificationServiceUrl;

    public function __construct(ConfigRepository $config)
    {
        $this->client = new GuzzleClient();
        $this->notificationServiceUrl = $config->get('services.notification.url');
    }

    public function sendTransactionNotification(User $payer, User $payee, float $value): void
    {
        $messagePayer = "Olá {$payer->full_name}, sua transação de R$" . number_format($value, 2, ',', '.') . " para {$payee->full_name} foi realizada com sucesso.";
        $messagePayee = "Olá {$payee->full_name}, você recebeu uma transação de R$" . number_format($value, 2, ',', '.') . " de {$payer->full_name}.";

        try {
            $this->send($payer->email, $messagePayer);
        } catch (Throwable $e) {
            throw new NotificationException('Falha ao enviar notificação da transação do pagador.', 500, $e);
        }

        try {
            $this->send($payee->email, $messagePayee);
        } catch (Throwable $e) {
            throw new NotificationException('Falha ao enviar notificação da transação do recebedor.', 500, $e);
        }

    }

    private function send(string $email, string $message): bool
    {
        try {
            $response = $this->client->post($this->notificationServiceUrl, [
                'json' => [
                    'email' => $email,
                    'message' => $message,
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if ($data['message'] !== 'Success') {
                 throw new \Exception('Serviço externo de notificação retornou falha.');
            }
            
            return true;

        } catch (Throwable $e) {
            throw $e;
        }
    }
}