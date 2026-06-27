<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $table = 'vehicles';
    protected $primaryKey = 'vehicle_id';
    public $timestamps = false;

    protected $fillable = [
        'registration_number',
        'owner_name',
        'owner_phone',
        'vehicle_type',
        'color',
        'manufacturer',
        'model',
        'weight',
        'registration_date',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'registration_date' => 'datetime',
            'created_at'        => 'datetime',
            'updated_at'        => 'datetime',
            'weight'            => 'float',
        ];
    }

    /**
     * Get the user who registered this vehicle.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Get the toll transactions for this vehicle.
     */
    public function transactions()
    {
        return $this->hasMany(TollTransaction::class, 'vehicle_id', 'vehicle_id');
    }
}
