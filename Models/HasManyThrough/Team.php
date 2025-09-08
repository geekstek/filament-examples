<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * 团队模型 - 4层关系的第三层
 * Company → Department → Team → Employee
 */
class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'department_id',
        'team_leader',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 团队属于一个部门
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * 一个团队有多个员工
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * 通过部门关联到公司 (hasOneThrough - 3层关系)
     * Team → Department → Company
     */
    public function company(): HasOneThrough
    {
        return $this->hasOneThrough(
            Company::class,     // 目标模型
            Department::class,  // 中间模型
            'id',               // 中间模型的外键 (departments.id)
            'id',               // 目标模型的外键 (companies.id)
            'department_id',    // 当前模型的本地键 (teams.department_id)
            'company_id'        // 中间模型的本地键 (departments.company_id)
        );
    }

    /**
     * 获取团队员工总数
     */
    public function getEmployeeCountAttribute(): int
    {
        return $this->employees()->count();
    }

    /**
     * 获取团队平均薪资
     */
    public function getAverageSalaryAttribute(): float
    {
        return $this->employees()->avg('salary') ?? 0.0;
    }

    /**
     * 作用域：按员工数量筛选团队
     */
    public function scopeWithEmployeeCount($query, int $minCount = 1)
    {
        return $query->has('employees', '>=', $minCount);
    }

    /**
     * 作用域：按薪资范围筛选团队
     */
    public function scopeWithSalaryRange($query, float $minSalary, float $maxSalary)
    {
        return $query->whereHas('employees', function ($q) use ($minSalary, $maxSalary) {
            $q->whereBetween('salary', [$minSalary, $maxSalary]);
        });
    }
}
