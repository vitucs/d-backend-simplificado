<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id ID do usuário (do service-users)
 * @property float $balance
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Wallet extends Model
{
    protected ?string $table = 'wallets';

    protected array $fillable = [
        'user_id',
        'balance',
    ];

    protected array $casts = [
        'id'         => 'integer',
        'user_id'    => 'integer',
        'balance'    => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}