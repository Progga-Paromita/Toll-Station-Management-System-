<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TollStation extends Model
{
    protected $table = 'toll_stations';
    protected $primaryKey = 'station_id';
    public $timestamps = false;

    protected $fillable = [
        'station_name',
        'district',
        'highway',
        'lane_count',
        'station_type',
        'opening_date',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'opening_date' => 'datetime',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
            'lane_count'   => 'integer',
        ];
    }

    /**
     * The admin user who created this station.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
