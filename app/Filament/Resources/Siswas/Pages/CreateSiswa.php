<?php

namespace App\Filament\Resources\Siswas\Pages;

use App\Filament\Resources\Siswas\SiswaResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class CreateSiswa extends CreateRecord
{
    protected static string $resource = SiswaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $data['user_id'] = $user->id;

        return static::getModel()::create($data);
    }
}
