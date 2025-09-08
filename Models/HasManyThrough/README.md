# Laravel HasManyThrough 4层关系示例

## 概述

本示例演示了 Laravel Eloquent 中的 `hasManyThrough` 关系，特别是如何处理4层复杂关系。`hasManyThrough` 允许你通过一个或多个中间模型来访问远程的一对多关系。

## 4层关系结构

```
Company (公司)
    ↓ hasMany
Department (部门)
    ↓ hasMany
Team (团队)
    ↓ hasMany
Employee (员工)
```

这种结构在企业管理系统中非常常见，我们可以通过 `hasManyThrough` 实现跨层级的数据访问。

## 数据库表结构

### companies 表
```sql
CREATE TABLE companies (
    id BIGINT UNSIGNED PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL,
    address TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### departments 表
```sql
CREATE TABLE departments (
    id BIGINT UNSIGNED PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL,
    company_id BIGINT UNSIGNED,
    manager_name VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id)
);
```

### teams 表
```sql
CREATE TABLE teams (
    id BIGINT UNSIGNED PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    department_id BIGINT UNSIGNED,
    team_leader VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);
```

### employees 表
```sql
CREATE TABLE employees (
    id BIGINT UNSIGNED PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    team_id BIGINT UNSIGNED,
    position VARCHAR(255),
    salary DECIMAL(10,2),
    skills TEXT,
    hire_date DATE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id)
);
```

## 模型关系定义

### Company 模型 (顶层)

```php
class Company extends Model
{
    // 直接关系：公司有多个部门
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    // 3层关系：公司 → 部门 → 团队
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

    // 4层关系：公司 → 部门 → 团队 → 员工 (自定义实现)
    public function getAllEmployees()
    {
        return Employee::whereHas('team.department', function ($query) {
            $query->where('company_id', $this->id);
        })->get();
    }
}
```

### Department 模型 (第二层)

```php
class Department extends Model
{
    // 反向关系：部门属于公司
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // 直接关系：部门有多个团队
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    // 3层关系：部门 → 团队 → 员工
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
}
```

### Team 模型 (第三层)

```php
class Team extends Model
{
    // 反向关系：团队属于部门
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // 直接关系：团队有多个员工
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // 3层关系：团队 → 部门 → 公司
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
}
```

### Employee 模型 (底层)

```php
class Employee extends Model
{
    // 直接关系：员工属于团队
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    // 3层关系：员工 → 团队 → 部门
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

    // 4层关系：员工 → 团队 → 部门 → 公司 (使用 Accessor)
    public function getCompanyAttribute(): ?Company
    {
        return $this->team?->department?->company;
    }
}
```

## HasManyThrough 参数详解

```php
$this->hasManyThrough(
    Employee::class,    // 1. 目标模型 - 你想要访问的最终模型
    Team::class,        // 2. 中间模型 - 连接当前模型和目标模型的中间表
    'department_id',    // 3. 中间模型的外键 - 中间表中引用当前模型的字段
    'team_id',          // 4. 目标模型的外键 - 目标表中引用中间表的字段
    'id',               // 5. 当前模型的本地键 - 当前表的主键字段
    'id'                // 6. 中间模型的本地键 - 中间表的主键字段
);
```

## 4层关系的实现策略

### 策略1：使用 whereHas 查询 (推荐)

```php
// 获取公司的所有员工
public function getAllEmployees()
{
    return Employee::whereHas('team.department', function ($query) {
        $query->where('company_id', $this->id);
    })->get();
}

// 获取有特定技能的公司员工
public function getEmployeesWithSkill(string $skill)
{
    return Employee::whereHas('team.department', function ($query) {
        $query->where('company_id', $this->id);
    })->where('skills', 'like', "%{$skill}%")->get();
}
```

### 策略2：使用 Accessor 属性 (简单访问)

```php
// Employee 模型中
public function getCompanyAttribute(): ?Company
{
    return $this->team?->department?->company;
}

public function getHierarchyPathAttribute(): string
{
    $team = $this->team;
    $department = $team?->department;
    $company = $department?->company;
    
    return "{$company?->name} > {$department?->name} > {$team?->name} > {$this->name}";
}
```

### 策略3：使用作用域 (查询构建器)

```php
// Employee 模型中
public function scopeFromCompany($query, int $companyId)
{
    return $query->whereHas('team.department', function ($q) use ($companyId) {
        $q->where('company_id', $companyId);
    });
}

// 使用示例
$employees = Employee::fromCompany(1)->get();
```

## 实际使用示例

### 创建测试数据

```php
// 创建公司
$company = Company::create([
    'name' => '科技有限公司',
    'code' => 'TECH001',
    'address' => '深圳市南山区'
]);

// 创建部门
$department = Department::create([
    'name' => '研发部',
    'code' => 'RD',
    'company_id' => $company->id,
    'manager_name' => '张经理'
]);

// 创建团队
$team = Team::create([
    'name' => '前端团队',
    'department_id' => $department->id,
    'team_leader' => '李组长',
    'description' => '负责前端开发工作'
]);

// 创建员工
$employee = Employee::create([
    'name' => '王开发',
    'email' => 'wang@company.com',
    'team_id' => $team->id,
    'position' => '高级前端工程师',
    'salary' => 15000.00,
    'skills' => 'Vue.js,React,TypeScript',
    'hire_date' => '2023-01-15'
]);
```

### 查询示例

```php
// 1. 获取公司的所有部门
$company = Company::find(1);
$departments = $company->departments;

// 2. 获取公司的所有团队 (3层关系)
$teams = $company->teams;
echo "公司共有 " . $teams->count() . " 个团队";

