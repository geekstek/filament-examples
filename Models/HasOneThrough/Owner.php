<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Owner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'car_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 所属账单分类
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * 通过汽车关联到机械工 (hasOneThrough 示例)
     */
    public function mechanic(): HasOneThrough
    {
        return $this->hasOneThrough(
            Mechanic::class, // 目标模型
            Car::class, // 中间模型
            'id', // 中间模型的外键 (cars.id)
            'id', // 目标模型的外键 (mechanics.id)
            'car_id', // 当前模型的本地键 (owners.car_id)
            'mechanic_id' // 中间模型的本地键 (cars.mechanic_id)
        );
    }
}
