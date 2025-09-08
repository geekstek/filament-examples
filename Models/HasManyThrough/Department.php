<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 部门模型 - 4层关系的第二层
 * Company → Department → Team → Employee
 */
class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'company_id',
        'manager_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 部门属于一个公司
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * 一个部门有多个团队
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /**
     * 通过团队关联到所有员工 (hasManyThrough - 3层关系)
     * Department → Team → Employee
     */
    public function employees(): HasMany
    {
        return $this->hasManyThrough(
            Employee::class,    // 目标模型
            Team::class,        // 中间模型
            'department_id',    // 中间模型的外键 (teams.department_id)
            'team_id',          // 目标模型的外键 (employees.team_id)
            'id',               // 当前模型的本地键 (departments.id)
            'id'                // 中间模型的本地键 (teams.id)
        );
    }

    /**
     * 获取部门员工总数
     */
    public function getEmployeeCountAttribute(): int
    {
        return $this->employees()->count();
    }

    /**
     * 获取部门团队总数
     */
    public function getTeamCountAttribute(): int
    {
        return $this->teams()->count();
    }

    /**
     * 作用域：按员工数量排序部门
     */
    public function scopeOrderByEmployeeCount($query, string $direction = 'desc')
    {
        return $query->withCount('employees')
            ->orderBy('employees_count', $direction);
    }

    /**
     * 作用域：筛选有特定技能员工的部门
     */
    public function scopeWithSkill($query, string $skill)
    {
        return $query->whereHas('employees', function ($q) use ($skill) {
            $q->where('skills', 'like', "%{$skill}%");
        });
    }
}
