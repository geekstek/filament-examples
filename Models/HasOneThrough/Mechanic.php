<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mechanic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class);
    }

    public function owners(): HasMany
    {
        return $this->hasManyThrough(
            Owner::class, // 目标模型
            Car::class, // 中间模型
            'mechanic_id', // 中间模型的外键 (cars.mechanic_id)
            'car_id', // 目标模型的外键 (owners.car_id)
            'id', // 当前模型的本地键 (mechanics.id)
            'id' // 中间模型的本地键 (cars.id)
        );
    }
}
