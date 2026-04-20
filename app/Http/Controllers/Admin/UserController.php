<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()
            ->withCount('systemLinks')
            ->latest();

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            match ($status) {
                'active' => $query->where('is_active', true),
                'inactive' => $query->where('is_active', false),
                default => null,
            };
        }

        return view('admin.users.index', [
            'users' => $query->paginate(15)->withQueryString(),
            'search' => $search,
            'status' => $status,
            'stats' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User(['is_active' => true]),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->validateData($request);

        User::create([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء المستخدم بنجاح.',
                'redirect' => route('admin.users.index'),
            ]);
        }

        return redirect()->route('admin.users.index')->with('status', 'تم إنشاء المستخدم.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $data = $this->validateData($request, $user->id);

        $updates = [
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];

        if (! empty($data['password'])) {
            $updates['password'] = Hash::make($data['password']);
        }

        $user->update($updates);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حفظ التعديلات.',
                'redirect' => route('admin.users.index'),
            ]);
        }

        return redirect()->route('admin.users.index')->with('status', 'تم الحفظ.');
    }

    public function destroy(Request $request, User $user): JsonResponse|RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك حذف حسابك.'], 422);
        }

        $user->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'تم حذف المستخدم.']);
        }

        return back()->with('status', 'تم الحذف.');
    }

    public function toggleActive(Request $request, User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك تعطيل حسابك.'], 422);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'تم تفعيل المستخدم.' : 'تم إيقاف المستخدم.',
            'is_active' => $user->is_active,
        ]);
    }

    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $newPassword = Str::password(14, letters: true, numbers: true, symbols: false);
        $user->password = Hash::make($newPassword);
        $user->save();

        AuditLog::create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_PASSWORD_CHANGED,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => ['reset_by_admin' => auth()->user()->email],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إعادة تعيين كلمة المرور.',
            'password' => $newPassword,
        ]);
    }

    private function validateData(Request $request, ?string $ignoreId = null): array
    {
        $rules = [
            'full_name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignoreId)->whereNull('deleted_at')],
            'phone' => ['nullable', 'string', 'max:32'],
            'is_active' => ['nullable', 'boolean'],
            'password' => [$ignoreId ? 'nullable' : 'required', 'nullable', 'string', 'min:8', 'confirmed'],
        ];

        return $request->validate($rules, [], [
            'full_name' => 'الاسم الكامل',
            'email' => 'البريد الإلكتروني',
            'phone' => 'الهاتف',
            'is_active' => 'الحالة',
            'password' => 'كلمة المرور',
        ]);
    }
}
