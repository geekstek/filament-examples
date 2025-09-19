# Filament Import 示例

## Import Action 

```
use Filament\Actions\ActionGroup;
use Geekstek\Support\Filament\Actions\SmartImportAction;
use Filament\Support\Icons\Heroicon;
use Filament\Facades\Filament;
use Geekstek\Support\Jobs\TransactionalImportCsv;

ActionGroup::make([
    SmartImportAction::make()
        ->label('导入')
        ->icon(Heroicon::ArrowDownTray)
        ->importer(EmployeeHobbyImporter::class)
        ->options(['company_id' => Filament::getTenant()->id])
        ->job(TransactionalImportCsv::class)
        ->templateFilePath('imports/员工爱好导入模板.xlsx')
        ->enableExcelToCsv(true)
        ->chunkSize(1000),
])
    ->label('更多')
    ->color('gray')
    ->button(),
```


## ImportColumn

#### 方法执行顺序

castStateUsing() > rules() > fillRecordUsing()


```
use Filament\Actions\Imports\ImportColumn;

ImportColumn::make('name')
    ->requiredMapping()
    ->rules(['required', 'max:255'])
    ->castStateUsing(function (string $state): ?float {
        if (blank($state)) {
            return null;
        }
        
        $state = preg_replace('/[^0-9.]/', '', $state);
        $state = floatval($state);
    
        return round($state, precision: 2);
    }),

```