<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    /**
     * تحديث بيانات الملف الشخصي ورفع الصورة
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null): User
    {
        if ($avatar) {
            // حذف الصورة القديمة إن وُجدت
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // رفع الصورة الجديدة في storage/app/public/avatars
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
     * تحديث كلمة المرور بعد مطابقة القديمة
     */
    public function updatePassword(User $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}