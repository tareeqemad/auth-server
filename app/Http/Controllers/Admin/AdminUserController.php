<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $admins = User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', User::ADMIN_ROLES))
            ->with('roles:id,name')
            ->latest()
            ->paginate(20);

        $roles = Role::whereIn('name', User::ADMIN_ROLES)
            ->withCount('users')
            ->get();

        return view('admin.admins.index', [
            'admins' => $admins,
            'roles' => $roles,
        ]);
    }

    public function assignRole(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'role' => ['required', 'string', 'in:'.implode(',', User::ADMIN_ROLES)],
        ]);

        $user->syncRoles([$data['role']]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث صلاحية المدير.',
        ]);
    }

    public function removeRole(Request $request, User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك إزالة صلاحيات حسابك.'], 422);
        }

        $user->syncRoles([]);

        return response()->json([
            'success' => true,
            'message' => 'تم إزالة صلاحيات المدير.',
        ]);
    }
}
