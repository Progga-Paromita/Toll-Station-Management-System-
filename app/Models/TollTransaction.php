<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TollTransaction extends Model
{
    protected $table = 'toll_transactions';
    protected $primaryKey = 'transaction_id';
    public $timestamps = false;

    protected $fillable = [
        'vehicle_number',
        'vehicle_type',
        'toll_amount',
        'payment_method',
        'operator_id',
        'created_at'
    ];

    protected function casts(): array
    {
        return [
            'toll_amount' => 'float',
            'created_at' => 'datetime',
        ];
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id', 'user_id');
    }
}
