<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class StaffUserController extends Controller
{
    protected function getPublicRoot(): string
    {
        return isset($_SERVER['DOCUMENT_ROOT']) && is_dir($_SERVER['DOCUMENT_ROOT']) 
            ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') 
            : public_path();
    }

    protected function saveUserFile($file, $subFolder): string
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $relativeDir = trim($subFolder, '/');

        $baseDir = $this->getPublicRoot() . '/uploads/' . $relativeDir;

        if (!file_exists($baseDir)) {
            @mkdir($baseDir, 0777, true);
        }

        $file->move($baseDir, $filename);

        return 'uploads/' . $relativeDir . '/' . $filename;
    }

    protected function deleteUserFile($relativePath): void
    {
        if (empty($relativePath)) return;

        $clean = ltrim($relativePath, '/');
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, 8);
        }

        $publicRoot = $this->getPublicRoot();
        $file1 = $publicRoot . '/' . $clean;
        $file2 = $publicRoot . '/storage/' . $clean;

        if (file_exists($file1)) @unlink($file1);
        if (file_exists($file2)) @unlink($file2);
    }

    protected function isLastSuperAdmin($userId): bool
    {
        $superCount = DB::table('users')
            ->where(function ($q) {
                $q->where('is_super_admin', true)
                  ->orWhere('role', 'super_admin');
            })
            ->whereNull('deleted_at')
            ->count();

        if ($superCount <= 1) {
            return DB::table('users')
                ->where('id', $userId)
                ->where(function ($q) {
                    $q->where('is_super_admin', true)
                      ->orWhere('role', 'super_admin');
                })
                ->exists();
        }

        return false;
    }

    /**
     * Helper: Resolve dynamic pagination limit (20, 30, 50, 100, 200, 500, all)
     */
    protected function resolvePaginationLimit(Request $request, int $default = 20): int
    {
        $perPage = $request->input('per_page', $default);
        if ($perPage === 'all' || (int)$perPage >= 999999) {
            return 999999;
        }
        $perPage = (int)$perPage;
        return in_array($perPage, [10, 20, 30, 50, 100, 200, 500]) ? $perPage : $default;
    }

    /**
     * 1. All Users & Staff Directory (Filters, Search, Per Page, Excel/PDF Export & AJAX)
     */
    public function index(Request $request)
    {
        $query = DB::table('users')
            ->leftJoin('users as creator', 'users.created_by', '=', 'creator.id')
            ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
            ->whereNull('users.deleted_at')
            ->select(
                'users.*', 
                'creator.name as creator_name',
                'branches.name as branch_name'
            );

        // 1. Role Filter
        if ($request->filled('role') && $request->role !== 'all') {
            $r = $request->role;
            $query->where(function ($q) use ($r) {
                $q->where('users.role', $r)
                  ->orWhere('users.role', str_replace('_', ' ', $r))
                  ->orWhere('users.role', str_replace('_', '-', $r));
            });
        }

        // 2. User Type Filter
        if ($request->filled('user_type') && $request->user_type !== 'all') {
            $ut = strtolower($request->user_type);
            if ($ut === 'customer') {
                $query->where('users.role', 'customer');
            } elseif ($ut === 'staff') {
                $query->where('users.role', '!=', 'customer');
            }
        }

        // 3. Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $st = strtolower($request->status);
            if (Schema::hasColumn('users', 'status')) {
                $query->where('users.status', $st);
            } elseif ($st === 'active') {
                $query->where('users.is_active', true);
            } elseif ($st === 'inactive') {
                $query->where('users.is_active', false);
            } elseif (in_array($st, ['suspended', 'frozen', 'blocked'])) {
                $query->where('users.is_locked', true);
            }
        }

        // 4. Branch Filter
        if ($request->filled('branch_id') && $request->branch_id !== 'all') {
            if ($request->branch_id === 'hq') {
                $query->whereNull('users.branch_id');
            } else {
                $query->where('users.branch_id', $request->branch_id);
            }
        }

        // 5. Search Filter
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('users.name', 'like', "%{$s}%")
                  ->orWhere('users.email', 'like', "%{$s}%")
                  ->orWhere('users.phone', 'like', "%{$s}%")
                  ->orWhere('users.designation', 'like', "%{$s}%");
            });
        }

        // 6. Created Date (Exact)
        if ($request->filled('created_date')) {
            $query->whereDate('users.created_at', $request->created_date);
        }

        // 7. Created Date Range
        if ($request->filled('created_from') && $request->filled('created_to')) {
            $query->whereBetween(DB::raw('DATE(users.created_at)'), [$request->created_from, $request->created_to]);
        } elseif ($request->filled('created_from')) {
            $query->whereDate('users.created_at', '>=', $request->created_from);
        } elseif ($request->filled('created_to')) {
            $query->whereDate('users.created_at', '<=', $request->created_to);
        }

        // 8. Inactivity Filter
        if ($request->filled('inactivity') && $request->inactivity !== 'all') {
            $inact = $request->inactivity;
            if ($inact === 'never_logged_in') {
                $query->whereNull('users.last_login_at');
            } elseif ($inact === 'inactive_30') {
                $query->where(function ($q) {
                    $q->where('users.last_login_at', '<=', now()->subDays(30))
                      ->orWhere(function ($q2) {
                          $q2->whereNull('users.last_login_at')->where('users.created_at', '<=', now()->subDays(30));
                      });
                });
            } elseif ($inact === 'inactive_60') {
                $query->where(function ($q) {
                    $q->where('users.last_login_at', '<=', now()->subDays(60))
                      ->orWhere(function ($q2) {
                          $q2->whereNull('users.last_login_at')->where('users.created_at', '<=', now()->subDays(60));
                      });
                });
            } elseif ($inact === 'inactive_90') {
                $query->where(function ($q) {
                    $q->where('users.last_login_at', '<=', now()->subDays(90))
                      ->orWhere(function ($q2) {
                          $q2->whereNull('users.last_login_at')->where('users.created_at', '<=', now()->subDays(90));
                      });
                });
            } elseif ($inact === 'before_date' && $request->filled('inactive_before_date')) {
                $targetDate = $request->inactive_before_date;
                $query->where(function ($q) use ($targetDate) {
                    $q->whereDate('users.last_login_at', '<=', $targetDate)
                      ->orWhere(function ($q2) use ($targetDate) {
                          $q2->whereNull('users.last_login_at')->whereDate('users.created_at', '<=', $targetDate);
                      });
                });
            }
        }

        // A. Export to Excel / CSV (ERR_INVALID_RESPONSE Fixed)
        if ($request->input('export') === 'excel' || $request->input('export') === 'csv') {
            $exportRecords = (clone $query)->latest('users.id')->get();
            return $this->streamUsersCsvExport($exportRecords, 'Staff_and_User_Directory_', false);
        }

        // B. Pure Text Data Endpoint for Clean PDF Table Generation
        if ($request->input('export') === 'pdf_data') {
            $pdfRecords = (clone $query)->latest('users.id')->get()->map(function ($u, $idx) {
                $statusStr = !empty($u->is_locked) ? 'Locked' : ($u->status ?? ($u->is_active ? 'Active' : 'Inactive'));
                return [
                    'sl'          => $idx + 1,
                    'id'          => $u->id,
                    'name'        => $u->name ?: 'N/A',
                    'designation' => $u->designation ?: ($u->role === 'customer' ? 'Customer' : 'Staff'),
                    'role'        => ucwords(str_replace(['_', '-'], ' ', $u->role ?: 'Staff')),
                    'branch'      => $u->branch_name ?: 'Head Office / Online',
                    'creator'     => $u->creator_name ? 'By: ' . $u->creator_name : 'Self-Registered',
                    'phone'       => $u->phone ?: 'N/A',
                    'email'       => $u->email ?: 'N/A',
                    'status'      => ucfirst($statusStr),
                    'created_at'  => date('d M Y', strtotime($u->created_at)),
                    'last_login'  => $u->last_login_at ? date('d M Y, h:i A', strtotime($u->last_login_at)) : 'Never Logged In',
                ];
            });

            return response()->json([
                'success'      => true,
                'title'        => config('app.name', 'Shop Now BD') . ' - Staff & User Directory Report',
                'generated_at' => date('d M Y, h:i A'),
                'total'        => $pdfRecords->count(),
                'records'      => $pdfRecords
            ]);
        }

        // Pagination with per-page selector
        $limit = $this->resolvePaginationLimit($request, 20);
        $users = $query->latest('users.id')->paginate($limit)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'html'       => view('admin.users.partials.user_rows', compact('users'))->render(),
                'pagination' => $users->hasPages() ? $users->links('pagination::bootstrap-5')->render() : '',
                'total'      => $users->total(),
                'firstItem'  => $users->firstItem() ?? 0,
                'lastItem'   => $users->lastItem() ?? 0,
                'count'      => $users->count(),
            ]);
        }

        $defaultRoles = collect(['super_admin', 'admin', 'branch_manager', 'staff', 'customer']);
        $dbRoles = collect();
        if (Schema::hasTable('role_route_permissions')) {
            $dbRoles = DB::table('role_route_permissions')->distinct()->pluck('role_name');
        }
        $allRoles = $defaultRoles->merge($dbRoles)
            ->map(fn($r) => str_replace(['-', ' '], '_', strtolower(trim((string)$r))))
            ->unique()
            ->values();

        $branches = Schema::hasTable('branches') ? DB::table('branches')->get() : collect();

        return view('admin.users.index', compact('users', 'allRoles', 'branches'));
    }

    /**
     * Clean Memory CSV/Excel Export Helper (Prevents ERR_INVALID_RESPONSE & Organizes Columns)
     */
    protected function streamUsersCsvExport($records, string $filePrefix, bool $isTrash = false)
    {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        $filename = $filePrefix . date('Y_m_d_His') . ".csv";
        $handle = fopen('php://temp', 'r+');

        // UTF-8 BOM so Microsoft Excel opens Bengali & symbols cleanly
        fwrite($handle, "\xEF\xBB\xBF");

        if ($isTrash) {
            fputcsv($handle, [
                'SL',
                'User ID',
                'Full Name',
                'Role',
                'Phone Number',
                'Email Address',
                'Branch Hub',
                'Deleted Date & Time'
            ]);

            foreach ($records as $index => $u) {
                $phoneStr = !empty($u->phone) ? '="' . $u->phone . '"' : 'N/A';
                fputcsv($handle, [
                    $index + 1,
                    '#' . $u->id,
                    $u->name ?: 'N/A',
                    ucwords(str_replace(['_', '-'], ' ', $u->role ?: 'Staff')),
                    $phoneStr,
                    $u->email ?: 'N/A',
                    $u->branch_name ?: 'Head Office / Online',
                    $u->deleted_at ? date('d M Y, h:i A', strtotime($u->deleted_at)) : 'N/A'
                ]);
            }
        } else {
            // Organized Column Headers for Active/All Users
            fputcsv($handle, [
                'SL',
                'User ID',
                'Full Name',
                'Role',
                'Designation',
                'Phone Number',
                'Email Address',
                'Account Status',
                'Branch / Hub',
                'Registration Source',
                'Monthly Salary (BDT)',
                'Created Date',
                'Last Login Activity'
            ]);

            foreach ($records as $index => $u) {
                $statusStr = !empty($u->is_locked) ? 'Locked' : ($u->status ?? ($u->is_active ? 'Active' : 'Inactive'));
                // Excel formula string wrapper so leading 0 in phone number is never stripped
                $phoneStr = !empty($u->phone) ? '="' . $u->phone . '"' : 'N/A';

                fputcsv($handle, [
                    $index + 1,
                    '#' . $u->id,
                    $u->name ?: 'N/A',
                    ucwords(str_replace(['_', '-'], ' ', $u->role ?: 'Staff')),
                    $u->designation ?: ($u->role === 'customer' ? 'Registered Customer' : 'Staff Member'),
                    $phoneStr,
                    $u->email ?: 'N/A',
                    ucfirst($statusStr),
                    $u->branch_name ?: 'Head Office / Online',
                    !empty($u->creator_name) ? 'By: ' . $u->creator_name : 'Self-Registered',
                    number_format((float)($u->monthly_salary ?? 0), 2, '.', ''),
                    date('d M Y', strtotime($u->created_at)),
                    !empty($u->last_login_at) ? date('d M Y, h:i A', strtotime($u->last_login_at)) : 'Never Logged In'
                ]);
            }
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return response($csvContent, 200, [
            'Content-Type'           => 'text/csv; charset=UTF-8',
            'Content-Disposition'    => 'attachment; filename="' . $filename . '"',
            'Content-Length'         => strlen($csvContent),
            'Cache-Control'          => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'                 => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * 2. Trashed Users & Staff with Filters, Search, Per Page, Excel/PDF Export & AJAX
     */
    public function trash(Request $request)
    {
        $query = DB::table('users')
            ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
            ->whereNotNull('users.deleted_at')
            ->select('users.*', 'branches.name as branch_name');

        if ($request->filled('role') && $request->role !== 'all') {
            $r = $request->role;
            $query->where(function ($q) use ($r) {
                $q->where('users.role', $r)
                  ->orWhere('users.role', str_replace('_', ' ', $r))
                  ->orWhere('users.role', str_replace('_', '-', $r));
            });
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('users.name', 'like', "%{$s}%")
                  ->orWhere('users.email', 'like', "%{$s}%")
                  ->orWhere('users.phone', 'like', "%{$s}%");
            });
        }

        if ($request->filled('deleted_date')) {
            $query->whereDate('users.deleted_at', $request->deleted_date);
        }

        if ($request->filled('deleted_from') && $request->filled('deleted_to')) {
            $query->whereBetween(DB::raw('DATE(users.deleted_at)'), [$request->deleted_from, $request->deleted_to]);
        }

        // A. Export Trashed Records to Excel / CSV
        if ($request->input('export') === 'excel' || $request->input('export') === 'csv') {
            $exportRecords = (clone $query)->latest('users.deleted_at')->get();
            return $this->streamUsersCsvExport($exportRecords, 'Trashed_Users_Report_', true);
        }

        // B. Pure Text Data Endpoint for Trashed PDF Table
        if ($request->input('export') === 'pdf_data') {
            $pdfRecords = (clone $query)->latest('users.deleted_at')->get()->map(function ($u, $idx) {
                return [
                    'sl'         => $idx + 1,
                    'id'         => $u->id,
                    'name'       => $u->name ?: 'N/A',
                    'role'       => ucwords(str_replace(['_', '-'], ' ', $u->role ?: 'Staff')),
                    'branch'     => $u->branch_name ?: 'Head Office / Online',
                    'phone'      => $u->phone ?: 'N/A',
                    'email'      => $u->email ?: 'N/A',
                    'deleted_at' => $u->deleted_at ? date('d M Y, h:i A', strtotime($u->deleted_at)) : 'N/A',
                ];
            });

            return response()->json([
                'success'      => true,
                'title'        => config('app.name', 'Shop Now BD') . ' - Trashed Users & Staff Report',
                'generated_at' => date('d M Y, h:i A'),
                'total'        => $pdfRecords->count(),
                'records'      => $pdfRecords
            ]);
        }

        $limit = $this->resolvePaginationLimit($request, 20);
        $trashedUsers = $query->latest('users.deleted_at')->paginate($limit)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'html'       => view('admin.users.partials.trash_rows', compact('trashedUsers'))->render(),
                'pagination' => $trashedUsers->hasPages() ? $trashedUsers->links('pagination::bootstrap-5')->render() : '',
                'total'      => $trashedUsers->total(),
                'firstItem'  => $trashedUsers->firstItem() ?? 0,
                'lastItem'   => $trashedUsers->lastItem() ?? 0,
                'count'      => $trashedUsers->count(),
            ]);
        }

        $defaultRoles = collect(['super_admin', 'admin', 'branch_manager', 'staff', 'customer']);
        $dbRoles = collect();
        if (Schema::hasTable('role_route_permissions')) {
            $dbRoles = DB::table('role_route_permissions')->distinct()->pluck('role_name');
        }
        $allRoles = $defaultRoles->merge($dbRoles)
            ->map(fn($r) => str_replace(['-', ' '], '_', strtolower(trim((string)$r))))
            ->unique()
            ->values();

        return view('admin.users.trash', compact('trashedUsers', 'allRoles'));
    }

    /**
     * Bulk Action Handler
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer',
            'action' => 'required|string|in:active,inactive,suspended,frozen,blocked,trash,force_logout,unlock'
        ]);

        $ids = $request->input('ids');
        $action = $request->input('action');
        $authId = Auth::id();
        $isSuperAdmin = Auth::user()->isSuperAdmin();

        $targetUsers = DB::table('users')->whereIn('id', $ids)->get();
        $affectedCount = 0;

        foreach ($targetUsers as $target) {
            if ($target->id == $authId) continue;

            $targetIsSuper = (!empty($target->is_super_admin) || $target->role === 'super_admin');
            if ($targetIsSuper && !$isSuperAdmin) continue;

            if ($action === 'trash') {
                if ($targetIsSuper && $this->isLastSuperAdmin($target->id)) continue;
                DB::table('users')->where('id', $target->id)->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);
                $affectedCount++;
            } elseif ($action === 'force_logout') {
                if (Schema::hasColumn('users', 'force_logout')) {
                    DB::table('users')->where('id', $target->id)->update(['force_logout' => true]);
                }
                if (Schema::hasTable('active_user_sessions')) {
                    DB::table('active_user_sessions')->where('user_id', $target->id)->update(['is_revoked' => true]);
                }
                $affectedCount++;
            } elseif ($action === 'unlock') {
                $up = [
                    'is_locked'         => false,
                    'locked_reason'     => null,
                    'password_attempts' => 0,
                    'otp_attempts'      => 0,
                    'is_active'         => true,
                    'updated_at'        => now(),
                ];
                if (Schema::hasColumn('users', 'status')) {
                    $up['status'] = 'active';
                }
                DB::table('users')->where('id', $target->id)->update($up);
                $affectedCount++;
            } else {
                if ($action !== 'active' && $targetIsSuper && $this->isLastSuperAdmin($target->id)) continue;

                $isActive = ($action === 'active');
                $isLocked = in_array($action, ['suspended', 'frozen', 'blocked']);

                $up = [
                    'is_active'     => $isActive,
                    'is_locked'     => $isLocked,
                    'locked_reason' => $isLocked ? 'Bulk Action: ' . ucfirst($action) : null,
                    'updated_at'    => now(),
                ];

                if (Schema::hasColumn('users', 'status')) {
                    $up['status'] = $action;
                }

                if (!$isActive && Schema::hasColumn('users', 'force_logout')) {
                    $up['force_logout'] = true;
                }

                DB::table('users')->where('id', $target->id)->update($up);
                $affectedCount++;
            }
        }

        Cache::flush();

        return response()->json([
            'success' => true,
            'message' => "Successfully processed {$affectedCount} user(s) with action [{$action}]."
        ]);
    }

    public function createStaff()
    {
        $defaultRoles = collect(['admin', 'branch_manager', 'staff']);
        if (Auth::user()->isSuperAdmin()) {
            $defaultRoles->prepend('super_admin');
        }

        $dbRoles = collect();
        if (Schema::hasTable('role_route_permissions')) {
            $dbRoles = DB::table('role_route_permissions')->distinct()->pluck('role_name');
        }
        
        $staffRoles = $defaultRoles->merge($dbRoles)->filter(fn($r) => $r !== 'customer')->unique();
        $branches = Schema::hasTable('branches') ? DB::table('branches')->get() : collect();

        return view('admin.users.create_staff', compact('staffRoles', 'branches'));
    }

    public function storeStaff(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:100',
            'role'             => 'required|string',
            'phone'            => 'required|string|max:20',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:6|confirmed',
            'monthly_salary'   => 'nullable|numeric|min:0',
            'photo'            => 'nullable|image|max:2048',
        ]);

        $isSuper = ($request->role === 'super_admin');

        if ($isSuper && !Auth::user()->isSuperAdmin()) {
            return back()->withInput()->with('error', 'Security Violation: Only an active Super Admin can create another Super Admin account.');
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $this->saveUserFile($request->file('photo'), 'staff/photos');
        }

        $userType = in_array($request->role, ['super_admin', 'admin']) ? 'admin' : 'staff';

        $insertData = [
            'name'             => $request->name,
            'email'            => strtolower(trim($request->email)),
            'password'         => Hash::make($request->password),
            'role'             => $request->role,
            'user_type'        => $userType,
            'designation'      => $request->designation,
            'branch_id'        => $request->branch_id,
            'phone'            => $request->phone,
            'phone_alt'        => $request->phone_alt,
            'email_alt'        => $request->email_alt,
            'nid_number'       => $request->nid_number,
            'dob'              => $request->dob,
            'joining_date'     => $request->joining_date,
            'resignation_date' => $request->resignation_date,
            'reference_name'   => $request->reference_name,
            'reference_phone'  => $request->reference_phone,
            'bio'              => $request->bio,
            'note'             => $request->note,
            'photo'            => $photoPath,
            'monthly_salary'   => $request->monthly_salary ?? 0,
            'salary_pay_day'   => $request->salary_pay_day ?? 5,
            'is_super_admin'   => $isSuper,
            'is_active'        => true,
            'is_locked'        => false,
            'created_by'       => Auth::id(),
            'created_at'       => now(),
            'updated_at'       => now(),
        ];

        if (Schema::hasColumn('users', 'status')) {
            $insertData['status'] = 'active';
        }

        $userId = DB::table('users')->insertGetId($insertData);

        if ($request->has('doc_titles') && $request->hasFile('doc_files')) {
            $titles = $request->doc_titles;
            $files = $request->file('doc_files');

            foreach ($files as $idx => $docFile) {
                if ($docFile && $docFile->isValid()) {
                    $title = $titles[$idx] ?? $docFile->getClientOriginalName();
                    $docPath = $this->saveUserFile($docFile, 'staff/documents');

                    DB::table('user_documents')->insert([
                        'user_id'    => $userId,
                        'doc_title'  => $title,
                        'file_path'  => $docPath,
                        'file_type'  => $docFile->getClientOriginalExtension(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        Cache::flush();

        return redirect()->route('admin.roles.users')->with('success', 'Staff/Admin account created successfully.');
    }

    public function editStaff($id)
    {
        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) abort(404, 'User not found');

        if (($user->is_super_admin || $user->role === 'super_admin') && !Auth::user()->isSuperAdmin()) {
            abort(403, 'Access Denied: Only a Super Admin can edit Super Admin profile.');
        }

        $defaultRoles = collect(['admin', 'branch_manager', 'staff']);
        if (Auth::user()->isSuperAdmin()) {
            $defaultRoles->prepend('super_admin');
        }

        $dbRoles = collect();
        if (Schema::hasTable('role_route_permissions')) {
            $dbRoles = DB::table('role_route_permissions')->distinct()->pluck('role_name');
        }
        $staffRoles = $defaultRoles->merge($dbRoles)->filter(fn($r) => $r !== 'customer')->unique();

        $branches = Schema::hasTable('branches') ? DB::table('branches')->get() : collect();
        $documents = Schema::hasTable('user_documents') ? DB::table('user_documents')->where('user_id', $id)->get() : collect();

        return view('admin.users.edit_staff', compact('user', 'staffRoles', 'branches', 'documents'));
    }

    public function updateStaff(Request $request, $id)
    {
        $targetUser = DB::table('users')->where('id', $id)->first();
        if (!$targetUser) abort(404, 'User not found');

        if (($targetUser->is_super_admin || $targetUser->role === 'super_admin') && !Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Security Violation: Only a Super Admin can modify another Super Admin account.');
        }

        $request->validate([
            'name'           => 'required|string|max:100',
            'role'           => 'required|string',
            'phone'          => 'required|string|max:20',
            'email'          => 'required|email|unique:users,email,' . $id,
            'monthly_salary' => 'nullable|numeric|min:0',
            'photo'          => 'nullable|image|max:2048',
        ]);

        $newRole = $request->role;
        $isSuper = ($newRole === 'super_admin');

        if (($targetUser->is_super_admin || $targetUser->role === 'super_admin') && !$isSuper) {
            if ($this->isLastSuperAdmin($id)) {
                return back()->withInput()->with('error', 'Security Block: Cannot demote the last remaining Super Admin in the system.');
            }
        }

        $userType = in_array($newRole, ['super_admin', 'admin']) ? 'admin' : 'staff';

        $updateData = [
            'name'             => $request->name,
            'email'            => strtolower(trim($request->email)),
            'role'             => $newRole,
            'user_type'        => $userType,
            'designation'      => $request->designation,
            'branch_id'        => $request->branch_id,
            'phone'            => $request->phone,
            'phone_alt'        => $request->phone_alt,
            'email_alt'        => $request->email_alt,
            'nid_number'       => $request->nid_number,
            'dob'              => $request->dob,
            'joining_date'     => $request->joining_date,
            'resignation_date' => $request->resignation_date,
            'reference_name'   => $request->reference_name,
            'reference_phone'  => $request->reference_phone,
            'bio'              => $request->bio,
            'note'             => $request->note,
            'monthly_salary'   => $request->monthly_salary ?? 0,
            'salary_pay_day'   => $request->salary_pay_day ?? 5,
            'is_super_admin'   => $isSuper,
            'updated_at'       => now(),
        ];

        if ($request->hasFile('photo')) {
            if ($targetUser->photo) {
                $this->deleteUserFile($targetUser->photo);
            }
            $updateData['photo'] = $this->saveUserFile($request->file('photo'), 'staff/photos');
        }

        DB::table('users')->where('id', $id)->update($updateData);

        if ($request->has('doc_titles') && $request->hasFile('doc_files')) {
            $titles = $request->doc_titles;
            $files = $request->file('doc_files');

            foreach ($files as $idx => $docFile) {
                if ($docFile && $docFile->isValid()) {
                    $title = $titles[$idx] ?? $docFile->getClientOriginalName();
                    $docPath = $this->saveUserFile($docFile, 'staff/documents');

                    DB::table('user_documents')->insert([
                        'user_id'    => $id,
                        'doc_title'  => $title,
                        'file_path'  => $docPath,
                        'file_type'  => $docFile->getClientOriginalExtension(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        Cache::flush();

        return redirect()->route('admin.roles.users')->with('success', 'Staff profile updated successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:active,inactive,suspended,frozen,blocked',
            'reason' => 'nullable|string|max:255',
        ]);

        $targetUser = DB::table('users')->where('id', $id)->first();
        if (!$targetUser) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        if ($targetUser->id == Auth::id()) {
            return response()->json(['success' => false, 'message' => 'You cannot change the status of your own logged-in account.'], 403);
        }

        if (($targetUser->is_super_admin || $targetUser->role === 'super_admin') && !Auth::user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only a Super Admin can alter Super Admin account status.'], 403);
        }

        $newStatus = strtolower($request->status);

        if ($newStatus !== 'active' && ($targetUser->is_super_admin || $targetUser->role === 'super_admin')) {
            if ($this->isLastSuperAdmin($id)) {
                return response()->json(['success' => false, 'message' => 'Cannot disable or block the last active Super Admin in the system.'], 403);
            }
        }

        $isActive = ($newStatus === 'active');
        $isLocked = in_array($newStatus, ['suspended', 'frozen', 'blocked']);

        $updateData = [
            'is_active'     => $isActive,
            'is_locked'     => $isLocked,
            'locked_reason' => $isLocked ? ($request->reason ?: 'Account ' . ucfirst($newStatus)) : null,
            'updated_at'    => now(),
        ];

        if (Schema::hasColumn('users', 'status')) {
            $updateData['status'] = $newStatus;
        }

        if (!$isActive && Schema::hasColumn('users', 'force_logout')) {
            $updateData['force_logout'] = true;
        }

        DB::table('users')->where('id', $id)->update($updateData);

        Cache::flush();

        return response()->json([
            'success' => true,
            'message' => "Account for {$targetUser->name} is now " . strtoupper($newStatus) . "."
        ]);
    }

    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $targetUser = DB::table('users')->where('id', $id)->first();
        if (!$targetUser) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        if (($targetUser->is_super_admin || $targetUser->role === 'super_admin') && !Auth::user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only a Super Admin can reset Super Admin passwords.'], 403);
        }

        $updateData = [
            'password'          => Hash::make($request->password),
            'password_attempts' => 0,
            'updated_at'        => now(),
        ];

        if (Schema::hasColumn('users', 'force_logout')) {
            $updateData['force_logout'] = true;
        }

        DB::table('users')->where('id', $id)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => "Password for {$targetUser->name} has been successfully reset."
        ]);
    }

    public function forceLogout($id)
    {
        $targetUser = DB::table('users')->where('id', $id)->first();
        if (!$targetUser) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        if ($targetUser->id == Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Cannot force logout your own current session from this button.'], 403);
        }

        if (($targetUser->is_super_admin || $targetUser->role === 'super_admin') && !Auth::user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only a Super Admin can revoke Super Admin sessions.'], 403);
        }

        if (Schema::hasColumn('users', 'force_logout')) {
            DB::table('users')->where('id', $id)->update([
                'force_logout' => true,
                'updated_at'   => now()
            ]);
        }

        if (Schema::hasTable('active_user_sessions')) {
            DB::table('active_user_sessions')->where('user_id', $id)->update(['is_revoked' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => "Force logout signal sent for {$targetUser->name}. Session will terminate immediately."
        ]);
    }

    public function createCustomer()
    {
        return view('admin.users.create_customer');
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'nullable|required_without:phone|email|unique:users,email',
            'phone'    => 'nullable|required_without:email|string|max:20',
            'password' => 'required|string|min:6',
            'photo'    => 'nullable|image|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $this->saveUserFile($request->file('photo'), 'customers/photos');
        }

        $finalEmail = $request->filled('email') 
            ? strtolower(trim($request->email)) 
            : preg_replace('/[^0-9]/', '', $request->phone) . '@customer.local';

        $insertData = [
            'name'           => $request->name,
            'email'          => $finalEmail,
            'phone'          => $request->phone,
            'password'       => Hash::make($request->password),
            'role'           => 'customer',
            'user_type'      => 'customer',
            'designation'    => null,
            'monthly_salary' => 0,
            'photo'          => $photoPath,
            'bio'            => $request->address,
            'note'           => $request->note,
            'is_active'      => true,
            'created_by'     => Auth::id(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ];

        if (Schema::hasColumn('users', 'status')) {
            $insertData['status'] = 'active';
        }

        DB::table('users')->insert($insertData);

        return redirect()->route('admin.roles.users')->with('success', 'Customer account registered successfully.');
    }

    public function show($id)
    {
        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) abort(404, 'User not found');

        $creator = $user->created_by ? DB::table('users')->where('id', $user->created_by)->value('name') : null;

        $customerOrders = collect();
        $orderStats = [
            'total'     => 0,
            'completed' => 0,
            'pending'   => 0,
            'returned'  => 0,
            'cancelled' => 0,
            'spent'     => 0,
        ];
        $supportTickets = collect();
        $documents = collect();
        $payrolls = collect();
        $attendances = collect();

        if ($user->role === 'customer') {
            $rawPhone = trim((string)$user->phone);
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            $last10Phone = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

            $phoneCol = 'shipping_phone';
            if (!Schema::hasColumn('orders', 'shipping_phone')) {
                $phoneCol = Schema::hasColumn('orders', 'phone') ? 'phone' : (Schema::hasColumn('orders', 'customer_phone') ? 'customer_phone' : null);
            }

            $orderQuery = DB::table('orders')->where(function ($q) use ($user, $phoneCol, $last10Phone) {
                $matched = false;
                if (Schema::hasColumn('orders', 'user_id') && !empty($user->id)) {
                    $q->where('user_id', $user->id);
                    $matched = true;
                }
                if ($phoneCol && !empty($last10Phone)) {
                    $matched ? $q->orWhere($phoneCol, 'like', "%{$last10Phone}%") : $q->where($phoneCol, 'like', "%{$last10Phone}%");
                    $matched = true;
                }
                if (!empty($user->email) && Schema::hasColumn('orders', 'shipping_email')) {
                    $matched ? $q->orWhere('shipping_email', $user->email) : $q->where('shipping_email', $user->email);
                    $matched = true;
                }
                if (!$matched) {
                    $q->where('id', 0);
                }
            });

            $customerOrders = (clone $orderQuery)->latest('id')->take(10)->get();

            $orderStats['total']     = (clone $orderQuery)->count();
            $orderStats['completed'] = (clone $orderQuery)->where('order_status', 'delivered')->count();
            $orderStats['pending']   = (clone $orderQuery)->whereIn('order_status', ['pending', 'received', 'confirmed', 'shipped'])->count();
            $orderStats['returned']  = (clone $orderQuery)->whereIn('order_status', ['returned', 'return_requested'])->count();
            $orderStats['cancelled'] = (clone $orderQuery)->where('order_status', 'cancelled')->count();
            $orderStats['spent']     = (clone $orderQuery)->where('order_status', 'delivered')->sum('grand_total');

            if (Schema::hasTable('support_tickets')) {
                $userName = trim((string)$user->name);
                $invoices = $customerOrders->pluck('invoice_no')->filter()->toArray();

                $supportTickets = DB::table('support_tickets')
                    ->where(function ($q) use ($user, $userName, $last10Phone, $invoices) {
                        $hasCondition = false;

                        if (Schema::hasColumn('support_tickets', 'user_id') && !empty($user->id)) {
                            $q->where('user_id', $user->id);
                            $hasCondition = true;
                        }

                        if (Schema::hasColumn('support_tickets', 'user_name') && !empty($userName)) {
                            $hasCondition ? $q->orWhere('user_name', $userName) : $q->where('user_name', $userName);
                            $hasCondition = true;
                        }

                        if (Schema::hasColumn('support_tickets', 'user_phone') && !empty($last10Phone)) {
                            $hasCondition ? $q->orWhere('user_phone', 'like', "%{$last10Phone}%") : $q->where('user_phone', 'like', "%{$last10Phone}%");
                            $hasCondition = true;
                        }

                        foreach ($invoices as $inv) {
                            if (!empty($inv)) {
                                $hasCondition ? $q->orWhere('subject', 'like', "%{$inv}%") : $q->where('subject', 'like', "%{$inv}%");
                                $hasCondition = true;
                            }
                        }

                        if (!$hasCondition) {
                            $q->where('id', 0);
                        }
                    })
                    ->latest('id')
                    ->take(15)
                    ->get();
            }
        } else {
            if (Schema::hasTable('user_documents')) {
                $documents = DB::table('user_documents')->where('user_id', $user->id)->get();
            }
            if (Schema::hasTable('staff_payrolls')) {
                $payrolls = DB::table('staff_payrolls')->where('user_id', $user->id)->latest('id')->take(12)->get();
            }
            if (Schema::hasTable('attendances')) {
                $attendances = DB::table('attendances')->where('user_id', $user->id)->latest('attendance_date')->take(30)->get();
            }
        }

        return view('admin.users.show', compact('user', 'creator', 'customerOrders', 'orderStats', 'supportTickets', 'documents', 'payrolls', 'attendances'));
    }

    public function downloadDoc($id)
    {
        $doc = DB::table('user_documents')->where('id', $id)->first();
        if (!$doc) abort(404, 'File not found');

        $clean = ltrim($doc->file_path, '/');
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, 8);
        }

        $publicRoot = $this->getPublicRoot();
        $fullPath = $publicRoot . '/' . $clean;

        if (!file_exists($fullPath)) {
            $fullPath = $publicRoot . '/storage/' . $clean;
        }

        if (!file_exists($fullPath)) {
            abort(404, 'File not found on server.');
        }

        return response()->download($fullPath);
    }

    public function unlock($id)
    {
        $updateData = [
            'is_locked'         => false,
            'locked_reason'     => null,
            'password_attempts' => 0,
            'otp_attempts'      => 0,
            'is_active'         => true,
            'updated_at'        => now(),
        ];

        if (Schema::hasColumn('users', 'status')) {
            $updateData['status'] = 'active';
        }

        DB::table('users')->where('id', $id)->update($updateData);

        return response()->json(['success' => true, 'message' => 'Account unlocked and activated successfully.']);
    }

    public function destroy(Request $request, $id)
    {
        $targetUser = DB::table('users')->where('id', $id)->first();
        if (!$targetUser) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        if ($targetUser->id == Auth::id()) {
            return response()->json(['success' => false, 'message' => 'You cannot delete your own logged-in account.'], 403);
        }

        if (($targetUser->is_super_admin || $targetUser->role === 'super_admin')) {
            if (!Auth::user()->isSuperAdmin()) {
                return response()->json(['success' => false, 'message' => 'Access Denied: Only a Super Admin can remove another Super Admin.'], 403);
            }
            if ($this->isLastSuperAdmin($id)) {
                return response()->json(['success' => false, 'message' => 'System Protection: Cannot delete the last remaining Super Admin.'], 403);
            }
        }

        DB::table('users')->where('id', $id)->update([
            'deleted_at' => now(),
            'updated_at' => now()
        ]);

        Cache::flush();

        return response()->json(['success' => true, 'message' => "{$targetUser->name} has been moved to Trash."]);
    }

    public function restore($id)
    {
        DB::table('users')->where('id', $id)->update([
            'deleted_at' => null,
            'updated_at' => now()
        ]);

        Cache::flush();

        return response()->json(['success' => true, 'message' => 'Account restored successfully.']);
    }

    public function forceDelete(Request $request, $id)
    {
        $targetUser = DB::table('users')->where('id', $id)->first();
        if (!$targetUser) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'User does not exist.'], 404);
            }
            return back()->with('error', 'User does not exist.');
        }

        if ($targetUser->id == Auth::id()) {
            return back()->with('error', 'Cannot delete self.');
        }

        if (($targetUser->is_super_admin || $targetUser->role === 'super_admin') && $this->isLastSuperAdmin($id)) {
            return back()->with('error', 'Security Block: Cannot permanently delete the last Super Admin.');
        }

        if ($targetUser->photo) {
            $this->deleteUserFile($targetUser->photo);
        }

        if (Schema::hasTable('user_documents')) {
            $docs = DB::table('user_documents')->where('user_id', $id)->get();
            foreach ($docs as $d) {
                $this->deleteUserFile($d->file_path);
            }
            DB::table('user_documents')->where('user_id', $id)->delete();
        }

        DB::table('users')->where('id', $id)->delete();

        Cache::flush();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'User permanently deleted from database.']);
        }

        return back()->with('success', 'User permanently deleted from database.');
    }
}