# Filament Forms Select 示例

## 下拉联动

```
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

DatePicker::make('native_date')
    ->label(__(self::TRANSLATIONS . 'fields.issue_date.label'))
    ->format('Y-m-d')
    ->seconds(false)
    ->timezone('America/New_York')
    ->required(),


DatePicker::make('not_native_date')
    ->label(__(self::TRANSLATIONS . 'fields.issue_date.label'))
    ->native(false)
    ->displayFormat('Y-m-d')
    ->locale('fr')
    ->hoursStep(2)
    ->minutesStep(15)
    ->secondsStep(10)
    ->minDate(now()->subYears(150))
    ->maxDate(now())
    ->firstDayOfWeek(0) // 0: 星期天
    ->disabledDates(['2000-01-03', '2000-01-15', '2000-01-20'])
    ->closeOnDateSelection()
    ->required(),

TimePicker::make('appointment_at')
    ->datalist([
        '09:00',
        '09:30',
        '10:00',
        '10:30',
        '11:00',
        '11:30',
        '12:00',
    ])
```

