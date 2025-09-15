<?php

declare(strict_types=1);

namespace App\Service;
 
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use App\Exception\TransactionException;
use App\Exception\UserException;
use App\Service\TransactionService;
use App\Models\User;
use App\Models\Wallet;
use Throwable;

class UserService
{
    public function __construct(
        private TransactionService $transactionService
    ) {
    }

    public function createUser(array $data): GuzzleResponse
    {
        try {
            $user = User::create($data);

            $wallet = new Wallet(['user_id' => $user->id, 'balance' => 0.0]);
            $user->wallet()->save($wallet);

            return new GuzzleResponse(201, [], json_encode([
                'message' => 'Usuário e carteira criados com sucesso.',
                'user' => $user->toArray(),
                'wallet' => $wallet->toArray(),
            ]));
        } catch (UserException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new UserException('Ocorreu um erro inesperado ao criar o usuário.', 500, $e);
        }
    }

    public function addBalance(float $value, int $id): GuzzleResponse
    {
        try {
            $result = $this->transactionService->addBalanceToUserWallet($id, $value);
            return new GuzzleResponse(200, [], json_encode($result));
        } catch (TransactionException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new UserException('Ocorreu um erro inesperado ao adicionar saldo.', 500);
        }
    }
}
