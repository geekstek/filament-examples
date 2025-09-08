<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 公司模型 - 4层关系的顶层
 * Company → Department → Team → Employee
 */
class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 一个公司有多个部门
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * 通过部门关联到所有团队 (hasManyThrough - 3层关系)
     * Company → Department → Team
     */
    public function teams(): HasMany
    {
        return $this->hasManyThrough(
            Team::class,        // 目标模型
            Department::class,  // 中间模型
            'company_id',       // 中间模型的外键 (departments.company_id)
            'department_id',    // 目标模型的外键 (teams.department_id)
            'id',               // 当前模型的本地键 (companies.id)
            'id'                // 中间模型的本地键 (departments.id)
        );
    }

    /**
     * 通过部门和团队关联到所有员工 (hasManyThrough - 4层关系)
     * 注意：Laravel 原生不支持4层 hasManyThrough
     * 这里展示的是理论上的实现，实际需要使用其他方法
     */
    public function employees(): HasMany
    {
        // 方法1：使用子查询 (推荐)
        return $this->hasMany(Employee::class, 'id', 'id')
            ->whereIn('team_id', function ($query) {
                $query->select('teams.id')
                    ->from('teams')
                    ->join('departments', 'teams.department_id', '=', 'departments.id')
                    ->where('departments.company_id', $this->id);
            });
    }

    /**
     * 获取公司所有员工的自定义方法 (真正的4层关系)
     */
    public function getAllEmployees()
    {
        return Employee::whereHas('team.department', function ($query) {
            $query->where('company_id', $this->id);
        })->get();
    }

    /**
     * 获取公司员工数量
     */
    public function getEmployeeCountAttribute(): int
    {
        return Employee::whereHas('team.department', function ($query) {
            $query->where('company_id', $this->id);
        })->count();
    }

    /**
     * 作用域：根据员工技能筛选公司
     */
    public function scopeWithEmployeeSkill($query, string $skill)
    {
        return $query->whereHas('departments.teams.employees', function ($q) use ($skill) {
            $q->where('skills', 'like', "%{$skill}%");
        });
    }
}
