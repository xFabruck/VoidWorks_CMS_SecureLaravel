<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserManager
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function paginateAdmin(): LengthAwarePaginator
    {
        return User::query()->orderBy('name')->paginate(15);
    }

    /** @param array{name:string,email:string,password:string,role:string,status:string} $data */
    public function create(array $data): User
    {
        $createdUser = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'status' => $data['status'],
        ]);

        $this->audit->record('user_created', $createdUser, ['role' => $createdUser->role]);

        return $createdUser;
    }

    /** @param array{name:string,email:string,password?:string|null,role:string,status:string} $data */
    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $lockedUsers = User::query()->orderBy('id')->lockForUpdate()->get();
            $lockedUser = $lockedUsers->firstWhere('id', $user->getKey());

            abort_unless($lockedUser, 404);
            $this->ensureSuperAdminInvariant($lockedUser, $data['role'], $data['status'], $lockedUsers);
            $previousRole = $lockedUser->role;

            $attributes = [
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'status' => $data['status'],
            ];

            if (filled($data['password'] ?? null)) {
                $attributes['password'] = Hash::make($data['password']);
            }

            $lockedUser->name = $attributes['name'];
            $lockedUser->email = $attributes['email'];
            $lockedUser->role = $attributes['role'];
            $lockedUser->status = $attributes['status'];

            if (array_key_exists('password', $attributes)) {
                $lockedUser->password = $attributes['password'];
            }

            $lockedUser->save();

            $this->audit->record('user_updated', $lockedUser);

            if ($previousRole !== $lockedUser->role) {
                $this->audit->record('role_changed', $lockedUser, [
                    'from' => $previousRole,
                    'to' => $lockedUser->role,
                ]);
            }

            return $lockedUser->refresh();
        });
    }

    public function setStatus(User $user, string $status): User
    {
        return DB::transaction(function () use ($user, $status): User {
            $lockedUsers = User::query()->orderBy('id')->lockForUpdate()->get();
            $lockedUser = $lockedUsers->firstWhere('id', $user->getKey());

            abort_unless($lockedUser, 404);
            $this->ensureSuperAdminInvariant($lockedUser, $lockedUser->role, $status, $lockedUsers);
            $lockedUser->status = $status;
            $lockedUser->save();
            $this->audit->record('user_updated', $lockedUser);

            return $lockedUser->refresh();
        });
    }

    /** @param Collection<int, User> $lockedUsers */
    private function ensureSuperAdminInvariant(User $user, string $newRole, string $newStatus, Collection $lockedUsers): void
    {
        if ($user->role !== 'super_admin' || $user->status !== 'active') {
            return;
        }

        if ($newRole === 'super_admin' && $newStatus === 'active') {
            return;
        }

        $activeSuperAdmins = $lockedUsers->where('role', 'super_admin')->where('status', 'active')->count();

        if ($activeSuperAdmins <= 1) {
            throw ValidationException::withMessages([
                'status' => 'No puedes cambiar el último Superadministrador activo.',
            ]);
        }
    }
}
