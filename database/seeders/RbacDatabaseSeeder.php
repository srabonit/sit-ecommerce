<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Http\Controllers\Admin\RolePermissionController;

class RbacDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. System module definitions theke module list load kora
        $systemModules = RolePermissionController::getSystemModules();

        if (Schema::hasTable('role_route_permissions')) {
            $tableCols = Schema::getColumnListing('role_route_permissions');
            $isCrudSchema = in_array('can_create', $tableCols) && in_array('can_read', $tableCols);

            $roleCol = in_array('role_name', $tableCols) ? 'role_name' : (in_array('role', $tableCols) ? 'role' : 'role_name');
            $moduleCol = in_array('route_module', $tableCols) ? 'route_module' : (in_array('module', $tableCols) ? 'module' : (in_array('module_name', $tableCols) ? 'module_name' : null));

            if ($isCrudSchema) {
                // === CASE A: Module-level CRUD schema (role_name, route_module, can_create, can_update, can_delete, can_read) ===

                $modulesList = [];
                foreach (array_keys($systemModules) as $title) {
                    $modulesList[] = Str::slug($title, '_');
                }
                $modulesList = array_unique($modulesList);

                // A. 'admin' role-er jonno shokol module-e full CRUD (1, 1, 1, 1) access
                DB::table('role_route_permissions')->where($roleCol, 'admin')->delete();

                $adminBatch = [];
                foreach ($modulesList as $mSlug) {
                    $row = [
                        $roleCol     => 'admin',
                        'can_create' => 1,
                        'can_update' => 1,
                        'can_delete' => 1,
                        'can_read'   => 1,
                    ];
                    if ($moduleCol) {
                        $row[$moduleCol] = $mSlug;
                    }
                    if (in_array('created_at', $tableCols)) $row['created_at'] = now();
                    if (in_array('updated_at', $tableCols)) $row['updated_at'] = now();

                    $adminBatch[] = $row;
                }

                if (!empty($adminBatch)) {
                    DB::table('role_route_permissions')->insert($adminBatch);
                }

                // B. 'branch_manager' role-er jonno branch-specific CRUD matrix
                DB::table('role_route_permissions')->where($roleCol, 'branch_manager')->delete();

                $branchMatrix = [
                    'dashboard'                => ['c' => 0, 'u' => 0, 'd' => 0, 'r' => 1],
                    'pos_terminal'             => ['c' => 1, 'u' => 1, 'd' => 0, 'r' => 1],
                    'order_management'         => ['c' => 1, 'u' => 1, 'd' => 0, 'r' => 1],
                    'courier_logistics'        => ['c' => 1, 'u' => 1, 'd' => 0, 'r' => 1],
                    'product_management'       => ['c' => 0, 'u' => 0, 'd' => 0, 'r' => 1],
                    'branch_hubs'              => ['c' => 0, 'u' => 1, 'd' => 0, 'r' => 1],
                    'media_library'            => ['c' => 1, 'u' => 0, 'd' => 0, 'r' => 1],
                    'accounts_ledger'          => ['c' => 1, 'u' => 0, 'd' => 0, 'r' => 1],
                    'reports_audit'            => ['c' => 0, 'u' => 0, 'd' => 0, 'r' => 1],
                    'customer_support_reviews' => ['c' => 1, 'u' => 1, 'd' => 0, 'r' => 1],
                    'promo_campaigns'          => ['c' => 0, 'u' => 0, 'd' => 0, 'r' => 1],
                ];

                $branchBatch = [];
                foreach ($branchMatrix as $mSlug => $rights) {
                    $row = [
                        $roleCol     => 'branch_manager',
                        'can_create' => $rights['c'],
                        'can_update' => $rights['u'],
                        'can_delete' => $rights['d'],
                        'can_read'   => $rights['r'],
                    ];
                    if ($moduleCol) {
                        $row[$moduleCol] = $mSlug;
                    }
                    if (in_array('created_at', $tableCols)) $row['created_at'] = now();
                    if (in_array('updated_at', $tableCols)) $row['updated_at'] = now();

                    $branchBatch[] = $row;
                }

                if (!empty($branchBatch)) {
                    DB::table('role_route_permissions')->insert($branchBatch);
                }

                // C. 'staff' role-er jonno basic operations matrix
                DB::table('role_route_permissions')->where($roleCol, 'staff')->delete();
                $staffMatrix = [
                    'dashboard'        => ['c' => 0, 'u' => 0, 'd' => 0, 'r' => 1],
                    'pos_terminal'     => ['c' => 1, 'u' => 1, 'd' => 0, 'r' => 1],
                    'order_management' => ['c' => 1, 'u' => 1, 'd' => 0, 'r' => 1],
                    'branch_hubs'      => ['c' => 0, 'u' => 0, 'd' => 0, 'r' => 1],
                ];

                $staffBatch = [];
                foreach ($staffMatrix as $mSlug => $rights) {
                    $row = [
                        $roleCol     => 'staff',
                        'can_create' => $rights['c'],
                        'can_update' => $rights['u'],
                        'can_delete' => $rights['d'],
                        'can_read'   => $rights['r'],
                    ];
                    if ($moduleCol) {
                        $row[$moduleCol] = $mSlug;
                    }
                    if (in_array('created_at', $tableCols)) $row['created_at'] = now();
                    if (in_array('updated_at', $tableCols)) $row['updated_at'] = now();

                    $staffBatch[] = $row;
                }

                if (!empty($staffBatch)) {
                    DB::table('role_route_permissions')->insert($staffBatch);
                }

            } else {
                // === CASE B: Route-name string based fallback schema ===
                $routeCol = collect($tableCols)->first(fn($c) => in_array($c, [
                    'route_name', 'permission_name', 'permission', 'route', 'routes'
                ])) ?? 'route_name';

                DB::table('role_route_permissions')->where($roleCol, 'admin')->delete();

                $adminBatch = [];
                foreach ($systemModules as $moduleTitle => $moduleInfo) {
                    $mSlug = Str::slug($moduleTitle, '_');
                    foreach (array_keys($moduleInfo['permissions'] ?? []) as $rKey) {
                        $row = [
                            $roleCol  => 'admin',
                            $routeCol => $rKey,
                        ];
                        if ($moduleCol) {
                            $row[$moduleCol] = $mSlug;
                        }
                        if (in_array('created_at', $tableCols)) $row['created_at'] = now();
                        if (in_array('updated_at', $tableCols)) $row['updated_at'] = now();
                        $adminBatch[] = $row;
                    }
                }

                if (!empty($adminBatch)) {
                    DB::table('role_route_permissions')->insert($adminBatch);
                }
            }
        }

        // 2. Spatie Permissions table sync (jodi table theke thake)
        if (Schema::hasTable('permissions') && Schema::hasTable('roles')) {
            try {
                $allRoutes = [];
                foreach ($systemModules as $m) {
                    foreach (array_keys($m['permissions'] ?? []) as $rk) {
                        $allRoutes[] = $rk;
                    }
                }

                foreach (array_unique($allRoutes) as $pName) {
                    DB::table('permissions')->updateOrInsert(
                        ['name' => $pName, 'guard_name' => 'web'],
                        ['updated_at' => now()]
                    );
                }

                DB::table('roles')->updateOrInsert(
                    ['name' => 'admin', 'guard_name' => 'web'],
                    ['updated_at' => now()]
                );

                DB::table('roles')->updateOrInsert(
                    ['name' => 'branch_manager', 'guard_name' => 'web'],
                    ['updated_at' => now()]
                );

                DB::table('roles')->updateOrInsert(
                    ['name' => 'staff', 'guard_name' => 'web'],
                    ['updated_at' => now()]
                );

                DB::table('roles')->updateOrInsert(
                    ['name' => 'super_admin', 'guard_name' => 'web'],
                    ['updated_at' => now()]
                );
            } catch (\Throwable $e) {
                // Spatie fallback bypass
            }
        }

        // 3. Root Super Admin user account check & unlock
        $superAdmin = DB::table('users')
            ->where('role', 'super_admin')
            ->orWhere('is_super_admin', true)
            ->first();

        if (!$superAdmin) {
            $userData = [
                'name'              => 'Root Super Admin',
                'email'             => 'admin@shopnowbd.online',
                'phone'             => '01700000000',
                'password'          => Hash::make('admin123456'),
                'role'              => 'super_admin',
                'designation'       => 'Chief Executive Officer',
                'is_super_admin'    => true,
                'is_active'         => true,
                'is_locked'         => false,
                'monthly_salary'    => 0,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];
            if (Schema::hasColumn('users', 'status')) $userData['status'] = 'active';
            if (Schema::hasColumn('users', 'force_logout')) $userData['force_logout'] = false;

            DB::table('users')->insert($userData);
        } else {
            $updateData = [
                'is_super_admin' => true,
                'is_active'      => true,
                'is_locked'      => false,
                'force_logout'   => false,
            ];
            if (Schema::hasColumn('users', 'status')) $updateData['status'] = 'active';

            DB::table('users')->where('id', $superAdmin->id)->update($updateData);
        }

        // 4. Cache flush kora
        Cache::flush();
    }
}