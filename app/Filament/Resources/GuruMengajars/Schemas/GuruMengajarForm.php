<?php

namespace App\Filament\Resources\GuruMengajars\Schemas;

use App\Models\TahunAjaran;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class GuruMengajarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Penugasan')
                    ->description('Tentukan parameter penugasan guru pada kelas dan mata pelajaran tertentu.')
                    ->schema([
                        Select::make('tahun_ajaran_id')
                            ->relationship('tahunAjaran', 'nama')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->nama_semester)
                            ->label('Tahun Ajaran')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn() => TahunAjaran::getActive()->id),
                        Select::make('guru_id')
                            ->relationship('guru', 'id', fn(Builder $query) => $query->with('user'))
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->user->name)
                            ->label('Nama Guru')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('kelas_id')
                            ->relationship('kelas', 'nama')
                            ->label('Kelas')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('mapel_id')
                            ->relationship('mapel', 'nama')
                            ->label('Mata Pelajaran')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Parameter Akademik')
                    ->schema([
                        TextInput::make('kkm')
                            ->label('KKM')
                            ->numeric()
                            ->default(75)
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        TextInput::make('jam_per_minggu')
                            ->label('Beban Jam (per Minggu)')
                            ->numeric()
                            ->default(2)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }
}
