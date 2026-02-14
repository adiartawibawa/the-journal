<?php

namespace App\Filament\Imports;

use App\Models\Guru;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    // Opsional: Tentukan role default yang akan diberikan
    protected static ?string $defaultRole = 'student';

    // Mapping role dari input ke database
    protected array $roleMapping = [
        'admin' => 'admin',
        'teacher' => 'teacher',
        'student' => 'student',
    ];

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nama')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('email')
                ->label('Email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255']),

            ImportColumn::make('password')
                ->label('Password')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->example('password123 atau biarkan kosong untuk auto-generate'),

            ImportColumn::make('email_verified_at')
                ->label('Email Verified At')
                ->rules(['nullable', 'date']),

        ];
    }

    public function resolveRecord(): User
    {
        return User::withTrashed()->firstOrNew([
            'email' => $this->data['email'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your user import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    /**
     * Dipanggil setelah record berhasil dibuat
     */
    protected function afterCreate(): void
    {
        $this->assignRolesToUser($this->record);

        // Create profile based on roles and data
        $this->createUserProfile($this->record);
    }

    /**
     * Dipanggil setelah record berhasil diupdate
     */
    protected function afterUpdate(): void
    {
        $this->assignRolesToUser($this->record);

        // Create profile based on roles and data
        $this->createUserProfile($this->record);
    }

    /**
     * Create or update user profile based on roles
     */
    protected function createUserProfile(User $user): void
    {
        $this->data['_profile_data'] = $this->extractProfileData($this->data);
        $profileData = $this->data['_profile_data'] ?? [];

        $userRoles = $user->getRoleNames()->toArray();

        // Jika user memiliki role guru
        if (in_array('teacher', $userRoles)) {
            $this->createOrUpdateGuruProfile($user, $profileData);
        }

        // Jika user memiliki role siswa
        if (in_array('student', $userRoles)) {
            $this->createOrUpdateSiswaProfile($user, $profileData);
        }
    }

    /**
     * Extract profile data from import data
     */
    protected function extractProfileData(array $data): array
    {
        $profileKeys = [
            // Student keys
            'nisn',
            'tempat_lahir',
            'tanggal_lahir',
            'nama_ayah',
            'nama_ibu',
            'pekerjaan_orang_tua',
            'alamat_orang_tua',
            'no_telp_orang_tua',
            'is_active',

            // Teacher keys
            'nuptk',
            'status_kepegawaian',
            'bidang_studi',
            'golongan',
            'tanggal_masuk',
            'pendidikan_terakhir'
        ];

        $profileData = [];
        foreach ($profileKeys as $key) {
            if (isset($data[$key]) && !empty($data[$key])) {
                $profileData[$key] = $data[$key];
            }
        }

        // Handle is_active default
        if (!isset($profileData['is_active'])) {
            $profileData['is_active'] = true;
        }

        return $profileData;
    }

    /**
     * Create or update guru profile
     */
    protected function createOrUpdateGuruProfile(User $user, array $data): void
    {
        $guruData = [
            'user_id' => $user->id,
            'nuptk' => $data['nuptk'] ?? null,
            'status_kepegawaian' => $data['status_kepegawaian'] ?? 'Honorer',
            'bidang_studi' => $data['bidang_studi'] ?? null,
            'golongan' => $data['golongan'] ?? null,
            'tanggal_masuk' => isset($data['tanggal_masuk']) ? Carbon::parse($data['tanggal_masuk']) : null,
            'pendidikan_terakhir' => $data['pendidikan_terakhir'] ?? null,
        ];

        // Filter null values
        $guruData = array_filter($guruData, fn($v) => $v !== null);

        // Cek apakah guru sudah punya profile
        $guru = Guru::where('user_id', $user->id)->first();

        if ($guru) {
            // Update existing
            $guru->update($guruData);
        } else {
            // Create new
            Guru::create($guruData);
        }
    }

    /**
     * Create or update siswa profile
     */
    protected function createOrUpdateSiswaProfile(User $user, array $data): void
    {
        $siswaData = [
            'user_id' => $user->id,
            'nisn' => $data['nisn'] ?? null,
            'tempat_lahir' => $data['tempat_lahir'] ?? null,
            'tanggal_lahir' => isset($data['tanggal_lahir']) ? Carbon::parse($data['tanggal_lahir']) : null,
            'nama_ayah' => $data['nama_ayah'] ?? null,
            'nama_ibu' => $data['nama_ibu'] ?? null,
            'pekerjaan_orang_tua' => $data['pekerjaan_orang_tua'] ?? null,
            'alamat_orang_tua' => $data['alamat_orang_tua'] ?? null,
            'no_telp_orang_tua' => $data['no_telp_orang_tua'] ?? null,
            'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ];

        // Filter null values
        $siswaData = array_filter($siswaData, function ($value) {
            return !is_null($value);
        });

        // Cek apakah siswa sudah punya profile
        $siswa = Siswa::where('user_id', $user->id)->first();

        if ($siswa) {
            // Update existing
            $siswa->update($siswaData);
        } else {
            // Create new
            Siswa::create($siswaData);
        }
    }

    /**
     * Assign roles ke user berdasarkan data import
     */
    protected function assignRolesToUser(User $user): void
    {
        $roles = $this->getRolesFromData();

        if (!empty($roles)) {
            $validRoles = array_filter($roles, fn($role) => $this->isValidRole($role));
            $user->syncRoles($validRoles);
        } else {
            $user->syncRoles([static::$defaultRole]);
        }
    }

    /**
     * Extract roles dari data import
     */
    protected function getRolesFromData(): array
    {
        // Jika ada kolom 'roles' di data import
        if (isset($this->data['roles']) && !empty($this->data['roles'])) {
            // Pisahkan dengan koma, lalu trim whitespace
            $roleNames = array_map('trim', explode(',', $this->data['roles']));

            // Map ke role names yang valid
            return array_map(function ($role) {
                return $this->roleMapping[strtolower($role)] ?? $role;
            }, $roleNames);
        }

        return [];
    }

    /**
     * Validasi apakah role exists di database
     */
    protected function isValidRole(string $role): bool
    {
        // Gunakan Spatie Permission untuk cek role
        return Role::where('name', $role)->exists();
    }
}
