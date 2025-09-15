<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\TransactionException;
use App\Exception\UserException;
use App\Http\Controllers\Controller;
use App\Service\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Validation\Factory as ValidatorFactory;
use Throwable;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService, private ValidatorFactory $validatorFactory
    ) {}

    public function create(Request $request, Response $response)
    {
        $validator = $this->validatorFactory->make($request->all(), [
            'full_name' => 'required|string|max:255',
            'document' => 'required|string|max:14',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'type' => 'required|in:common,shopkeeper',
        ]);

        if ($validator->fails()) {
            return $response->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        try {
            $serviceResponse = $this->userService->createUser($data);
            return $response->json(json_decode($serviceResponse->getBody()->getContents(), true), $serviceResponse->getStatusCode());
        } catch (UserException $e) {
            return $response->json(['error' => $e->getMessage()], $e->getCode());
        } catch (Throwable $e) {   
            return $response->json(['error' => 'Ocorreu um erro inesperado no servidor.'], 500);
        }
    }

    public function addBalance(Request $request, Response $response, int $id)
    {
        $validator = $this->validatorFactory->make($request->all(), [
            'value' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return $response->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $valueToAdd = (float) $data['value'];
        
        try {
            $serviceResponse = $this->userService->addBalance($valueToAdd, $id);
            return $response->json(json_decode($serviceResponse->getBody()->getContents(), true), $serviceResponse->getStatusCode());
        } catch (UserException $e) {
            return $response->json(['error' => $e->getMessage()], $e->getCode());
        } catch (TransactionException $e) {
            return $response->json(['error' => $e->getMessage()], $e->getCode());
        } catch (Throwable $e) {   
            return $response->json(['error' => 'Ocorreu um erro inesperado no servidor.'], 500);
        }
    }
}
