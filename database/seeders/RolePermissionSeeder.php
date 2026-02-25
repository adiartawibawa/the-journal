<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cache Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftar Resource sesuai gambar
        $resources = [
            'GuruMengajar',
            'Guru',
            'Jurnal',
            'Kelas',
            'Mapel',
            'KelasSiswa',
            'Siswa',
            'TahunAjaran',
            'User',
            'Role'
        ];

        // Daftar Action (Format PascalCase sesuai preferensi Anda)
        $actions = [
            'ViewAny',
            'View',
            'Create',
            'Update',
            'Delete',
            'Restore',
            'ForceDelete',
            'ForceDeleteAny',
            'RestoreAny',
            'Replicate',
            'Reorder'
        ];

        // Generate Permissions dengan format 'Action:Resource'
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action}:{$resource}"]);
            }
        }

        // Konfigurasi Role dan Penugasan Permission

        // --- ROLE: SUPER ADMIN ---
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // --- ROLE: ADMIN ---
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            // Akses Kontrol User & Role (Terbatas)
            'ViewAny:User',
            'View:User',
            'ViewAny:Role',
            'View:Role',
            // Manajemen Data Master (Penuh)
            'ViewAny:Guru',
            'View:Guru',
            'Create:Guru',
            'Update:Guru',
            'Delete:Guru',
            'ViewAny:Siswa',
            'View:Siswa',
            'Create:Siswa',
            'Update:Siswa',
            'Delete:Siswa',
            'ViewAny:Kelas',
            'View:Kelas',
            'Create:Kelas',
            'Update:Kelas',
            'ViewAny:Mapel',
            'View:Mapel',
            'Create:Mapel',
            'Update:Mapel',
            'ViewAny:TahunAjaran',
            'View:TahunAjaran',
            'Update:TahunAjaran',
            'ViewAny:KelasSiswa',
            'Create:KelasSiswa',
            'ViewAny:GuruMengajar',
        ]);

        // --- ROLE: TEACHER ---
        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $teacher->givePermissionTo([
            // Operasional Jurnal
            'ViewAny:Jurnal',
            'View:Jurnal',
            'Create:Jurnal',
            'Update:Jurnal',
            'Replicate:Jurnal',
            // Akses Referensi (Read-Only)
            'ViewAny:Guru',
            'View:Guru',
            'ViewAny:Siswa',
            'View:Siswa',
            'ViewAny:Kelas',
            'View:Kelas',
            'ViewAny:Mapel',
            'View:Mapel',
            'ViewAny:TahunAjaran',
            'View:TahunAjaran',
        ]);

        // --- ROLE: STUDENT ---
        $student = Role::firstOrCreate(['name' => 'student']);
        $student->givePermissionTo([
            'ViewAny:Jurnal',
            'View:Jurnal',
            'ViewAny:Mapel',
            'View:Mapel',
            'View:Siswa', // Untuk melihat profil pribadi
            'View:Kelas', // Untuk melihat detail kelasnya
        ]);
    }
}
