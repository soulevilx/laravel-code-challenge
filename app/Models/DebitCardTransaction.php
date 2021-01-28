<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class DebitCardTransaction extends Authenticatable
{
    use HasFactory, SoftDeletes;

    // Currencies available
    public const CURRENCY_IDR = 'IDR';
    public const CURRENCY_SGD = 'SGD';
    public const CURRENCY_THB = 'THB';
    public const CURRENCY_VND = 'VND';

    public const CURRENCIES = [
        self::CURRENCY_IDR,
        self::CURRENCY_SGD,
        self::CURRENCY_THB,
        self::CURRENCY_VND,
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'debit_card_transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'debit_card_id',
        'amount',
        'currency_code',
    ];

    /**
     * A Debit card transaction belongs to a Debit card
     *
     * @return BelongsTo
     */
    public function debitCard()
    {
        return $this->belongsTo(DebitCard::class, 'debit_card_id');
    }
}
