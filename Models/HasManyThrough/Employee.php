<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * 员工模型 - 4层关系的底层
 * Company → Department → Team → Employee
 */
class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'team_id',
        'position',
        'salary',
        'skills',
        'hire_date',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'hire_date' => 'date',
        'salary' => 'decimal:2',
    ];

    /**
     * 员工属于一个团队
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * 通过团队关联到部门 (hasOneThrough - 3层关系)
     * Employee → Team → Department
     */
    public function department(): HasOneThrough
    {
        return $this->hasOneThrough(
            Department::class,  // 目标模型
            Team::class,        // 中间模型
            'id',               // 中间模型的外键 (teams.id)
            'id',               // 目标模型的外键 (departments.id)
            'team_id',          // 当前模型的本地键 (employees.team_id)
            'department_id'     // 中间模型的本地键 (teams.department_id)
        );
    }

    /**
     * 通过团队和部门关联到公司 (4层关系 - 使用 Accessor)
     * Employee → Team → Department → Company
     */
    public function getCompanyAttribute(): ?Company
    {
        return $this->team?->department?->company;
    }

    /**
     * 获取员工所属部门 (使用 Accessor)
     */
    public function getDepartmentNameAttribute(): ?string
    {
        return $this->team?->department?->name;
    }

    /**
     * 获取员工所属公司 (使用 Accessor)
     */
    public function getCompanyNameAttribute(): ?string
    {
        return $this->team?->department?->company?->name;
    }

    /**
     * 获取员工的完整层级路径
     */
    public function getHierarchyPathAttribute(): string
    {
        $team = $this->team;
        if (!$team) {
            return $this->name;
        }

        $department = $team->department;
        if (!$department) {
            return "{$team->name} > {$this->name}";
        }

        $company = $department->company;
        if (!$company) {
            return "{$department->name} > {$team->name} > {$this->name}";
        }

        return "{$company->name} > {$department->name} > {$team->name} > {$this->name}";
    }

    /**
     * 作用域：按公司筛选员工
     */
    public function scopeFromCompany($query, int $companyId)
    {
        return $query->whereHas('team.department', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        });
    }

    /**
     * 作用域：按部门筛选员工
     */
    public function scopeFromDepartment($query, int $departmentId)
    {
        return $query->whereHas('team', function ($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        });
    }

    /**
     * 作用域：按技能筛选员工
     */
    public function scopeWithSkill($query, string $skill)
    {
        return $query->where('skills', 'like', "%{$skill}%");
    }

    /**
     * 作用域：按薪资范围筛选员工
     */
    public function scopeSalaryBetween($query, float $min, float $max)
    {
        return $query->whereBetween('salary', [$min, $max]);
    }

    /**
     * 作用域：按入职时间筛选员工
     */
    public function scopeHiredAfter($query, string $date)
    {
        return $query->where('hire_date', '>=', $date);
    }
}
