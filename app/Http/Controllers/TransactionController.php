<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\TransactionException;
use App\Exception\NotificationException;
use App\Http\Controllers\Controller;
use App\Service\TransactionService;
use App\Service\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Validation\Factory as ValidatorFactory;
use Throwable;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
        private NotificationService $notificationService,
        private ValidatorFactory $validatorFactory
    ) {}

    public function create(Request $request, Response $response)
    {
        $validator = $this->validatorFactory->make($request->all(), [
            'payer' => 'required|integer|exists:users,id',
            'payee' => 'required|integer|exists:users,id',
            'value' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return $response->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        try {
            $transactionResult = $this->transactionService->processTransaction(
                $data['payer'],
                $data['payee'],
                (float) $data['value']
            );

            try {
                $this->notificationService->sendTransactionNotification(
                    $transactionResult['payer'],
                    $transactionResult['payee'],
                    $transactionResult['value']
                );
            } catch (NotificationException $e) {
                return $response->json(['error' => $e->getMessage()], $e->getCode());
            }

            return $response->json([
                'message' => 'Transação realizada com sucesso.',
                'transaction' => $transactionResult['transaction']->toArray(),
                'payer_wallet' => $transactionResult['payer_wallet']->toArray(),
                'payee_wallet' => $transactionResult['payee_wallet']->toArray(),
            ], 200);
        } catch (TransactionException $e) {
            return $response->json(['error' => $e->getMessage()], $e->getCode());
        } catch (Throwable $e) {
            return $response->json(['error' => 'Ocorreu um erro inesperado no servidor.'], 500);
        }
    }
}