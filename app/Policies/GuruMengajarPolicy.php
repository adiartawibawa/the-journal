<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\GuruMengajar;
use Illuminate\Auth\Access\HandlesAuthorization;

class GuruMengajarPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GuruMengajar');
    }

    public function view(AuthUser $authUser, GuruMengajar $guruMengajar): bool
    {
        return $authUser->can('View:GuruMengajar');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GuruMengajar');
    }

    public function update(AuthUser $authUser, GuruMengajar $guruMengajar): bool
    {
        return $authUser->can('Update:GuruMengajar');
    }

    public function delete(AuthUser $authUser, GuruMengajar $guruMengajar): bool
    {
        return $authUser->can('Delete:GuruMengajar');
    }

    public function restore(AuthUser $authUser, GuruMengajar $guruMengajar): bool
    {
        return $authUser->can('Restore:GuruMengajar');
    }

    public function forceDelete(AuthUser $authUser, GuruMengajar $guruMengajar): bool
    {
        return $authUser->can('ForceDelete:GuruMengajar');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GuruMengajar');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GuruMengajar');
    }

    public function replicate(AuthUser $authUser, GuruMengajar $guruMengajar): bool
    {
        return $authUser->can('Replicate:GuruMengajar');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GuruMengajar');
    }

}