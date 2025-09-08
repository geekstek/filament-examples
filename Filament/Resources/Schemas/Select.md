# Filament Forms Select 示例

## 下拉联动

```
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

Select::make('community_id')
    ->label(__(self::TRANSLATIONS . 'fields.community_name.label'))
    ->relationship('community', 'name') // 定义关联关系
    ->searchable()
    ->preload()
    ->required()
    ->live()
    ->afterStateUpdated(fn (Set $set) => $set('bill_category_id', null)), // 当所选的内容变更时, 清空下方下拉框内容

Select::make('bill_category_id')
    ->label(__(self::TRANSLATIONS . 'fields.bill_category_name.label'))
    ->options(fn (Get $get): array => 
        BillCategory::query()
            ->when(
                $get('community_id'), // 获取上方的下拉框的值, 加到本选项的筛选项
                fn (Builder $query, $communityId) => 
                    $query->where('community_id', $communityId)
            )
            ->pluck('name', 'id')
            ->toArray()
    )
    ->searchable()
    ->preload()
    ->required()
    ->live()
    ->disabled(fn (Get $get): bool => blank($get('community_id'))) // 当上方select 为空时, 本select 禁用
    ->helperText(fn (Get $get): ?string => 
        blank($get('community_id')) // 当上方select 为空时, helperText显示 "请先选择社区"
            ? __('prop-acc::resources/charge-rule.fields.bill_category_name.helper_text') 
            : null
    ),

```

