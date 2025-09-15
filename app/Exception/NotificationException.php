<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

use Throwable;

class NotificationException extends Exception 
{
    public function __construct(string $message = 'Erro no contexto de notificacao.', int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
