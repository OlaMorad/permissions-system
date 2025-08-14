<?php

namespace App\Services;

use App\Models\Manager;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ManagerService
{

    public function show_my_employees()
    {
        $user = Auth::user();
$manager = Manager::where('user_id',$user->id)->first();
        $employeesId = Employee::where('manager_id', $manager->id)->pluck('user_id');
        return $employees = DB::table('users')->whereIn('id', $employeesId)->select('name', 'email')->get();
    }

    
    public function edit_manager_information($data){
        $manager = $this->getManager($data->manager_id);
        $user = $manager->user;
        $this->updateBasicInformation($user, $data);
        $this->updatePassword($user, $data);
        $this->updateAvatar($user, $data);
        $user->save();

        return $this->formatUserResponse($user);
    }

        private function getManager(int $id): Manager
    {
        return Manager::findOrFail($id);
    }
        private function updateBasicInformation($user,  $data): void
    {
        $user->name    = $data['name'] ?? $user->name;
        $user->email   = $data['email'] ?? $user->email;
        $user->address = $data['address'] ?? $user->address;
        $user->phone   = $data['phone'] ?? $user->phone;
    }

        private function updatePassword($user,  $data): void
    {
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
    }
    private function updateAvatar($user,  $data): void
    {
        if (!empty($data['avatar'])) {
            if ($user->avatar) {
                //حذف الصورة القديمة
                Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = $data['avatar']->store('avatars', 'public');
        }
    }

    private function formatUserResponse($user): array
    {
        return [
            'id'      => $user->id,
            'name'    => $user->name,
            'email'   => $user->email,
            'address' => $user->address,
            'phone'   => $user->phone,
            'avatar'  => $user->avatar ? asset('storage/' . $user->avatar) : null,
        ];
    }
}
