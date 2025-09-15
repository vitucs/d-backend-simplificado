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
class User extends Model
{
    protected ?string $table = 'users';
    protected array $fillable = [
        'full_name',
        'document',
        'email',
        'password',
        'type',
    ];

    protected array $hidden = [
        'password',
    ];

    protected array $casts = [
        'id'         => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }
}