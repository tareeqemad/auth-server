<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Rules\PalestinianNationalId;
use App\Services\AccountLockoutService;
use App\Services\PasswordHistoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = User::query()
            ->withCount('systemLinks')
            ->latest();

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('national_id', 'like', "%{$search}%")
                    ->orWhere('employee_number', $search);
            });
        }

        if ($status = $request->query('status')) {
            match ($status) {
                'active' => $query->where('is_active', true),
                'inactive' => $query->where('is_active', false),
                'locked' => $query->whereNotNull('locked_until')->where('locked_until', '>', now()),
                default => null,
            };
        }

        $users = $query->paginate(200)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.users._rows', ['users' => $users])->render(),
                'has_more' => $users->hasMorePages(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
                'loaded' => ($users->currentPage() - 1) * $users->perPage() + $users->count(),
            ]);
        }

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'status' => $status,
            'stats' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
                'locked' => User::whereNotNull('locked_until')->where('locked_until', '>', now())->count(),
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
            'national_id' => $data['national_id'] ?? null,
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

    public function show(User $user): View
    {
        $user->load(['systemLinks', 'roles']);

        $recentAudit = \App\Models\AuditLog::where('user_id', $user->id)
            ->latest('created_at')
            ->limit(15)
            ->get();

        $sessionsActive = \App\Models\SsoSession::where('user_id', $user->id)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->count();

        return view('admin.users.show', [
            'user' => $user,
            'recentAudit' => $recentAudit,
            'sessionsActive' => $sessionsActive,
        ]);
    }

    public function update(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $data = $this->validateData($request, $user->id);

        $updates = [
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'national_id' => $data['national_id'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];

        $previousHash = null;
        if (! empty($data['password'])) {
            $previousHash = $user->password;
            $updates['password'] = Hash::make($data['password']);
        }

        $user->update($updates);

        if ($previousHash !== null) {
            app(PasswordHistoryService::class)->record($user, $previousHash);
        }

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

    public function unlock(Request $request, User $user, AccountLockoutService $lockout): JsonResponse
    {
        if (! $user->isLocked() && $user->failed_login_attempts === 0) {
            return response()->json([
                'success' => false,
                'message' => 'الحساب غير محظور أصلاً.',
            ], 422);
        }

        $lockout->unlockByAdmin($user, $request->user(), $request);

        return response()->json([
            'success' => true,
            'message' => 'تم فك الحظر عن المستخدم.',
        ]);
    }

    public function lock(Request $request, User $user, AccountLockoutService $lockout): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك حظر حسابك.'], 422);
        }

        $data = $request->validate([
            'minutes' => ['nullable', 'integer', 'min:1', 'max:525600'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $lockout->lockByAdmin(
            $user,
            $request->user(),
            $data['minutes'] ?? null,
            $data['reason'] ?? null,
            $request,
        );

        return response()->json([
            'success' => true,
            'message' => $data['minutes']
                ? "تم حظر المستخدم لمدة {$data['minutes']} دقيقة."
                : 'تم حظر المستخدم بشكل دائم (حتى يتم فك الحظر يدوياً).',
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'users-'.now()->format('Y-m-d-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store, no-cache',
        ];

        return response()->streamDownload(function () use ($request) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'id', 'email', 'full_name', 'phone', 'national_id', 'employee_number',
                'source', 'needs_id_linking', 'job_title', 'department', 'governorate',
                'is_active', 'locked_until', 'failed_login_attempts', 'sms_2fa_enabled',
                'last_login_at', 'last_login_ip', 'created_at', 'systems_count',
            ]);

            $query = User::query()->withCount('systemLinks');

            if ($search = $request->query('q')) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('national_id', 'like', "%{$search}%");
                });
            }

            if ($status = $request->query('status')) {
                match ($status) {
                    'active' => $query->where('is_active', true),
                    'inactive' => $query->where('is_active', false),
                    'locked' => $query->whereNotNull('locked_until')->where('locked_until', '>', now()),
                    default => null,
                };
            }

            $query->orderBy('created_at')->chunk(500, function ($users) use ($out) {
                foreach ($users as $u) {
                    fputcsv($out, [
                        $u->id, $u->email, $u->full_name, $u->phone, $u->national_id,
                        $u->employee_number, $u->source, $u->needs_id_linking ? '1' : '0',
                        $u->job_title, $u->department, $u->governorate,
                        $u->is_active ? '1' : '0',
                        $u->locked_until?->toIso8601String() ?? '',
                        $u->failed_login_attempts, $u->sms_2fa_enabled ? '1' : '0',
                        $u->last_login_at?->toIso8601String() ?? '',
                        $u->last_login_ip,
                        $u->created_at?->toIso8601String() ?? '',
                        $u->system_links_count,
                    ]);
                }
            });

            fclose($out);
        }, $filename, $headers);
    }

    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $newPassword = Str::password(14, letters: true, numbers: true, symbols: false);
        $previousHash = $user->password;
        $user->password = Hash::make($newPassword);
        $user->save();

        app(PasswordHistoryService::class)->record($user, $previousHash);

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
            'national_id' => [
                'nullable', 'string', 'size:9',
                Rule::unique('users', 'national_id')->ignore($ignoreId)->whereNull('deleted_at'),
                new PalestinianNationalId(),
            ],
            'is_active' => ['nullable', 'boolean'],
            'password' => [$ignoreId ? 'nullable' : 'required', 'nullable', 'string', 'min:8', 'confirmed'],
        ];

        return $request->validate($rules, [], [
            'full_name' => 'الاسم الكامل',
            'national_id' => 'رقم الهوية',
            'email' => 'البريد الإلكتروني',
            'phone' => 'الهاتف',
            'is_active' => 'الحالة',
            'password' => 'كلمة المرور',
        ]);
    }
}
