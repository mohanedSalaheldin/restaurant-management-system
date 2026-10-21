<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null): User
    {
        if ($avatar) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $avatar->store('avatars', 'public');
        }

        $user->update([
            'name'   => $data['name'],
            'phone'  => $data['phone'] ?? null,
            'avatar' => $data['avatar'] ?? $user->avatar,
        ]);

        return $user;
    }

    /**
     * Assumes the current password has already been verified.
     */
    public function updatePassword(User $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}