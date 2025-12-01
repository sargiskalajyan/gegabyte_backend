<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'orders';


    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id','package_id','amount','currency','gateway','status',
        'reference','payload','idempotency_key','meta'
    ];


    /**
     * @var string[]
     */
    protected $casts = [
        'payload' => 'array',
        'meta' => 'array',
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }


    /**
     * @param string|null $reference
     * @param array $payload
     * @return void
     */
    public function markPaid(string $reference = null, array $payload = [])
    {
        $this->status = 'paid';
        if ($reference)  {
            $this->reference = $reference;
        }
        $this->payload = array_merge($this->payload ?? [], $payload);
        $this->save();
    }
}
