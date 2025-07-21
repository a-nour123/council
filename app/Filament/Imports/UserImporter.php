<?php

namespace App\Filament\Imports;

use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nickname')
                ->rules(['max:255']),
            // ImportColumn::make('name')
            //     ->rules(['max:255']),
            ImportColumn::make('ar_name')
                ->rules(['max:255']),
            ImportColumn::make('en_name')
                ->rules(['max:255']),
            ImportColumn::make('phone')
                ->rules(['max:255']),
            ImportColumn::make('username')
                ->rules(['max:255']),
            ImportColumn::make('email')
                ->rules(['email', 'max:255']),
            // ImportColumn::make('faculty')
            //     ->relationship(),
            // ImportColumn::make('position')
            //     ->relationship(),
            // ImportColumn::make('headquarter')
            //     ->relationship(),
            // ImportColumn::make('email_verified_at')
            //     ->rules(['email', 'datetime']),
            ImportColumn::make('password')
                ->requiredMapping()
                // ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                ->rules(['required', 'max:255']),

        ];
    }

    protected function beforeSave(): void
    {
        // Hash the password before saving the record to the database
        if ($this->record->password) {
            $this->record->password = Hash::make($this->record->password);
        }
    }

    public function resolveRecord(): ?User
    {
        // return User::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new User();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your user import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
