<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Carbon;
use Hyperf\Database\Model\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Wallet;

/**
 * @property int $id
 * @property string $full_name
 * @property string $document CPF ou CNPJ
 * @property string $email
 * @property string $password
 * @property string $type common ou shopkeeper
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Wallet $wallet
 */
class Transaction extends Model
{
    protected ?string $table = 'transactions';

    protected array $fillable = [
        'payer_id',
        'payee_id',
        'amount',
        'status',
    ];

    protected array $casts = [
        'id'         => 'integer',
        'payer_id'   => 'integer',
        'payee_id'   => 'integer',
        'amount'     => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}