// 3. 获取公司的所有员工 (4层关系)
$employees = $company->getAllEmployees();
foreach ($employees as $employee) {
    echo $employee->hierarchy_path;
}

// 4. 获取部门的所有员工 (3层关系)
$department = Department::find(1);
$employees = $department->employees;

// 5. 使用预加载优化查询
$employees = Employee::with([
    'team' => function ($query) {
        $query->with([
            'department' => function ($query) {
                $query->with('company');
            }
        ]);
    }
])->get();

// 6. 复杂查询：获取有特定技能的员工
$phpDevelopers = Employee::withSkill('PHP')
    ->fromCompany(1)
    ->salaryBetween(10000, 20000)
    ->get();

// 7. 统计查询
$companyStats = [
    'departments' => $company->departments()->count(),
    'teams' => $company->teams()->count(),
    'employees' => $company->employee_count,
    'average_salary' => $company->getAllEmployees()->avg('salary')
];
```

### 高级查询示例

```php
// 1. 按部门统计员工数量
$departmentStats = Department::withCount('employees')
    ->where('company_id', 1)
    ->orderByEmployeeCount()
    ->get();

// 2. 查找有特定技能的部门
$departments = Department::withSkill('Laravel')
    ->with(['company', 'teams'])
    ->get();

// 3. 按薪资范围查找团队
$highSalaryTeams = Team::withSalaryRange(15000, 25000)
    ->with(['department.company'])
    ->get();

// 4. 复合条件查询
$seniorDevelopers = Employee::fromCompany(1)
    ->withSkill('Laravel')
    ->salaryBetween(15000, 30000)
    ->hiredAfter('2022-01-01')
    ->with('team.department.company')
    ->get();

// 5. 聚合查询
$companyReport = Company::find(1);
$report = [
    'total_employees' => $companyReport->getAllEmployees()->count(),
    'departments' => $companyReport->departments()->count(),
    'teams' => $companyReport->teams()->count(),
    'average_salary' => $companyReport->getAllEmployees()->avg('salary'),
    'total_payroll' => $companyReport->getAllEmployees()->sum('salary'),
    'skills_distribution' => $companyReport->getAllEmployees()
        ->pluck('skills')
        ->flatMap(fn($skills) => explode(',', $skills))
        ->countBy()
        ->toArray()
];
```

## 性能优化策略

### 1. 预加载关系

```php
// 优化前：N+1 查询问题
$employees = Employee::all();
foreach ($employees as $employee) {
    echo $employee->team->department->company->name; // 每次都查询数据库
}

// 优化后：使用预加载
$employees = Employee::with('team.department.company')->get();
foreach ($employees as $employee) {
    echo $employee->team->department->company->name; // 从内存中获取
}
```

### 2. 选择性字段加载

```php
$employees = Employee::with([
    'team:id,name,department_id',
    'team.department:id,name,company_id',
    'team.department.company:id,name'
])->select('id', 'name', 'team_id', 'salary')->get();
```

### 3. 使用索引优化

```sql
-- 在迁移文件中添加索引
CREATE INDEX idx_departments_company_id ON departments(company_id);
CREATE INDEX idx_teams_department_id ON teams(department_id);
CREATE INDEX idx_employees_team_id ON employees(team_id);
CREATE INDEX idx_employees_skills ON employees(skills);
CREATE INDEX idx_employees_salary ON employees(salary);
```

### 4. 缓存常用查询

```php
// 使用 Laravel 缓存
$companyEmployees = Cache::remember("company_{$companyId}_employees", 3600, function () use ($companyId) {
    return Company::find($companyId)->getAllEmployees();
});
```

## Laravel 关系层级限制与解决方案

### 原生支持的关系层级

- **hasMany/belongsTo**: 无限制，但每层都需要单独查询
- **hasOneThrough/hasManyThrough**: 最多支持3层关系
- **4层及以上**: 需要使用自定义方法

### 4层关系的最佳实践

1. **使用 whereHas 查询**: 性能好，支持复杂条件
2. **使用 Accessor 属性**: 简单直观，适合单个对象访问
3. **使用作用域方法**: 可复用，支持链式调用
4. **合理使用预加载**: 避免 N+1 查询问题

## 实际应用场景

### 1. 企业管理系统
- 集团 → 子公司 → 部门 → 员工
- 总部 → 分公司 → 门店 → 销售员

### 2. 电商平台
- 平台 → 商家 → 分类 → 商品
- 供应商 → 品牌 → 系列 → 产品

### 3. 教育系统
- 教育局 → 学校 → 年级 → 学生
- 大学 → 学院 → 专业 → 课程

### 4. 医疗系统
- 卫生局 → 医院 → 科室 → 医生
- 医疗集团 → 分院 → 病区 → 病床

### 5. 地理位置
- 国家 → 省份 → 城市 → 区域
- 大陆 → 国家 → 州省 → 城市

## 注意事项与最佳实践

### 1. 性能考虑
- 始终使用预加载避免 N+1 问题
- 合理使用 `select()` 限制查询字段
- 为外键字段添加数据库索引
- 对于大数据量，考虑使用分页

### 2. 代码维护
- 使用作用域方法提高代码复用性
- 为复杂查询编写专门的服务类
- 使用 Accessor 简化属性访问
- 编写完整的测试用例

### 3. 数据一致性
- 使用外键约束保证数据完整性
- 考虑使用软删除避免数据丢失
- 实现级联操作的业务逻辑
- 使用数据库事务保证操作原子性

### 4. 查询优化
- 避免在循环中执行查询
- 使用批量操作替代单条操作
- 合理使用缓存机制
- 监控慢查询并优化

这个4层 HasManyThrough 示例展示了如何在 Laravel 中处理复杂的企业级数据关系，提供了完整的解决方案和最佳实践指导。
