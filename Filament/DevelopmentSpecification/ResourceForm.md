# Filament 4 Resource类 Form 开发规范

`filament-4-resource-form`

## 一、布局架构原则

### 1.1 布局优先级

使用顺序：基础字段 → Section → Tabs → Wizard

```
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Wizard;
```

适用场景：

- 字段数 < 8：直接平铺
- 字段数 8-15：使用 2-3 个 Section
- 字段数 >15：使用 Tabs 分组

### 1.2 Section 最佳实践

```php
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

Section::make('基础信息')
    ->description('带 * 号为必填字段')
    ->icon(Heroicon::OutlinedBars4)
    ->schema([
        // 字段定义
    ])
    ->columns(2) // 响应式布局
```

## 二、视觉优化规范

### 2.1 图标使用规则

图标选择原则：
必需使用 `Filament\Support\Icons\Heroicon` 类来定义图标

- 表单操作：Heroicon::XXX 系列（实心）
- 信息展示：Heroicon::OutlinedXXX 系列（线框）

禁用方案：

- ❌ 同一 Section 混合使用不同系列图标
- ✅ 全站保持统一图标风格

## 三、命名与翻译规范

### 3.1 Section 命名规范

每个 Section 需添加翻译标识：

```php
Section::make(__(self::TRANSLATIONS . 'sections.<section名>.label'))
    ->heading(__(self::TRANSLATIONS . 'sections.<section名>.heading'))
    ->description(__(self::TRANSLATIONS . 'sections.<section名>.description'))

```

### 3.2 字段命名规范

所有字段需添加标准化标签：

```php
TextInput::make('name')
    ->label(__(self::TRANSLATIONS . 'fields.name.label'))

```

## 四、常用字段优化规范

### 4.1 Select 字段优化

```php
Select::make('category_id')
    ->label(__(self::TRANSLATIONS . 'fields.category_id.label'))
    ->relationship('category', 'name')
    ->preload() // 预加载选项
    ->searchable() // 支持搜索

```

### 4.2 FileUpload 字段优化

```php
FileUpload::make('avatar')
    ->label(__(self::TRANSLATIONS . 'fields.avatar.label'))
    ->directory('avatars') // 指定存储目录
    ->panelLayout('grid') // 网格预览布局
    ->previewable() // 支持预览
    ->downloadable() // 支持下载
    ->image() // 指定为图片类型
    ->imageEditor() // 支持图片编辑

```

### 4.3 日期时间字段优化

日期选择器：

```php
DatePicker::make('published_at')
    ->label(__(self::TRANSLATIONS . 'fields.published_at.label'))
    ->native(false) // 使用自定义日期选择器
    ->displayFormat('Y-m-d') // 显示格式

```

日期时间选择器：

```php
DateTimePicker::make('scheduled_at')
    ->label(__(self::TRANSLATIONS . 'fields.scheduled_at.label'))
    ->native(false) // 使用自定义日期时间选择器
    ->displayFormat('Y-m-d H:i') // 显示格式
    ->seconds(false) // 不显示秒

```

## 五、响应式布局设计

### 5.1 列数设置规范

```php
Section::make(__(self::TRANSLATIONS . 'sections.basic.label'))
    ->schema([
        // 字段定义
    ])
    ->columns([
        'default' => 1,
        'sm' => 2,
        'md' => 3,
        'lg' => 4,
    ])

```

### 5.2 字段宽度比例

```php
TextInput::make('title')
    ->columnSpan([
        'default' => 1,
        'md' => 2,
    ])
```