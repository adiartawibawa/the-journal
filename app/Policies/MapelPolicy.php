<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Mapel;
use Illuminate\Auth\Access\HandlesAuthorization;

class MapelPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Mapel');
    }

    public function view(AuthUser $authUser, Mapel $mapel): bool
    {
        return $authUser->can('View:Mapel');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Mapel');
    }

    public function update(AuthUser $authUser, Mapel $mapel): bool
    {
        return $authUser->can('Update:Mapel');
    }

    public function delete(AuthUser $authUser, Mapel $mapel): bool
    {
        return $authUser->can('Delete:Mapel');
    }

    public function restore(AuthUser $authUser, Mapel $mapel): bool
    {
        return $authUser->can('Restore:Mapel');
    }

    public function forceDelete(AuthUser $authUser, Mapel $mapel): bool
    {
        return $authUser->can('ForceDelete:Mapel');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Mapel');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Mapel');
    }

    public function replicate(AuthUser $authUser, Mapel $mapel): bool
    {
        return $authUser->can('Replicate:Mapel');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Mapel');
    }

}