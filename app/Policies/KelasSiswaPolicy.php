<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KelasSiswa;
use Illuminate\Auth\Access\HandlesAuthorization;

class KelasSiswaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KelasSiswa');
    }

    public function view(AuthUser $authUser, KelasSiswa $kelasSiswa): bool
    {
        return $authUser->can('View:KelasSiswa');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KelasSiswa');
    }

    public function update(AuthUser $authUser, KelasSiswa $kelasSiswa): bool
    {
        return $authUser->can('Update:KelasSiswa');
    }

    public function delete(AuthUser $authUser, KelasSiswa $kelasSiswa): bool
    {
        return $authUser->can('Delete:KelasSiswa');
    }

    public function restore(AuthUser $authUser, KelasSiswa $kelasSiswa): bool
    {
        return $authUser->can('Restore:KelasSiswa');
    }

    public function forceDelete(AuthUser $authUser, KelasSiswa $kelasSiswa): bool
    {
        return $authUser->can('ForceDelete:KelasSiswa');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KelasSiswa');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KelasSiswa');
    }

    public function replicate(AuthUser $authUser, KelasSiswa $kelasSiswa): bool
    {
        return $authUser->can('Replicate:KelasSiswa');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KelasSiswa');
    }

}