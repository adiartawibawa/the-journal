<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Jurnal;
use Illuminate\Auth\Access\HandlesAuthorization;

class JurnalPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Jurnal');
    }

    public function view(AuthUser $authUser, Jurnal $jurnal): bool
    {
        return $authUser->can('View:Jurnal');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Jurnal');
    }

    public function update(AuthUser $authUser, Jurnal $jurnal): bool
    {
        return $authUser->can('Update:Jurnal');
    }

    public function delete(AuthUser $authUser, Jurnal $jurnal): bool
    {
        return $authUser->can('Delete:Jurnal');
    }

    public function restore(AuthUser $authUser, Jurnal $jurnal): bool
    {
        return $authUser->can('Restore:Jurnal');
    }

    public function forceDelete(AuthUser $authUser, Jurnal $jurnal): bool
    {
        return $authUser->can('ForceDelete:Jurnal');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Jurnal');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Jurnal');
    }

    public function replicate(AuthUser $authUser, Jurnal $jurnal): bool
    {
        return $authUser->can('Replicate:Jurnal');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Jurnal');
    }

}