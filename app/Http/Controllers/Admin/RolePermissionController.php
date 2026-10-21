<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RolePermissionController extends Controller
{
    /**
     * Database theke hyphenated duplicate roles auto merge ebong cleanup helper
     */
    public static function autoCleanupDuplicateRoles(): void
    {
        try {
            // ১. Users টেবিলে কোনো হাইফেন রোলে ইউজার থাকলে আন্ডারস্কোরে রূপান্তর
            if (Schema::hasTable('users')) {
                $hyphenUsers = DB::table('users')->where('role', 'like', '%-%')->get();
                foreach ($hyphenUsers as $u) {
                    $cleanRole = str_replace('-', '_', strtolower($u->role));
                    DB::table('users')->where('id', $u->id)->update(['role' => $cleanRole]);
                }
            }

            // ২. role_route_permissions টেবিলে হাইফেন রোল ডুপ্লিকেট মার্জ ও ডিলিট
            if (Schema::hasTable('role_route_permissions')) {
                $hyphenPerms = DB::table('role_route_permissions')->where('role_name', 'like', '%-%')->get();
                foreach ($hyphenPerms as $hRow) {
                    $cleanRole = str_replace('-', '_', strtolower($hRow->role_name));
                    $exists = DB::table('role_route_permissions')
                        ->where('role_name', $cleanRole)
                        ->where('route_module', $hRow->route_module)
                        ->exists();

                    if ($exists) {
                        DB::table('role_route_permissions')->where('id', $hRow->id)->delete();
                    } else {
                        DB::table('role_route_permissions')->where('id', $hRow->id)->update(['role_name' => $cleanRole]);
                    }
                }
            }

            // ৩. Spatie roles টেবিলে থাকা হাইফেন ডুপ্লিকেট রোল ডিলিট
            if (Schema::hasTable('roles')) {
                $hyphenRoles = DB::table('roles')->where('name', 'like', '%-%')->get();
                foreach ($hyphenRoles as $hRole) {
                    $cleanRole = str_replace('-', '_', strtolower($hRole->name));
                    $underscoreRole = DB::table('roles')->where('name', $cleanRole)->first();

                    if ($underscoreRole) {
                        if (Schema::hasTable('role_has_permissions')) {
                            $oldPerms = DB::table('role_has_permissions')->where('role_id', $hRole->id)->pluck('permission_id');
                            foreach ($oldPerms as $permId) {
                                DB::table('role_has_permissions')->updateOrInsert([
                                    'permission_id' => $permId,
                                    'role_id'       => $underscoreRole->id
                                ]);
                            }
                            DB::table('role_has_permissions')->where('role_id', $hRole->id)->delete();
                        }
                        DB::table('roles')->where('id', $hRole->id)->delete();
                    } else {
                        DB::table('roles')->where('id', $hRole->id)->update(['name' => $cleanRole]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Failsafe bypass
        }
    }

    /**
     * সিস্টেমের সকল মডিউল ও তাদের রাউট-পারমিশন ডেফিনিশন (১০০% সম্পূর্ণ তালিকা)[cite: 63]
     */
    public static function getSystemModules(): array
    {
        return [
            'Dashboard' => [
                'icon'  => 'fas fa-gauge-high',
                'color' => 'primary',
                'permissions' => [
                    'admin.dashboard'   => 'View Main Analytics Dashboard',
                    'admin.search.ajax' => 'Global Quick Search & Barcode Lookup',
                ]
            ],
            'POS Terminal' => [
                'icon'  => 'fas fa-cash-register',
                'color' => 'success',
                'permissions' => [
                    'admin.pos.index'            => 'Access POS Landing Page',
                    'admin.pos.terminal'         => 'Open Branch POS Terminal',
                    'admin.pos.order.place'      => 'Place & Complete POS Orders',
                    'admin.pos.filter.products'  => 'Filter & Search Products in POS',
                    'admin.pos.scan.barcode'     => 'Scan Barcode in POS Terminal',
                    'admin.pos.search.customers' => 'Live Search Customers in POS',
                    'admin.pos.customer.create'  => 'Register Walk-in Customers from POS',
                    'admin.pos.print.thermal'    => 'Print POS 80mm Thermal Receipt',
                    'admin.pos.print.a5'         => 'Print POS A5 Customer Memo',
                ]
            ],
            'Order Management' => [
                'icon'  => 'fas fa-cart-shopping',
                'color' => 'primary',
                'permissions' => [
                    'admin.order.online'        => 'View All Online Orders List',
                    'admin.order.view'          => 'View Order Details & Full Invoice',
                    'admin.order.status.update' => 'Update Order Processing Status',
                    'admin.order.courier.send'  => 'Send / Dispatch Order to Courier API',
                    'admin.order.invoice.print' => 'Print Official Customer Invoice',
                    'admin.order.destroy'       => 'Delete Order Permanently',
                    'admin.order.bulk.action'   => 'Execute Bulk Order Operations',
                    'admin.order.incomplete'    => 'Manage Incomplete / Abandoned Orders',
                    'admin.order.return'        => 'Manage Returned Parcel Records',
                    'admin.order.exchange'      => 'Manage Customer Exchange Orders',
                    'admin.order.fraud'         => 'Use FraudBD Customer Phone Checker',
                ]
            ],
            'Courier Logistics' => [
                'icon'  => 'fas fa-truck-fast',
                'color' => 'info',
                'permissions' => [
                    'admin.courier.dashboard'            => 'View Courier Financial Dashboard',
                    'admin.courier.index'                => 'View All Courier Consignments',
                    'admin.courier.booking'              => 'Book New Courier Consignment',
                    'admin.courier.settlements'          => 'View COD & Cost Settlement Ledger',
                    'admin.courier.settlement.update'    => 'Update Payment Settlement Status',
                    'admin.courier.sync.status'          => 'Live Sync Tracking (Steadfast/Pathao)',
                    'admin.courier.manual.status.update' => 'Manual Status & Delivery Cost Override',
                    'admin.courier.destroy'              => 'Delete Courier Record (Safe)',
                    'admin.courier.bulk.action'          => 'Bulk Courier Consignment Actions',
                ]
            ],
            'Product Management' => [
                'icon'  => 'fas fa-boxes-stacked',
                'color' => 'warning',
                'permissions' => [
                    'admin.product.index'                 => 'View Products Inventory List',
                    'admin.product.create'                => 'Create New Product',
                    'admin.product.edit'                  => 'Edit Product Details',
                    'admin.product.destroy'               => 'Delete Product Permanently',
                    'admin.product.bulk.action'           => 'Execute Bulk Product Actions',
                    'admin.product.toggle.featured'       => 'Toggle Featured Product Badge',
                    'admin.product.category'              => 'Manage Product Categories',
                    'admin.product.category.quick_store'  => 'Quick Add Category via Modal',
                    'admin.product.category.destroy'      => 'Delete Product Category',
                    'admin.product.brand'                 => 'Manage Product Brands',
                    'admin.product.brand.quick_store'     => 'Quick Add Brand via Modal',
                    'admin.product.brand.destroy'         => 'Delete Product Brand',
                    'admin.product.barcode'               => 'Generate & Print Product Barcodes',
                ]
            ],
            'Branch Hubs' => [
                'icon'  => 'fas fa-building-user',
                'color' => 'secondary',
                'permissions' => [
                    'admin.branch.index'          => 'View All Branch Hubs List',
                    'admin.branch.create'         => 'Create New Branch Hub',
                    'admin.branch.edit'           => 'Edit Branch Information',
                    'admin.branch.destroy'        => 'Delete Branch Hub',
                    'admin.branch.toggle.status'  => 'Toggle Branch Active/Inactive Status',
                    'admin.branch.new.assign'     => 'Assign Stock to Branches',
                    'admin.branch.assign.history' => 'View Stock Transfer History',
                    'admin.branch.products'       => 'View Branch Stock Levels',
                    'admin.branch.orders'         => 'View Branch-Specific POS Orders',
                ]
            ],
            'Purchases' => [
                'icon'  => 'fas fa-receipt',
                'color' => 'dark',
                'permissions' => [
                    'admin.purchase.index'  => 'View All Purchases Ledger',
                    'admin.purchase.create' => 'Record New Vendor Purchase',
                ]
            ],
            'Media Library' => [
                'icon'  => 'fas fa-photo-film',
                'color' => 'dark',
                'permissions' => [
                    'admin.media.index'        => 'View Global Media Library',
                    'admin.media.upload'       => 'Upload Media Images & Files',
                    'admin.media.update'       => 'Update Media Metadata & Alt Text',
                    'admin.media.replace'      => 'Replace Existing Media File',
                    'admin.media.destroy'      => 'Delete Media File',
                    'admin.media.bulk.destroy' => 'Bulk Delete Media Files',
                ]
            ],
            'Accounts & Ledger' => [
                'icon'  => 'fas fa-hand-holding-dollar',
                'color' => 'success',
                'permissions' => [
                    'admin.accounts.methods'      => 'View Payment Methods Ledger',
                    'admin.accounts.transactions' => 'View Income & Expense Transactions',
                    'admin.accounts.expenses'     => 'Manage Operating Expenses',
                    'admin.accounts.income'       => 'Manage Store Incomes',
                    'admin.accounts.assets'       => 'Manage Business Assets',
                    'admin.accounts.investment'   => 'Manage Investor Ledger',
                    'admin.accounts.bank'         => 'View Live Bank Balances',
                ]
            ],
            'Reports & Audit' => [
                'icon'  => 'fas fa-chart-line',
                'color' => 'primary',
                'permissions' => [
                    'admin.report.sales'    => 'View Analytical Sales Reports',
                    'admin.report.stock'    => 'View Stock Inventory Reports',
                    'admin.report.purchase' => 'View Purchase & Expense Reports',
                ]
            ],
            'Customer Support & Contacts' => [
                'icon'  => 'fas fa-headset',
                'color' => 'info',
                'permissions' => [
                    'admin.support.index'    => 'View Customer Support Tickets',
                    'admin.support.show'     => 'Read & Reply to Support Tickets',
                    'admin.review.index'     => 'Moderate Product Reviews',
                    'admin.contact.customer' => 'View Registered Customers Directory',
                    'admin.contact.supplier' => 'View Vendor & Suppliers Directory',
                ]
            ],
            'Promo & Campaigns' => [
                'icon'  => 'fas fa-tags',
                'color' => 'danger',
                'permissions' => [
                    'admin.promo.flashsale' => 'Configure Flash Sale Campaigns',
                    'admin.promo.coupon'    => 'Manage Discount Coupons & Vouchers',
                ]
            ],
            'Dynamic Form Builder' => [
                'icon'  => 'fas fa-table-list',
                'color' => 'secondary',
                'permissions' => [
                    'admin.forms.index'   => 'View All Dynamic Forms',
                    'admin.forms.create'  => 'Create New Custom Forms',
                    'admin.forms.edit'    => 'Build & Edit Custom Form Fields',
                    'admin.forms.destroy' => 'Delete Custom Forms',
                ]
            ],
            'Site Content & CMS' => [
                'icon'  => 'fas fa-newspaper',
                'color' => 'primary',
                'permissions' => [
                    'admin.content.pages.index'                    => 'Manage Custom CMS Pages',
                    'admin.content.blogs.index'                    => 'Manage Blog Articles & Posts',
                    'admin.content.blogs.comments'                 => 'Moderate Blog Post Comments',
                    'admin.content.blogs.comments.toggleAutoApprove' => 'Toggle Auto-Approve Blog Comments',
                    'admin.content.menus.index'                    => 'Configure Navigation Menus',
                    'admin.content.widgets.index'                  => 'Manage Storefront Widgets',
                ]
            ],
            'Shipping Configuration' => [
                'icon'  => 'fas fa-boxes-packing',
                'color' => 'warning',
                'permissions' => [
                    'admin.shipping.index'    => 'Manage Shipping Delivery Zones',
                    'admin.shipping.classes'  => 'Manage Shipping Cost Classes',
                    'admin.shipping.pickup'   => 'Manage Local Store Pickup Hubs',
                    'admin.shipping.settings' => 'General Shipping Calculations',
                ]
            ],
            'Theme & Storefront' => [
                'icon'  => 'fas fa-paintbrush',
                'color' => 'success',
                'permissions' => [
                    'admin.theme.setting.home_builder'       => 'Drag & Drop Homepage Builder',
                    'admin.theme.setting.featured_products'  => 'Configure Featured Products',
                    'admin.theme.setting.slider'             => 'Configure Hero Banner Sliders',
                    'admin.theme.setting.flash_sale'         => 'Hero Slider & Flash Cards Setup',
                    'admin.theme.setting.popular_categories' => 'Popular Category Slider Setup',
                    'admin.theme.setting.topbar'             => 'Storefront Topbar Configuration',
                    'admin.theme.setting.colors'             => 'Customize Theme Brand Colors',
                    'admin.theme.setting.footer'             => 'Storefront Footer Builder',
                    'admin.theme.setting.product_card'       => 'Product Grid Card Display Mode',
                    'admin.theme.setting.cart_features'      => 'Cart & Instant Checkout Features',
                ]
            ],
            'Users & Roles (RBAC Security)' => [
                'icon'  => 'fas fa-user-shield',
                'color' => 'danger',
                'permissions' => [
                    'admin.roles.index'              => 'View Roles & Access Levels',
                    'admin.roles.create'             => 'Create New Role',
                    'admin.roles.edit'               => 'Edit Role Permissions',
                    'admin.roles.destroy'            => 'Delete Custom Role',
                    'admin.roles.users'              => 'View Staff & Internal Users',
                    'admin.roles.users.create_staff' => 'Register New Staff Member',
                    'admin.roles.users.edit_staff'   => 'Edit Staff Employment Info',
                    'admin.roles.trash'              => 'View & Restore Trashed Accounts',
                ]
            ],
            'Live User Sessions' => [
                'icon'  => 'fas fa-tower-broadcast',
                'color' => 'success',
                'permissions' => [
                    'admin.sessions.index'      => 'Monitor Live Logged-In Sessions',
                    'admin.sessions.revoke'     => 'Terminate Device Session Remotely',
                    'admin.sessions.revoke_all' => 'Force Logout All Connected Devices',
                ]
            ],
            'Activity Logs & Audit Trail' => [
                'icon'  => 'fas fa-clipboard-list',
                'color' => 'info',
                'permissions' => [
                    'admin.activity.logs'    => 'View System Audit Trail & Logs',
                    'admin.activity.show'    => 'Inspect Old vs New Data Differences',
                    'admin.activity.cleanup' => 'Purge & Archive Old Audit Records',
                ]
            ],
            'System & Gateway Settings' => [
                'icon'  => 'fas fa-gears',
                'color' => 'dark',
                'permissions' => [
                    'admin.payment.setting.gateways' => 'Configure Payment Gateways',
                    'admin.payment.setting.manual'   => 'Configure Manual Payment Gateways',
                    'admin.system.setting.courier'   => 'Setup Steadfast / Pathao Courier APIs',
                    'admin.system.setting.fraud'     => 'Setup FraudBD Security API Credentials',
                    'admin.system.setting.smtp'      => 'Configure SMTP Email Server',
                    'admin.system.setting.sms'       => 'Configure SMS Gateway Credentials',
                    'admin.setting.general'          => 'General Store & Branding Settings',
                    'admin.ecommerce.setting.shipping' => 'Ecommerce Shipping Global Settings',
                    'admin.ecommerce.setting.company'  => 'Company Information & Invoice Meta',
                    'admin.banner.setting.main'      => 'Banner Setup - Main Banner',
                    'admin.banner.setting.campaign'  => 'Banner Setup - Campaign Banners',
                    'admin.banner.setting.shop'      => 'Banner Setup - Shop Page Banner',
                    'admin.frontend.setting.logo'    => 'Frontend Logo & Brand Favicon',
                    'admin.frontend.setting.about'   => 'About Us Page Content',
                    'admin.frontend.setting.contact' => 'Contact Us Page Content',
                    'admin.frontend.setting.faq'     => 'Frequently Asked Questions (FAQ)',
                    'admin.seo.index'                => 'Search Engine Optimization (SEO)',
                ]
            ]
        ];
    }

    /**
     * রাউটের নাম থেকে CRUD অ্যাকশন টাইপ নির্ণয়[cite: 63]
     */
    public static function detectActionType(string $routeName): string
    {
        $r = strtolower($routeName);
        if (str_contains($r, 'destroy') || str_contains($r, 'delete') || str_contains($r, 'force_delete') || str_contains($r, 'cleanup')) {
            return 'delete';
        }
        if (str_contains($r, 'create') || str_contains($r, 'store') || str_contains($r, 'upload') || str_contains($r, 'booking') || str_contains($r, 'new.assign') || str_contains($r, 'assign.store')) {
            return 'create';
        }
        if (str_contains($r, 'edit') || str_contains($r, 'update') || str_contains($r, 'toggle') || str_contains($r, 'status') || str_contains($r, 'reply') || str_contains($r, 'reorder') || str_contains($r, 'reset') || str_contains($r, 'replace')) {
            return 'update';
        }
        return 'read';
    }

    /**
     * ১. সকল রোলের তালিকা (অটো-ক্লিনআপ ও ডি-ডুপ্লিকেশন সহ)[cite: 63]
     */
    public function index()
    {
        // ব্যাকগ্রাউন্ডে বিদ্যমান হাইফেন ডুপ্লিকেট রোলস অটো-মার্জ ও ক্লিনআপ
        self::autoCleanupDuplicateRoles();

        $defaultRoles = collect(['super_admin', 'admin', 'branch_manager', 'staff']);
        
        $dbRoles = collect();
        if (Schema::hasTable('role_route_permissions')) {
            $dbRoles = DB::table('role_route_permissions')->distinct()->pluck('role_name');
        }
        if (Schema::hasTable('roles')) {
            $spatieRoles = DB::table('roles')->pluck('name');
            $dbRoles = $dbRoles->merge($spatieRoles);
        }

        // হাইফেন ও স্পেসকে আন্ডারস্কোরে কনভার্ট করে নিখুঁত ইউনিক রোল তালিকা তৈরি
        $allRoles = $defaultRoles->merge($dbRoles)
            ->filter(fn($r) => !empty($r) && strtolower(trim((string)$r)) !== 'customer')
            ->map(fn($r) => str_replace(['-', ' '], '_', strtolower(trim((string)$r))))
            ->unique()
            ->values();

        $roleStats = [];
        foreach ($allRoles as $rName) {
            $userCount = DB::table('users')
                ->where(function ($q) use ($rName) {
                    $hyphen = str_replace('_', '-', $rName);
                    $space  = str_replace('_', ' ', $rName);
                    $q->where('role', $rName)
                      ->orWhere('role', $hyphen)
                      ->orWhere('role', $space);
                })
                ->whereNull('deleted_at')
                ->count();
            
            $permCount = 0;
            if (Schema::hasTable('role_route_permissions')) {
                $permCount = DB::table('role_route_permissions')
                    ->where(function ($q) use ($rName) {
                        $hyphen = str_replace('_', '-', $rName);
                        $space  = str_replace('_', ' ', $rName);
                        $q->where('role_name', $rName)
                          ->orWhere('role_name', $hyphen)
                          ->orWhere('role_name', $space);
                    })
                    ->where(function ($q) {
                        $q->where('can_read', 1)
                          ->orWhere('can_create', 1)
                          ->orWhere('can_update', 1)
                          ->orWhere('can_delete', 1);
                    })
                    ->count();
            }

            $roleStats[] = [
                'name'        => $rName,
                'display'     => ucwords(str_replace('_', ' ', $rName)),
                'users_count' => $userCount,
                'perms_count' => $permCount,
                'is_protected'=> ($rName === 'super_admin'),
            ];
        }

        return view('admin.roles.index', compact('roleStats'));
    }

    /**
     * ২. নতুন রোল তৈরির ফর্ম[cite: 63]
     */
    public function create()
    {
        $modules = self::getSystemModules();
        return view('admin.roles.create', compact('modules'));
    }

    /**
     * ৩. রোল ও পারমিশন সংরক্ষণ (ডুপ্লিকেট শিল্ড সহ)[cite: 63]
     */
    public function store(Request $request)
    {
        $request->validate([
            'role_name'   => 'required|string|max:50',
            'permissions' => 'nullable|array',
        ]);

        $rawName = trim($request->role_name);
        $roleSlug = Str::slug($rawName, '_');

        if ($roleSlug === 'customer') {
            return back()->withInput()->with('error', 'Customer রোলে অ্যাডমিন পারমিশন দেওয়া সম্ভব নয়।');
        }

        if ($roleSlug === 'super_admin' && !auth()->user()->isSuperAdmin()) {
            return back()->withInput()->with('error', 'শুধুমাত্র সুপার অ্যাডমিনরা সুপার অ্যাডমিন সুবিধা পরিবর্তন করতে পারেন।');
        }

        // ডুপ্লিকেট রোল প্রতিরোধ (স্পেস, ড্যাশ ও কেস ফিল্টার)
        $isEdit = $request->boolean('is_edit') || $request->filled('original_role');
        $originalRole = $request->input('original_role');

        $cleanTarget = strtolower(preg_replace('/[^a-z0-9]/', '', $rawName));

        $existingRoles = collect(['super_admin', 'admin', 'branch_manager', 'staff', 'customer']);
        if (Schema::hasTable('role_route_permissions')) {
            $existingRoles = $existingRoles->merge(DB::table('role_route_permissions')->pluck('role_name'));
        }
        if (Schema::hasTable('roles')) {
            $existingRoles = $existingRoles->merge(DB::table('roles')->pluck('name'));
        }

        $existingCleanMap = $existingRoles->unique()->mapWithKeys(function ($r) {
            return [strtolower(preg_replace('/[^a-z0-9]/', '', (string)$r)) => $r];
        });

        if (!$isEdit && $existingCleanMap->has($cleanTarget)) {
            return back()->withInput()->with('error', "The role '{$rawName}' already exists (even with different spaces or dashes)! Duplicate roles are strictly forbidden.");
        }

        if ($isEdit && $originalRole) {
            $cleanOriginal = strtolower(preg_replace('/[^a-z0-9]/', '', $originalRole));
            if ($cleanOriginal !== $cleanTarget && $existingCleanMap->has($cleanTarget)) {
                return back()->withInput()->with('error', "Another role named '{$rawName}' already exists! Please choose a unique name.");
            }
        }

        $submittedPermissions = $request->input('permissions', []);
        $systemModules = self::getSystemModules();

        // role_route_permissions টেবিল আপডেট
        if (Schema::hasTable('role_route_permissions')) {
            DB::table('role_route_permissions')
                ->where('role_name', $roleSlug)
                ->orWhere('role_name', $rawName)
                ->orWhere('role_name', str_replace('_', '-', $roleSlug))
                ->orWhere('role_name', str_replace('_', ' ', $roleSlug))
                ->delete();

            $insertData = [];

            foreach ($systemModules as $moduleTitle => $moduleInfo) {
                $moduleSlug = Str::slug($moduleTitle, '_');
                $moduleRoutes = array_keys($moduleInfo['permissions'] ?? []);

                $canCreate = 0;
                $canUpdate = 0;
                $canDelete = 0;
                $canRead   = 0;

                foreach ($moduleRoutes as $routeKey) {
                    if (in_array($routeKey, $submittedPermissions)) {
                        $action = self::detectActionType($routeKey);
                        if ($action === 'create') $canCreate = 1;
                        elseif ($action === 'update') $canUpdate = 1;
                        elseif ($action === 'delete') $canDelete = 1;
                        else $canRead = 1;
                    }
                }

                if ($canCreate || $canUpdate || $canDelete || $canRead) {
                    $insertData[] = [
                        'role_name'    => $roleSlug,
                        'route_module' => $moduleSlug,
                        'can_create'   => $canCreate,
                        'can_update'   => $canUpdate,
                        'can_delete'   => $canDelete,
                        'can_read'     => $canRead,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }
            }

            if (!empty($insertData)) {
                DB::table('role_route_permissions')->insert($insertData);
            }
        }

        // Spatie Role ও Permission সিঙ্ক
        if (Schema::hasTable('roles') && Schema::hasTable('permissions')) {
            try {
                $spatieRole = DB::table('roles')->where('name', $roleSlug)->first();
                if (!$spatieRole) {
                    $roleId = DB::table('roles')->insertGetId([
                        'name'       => $roleSlug,
                        'guard_name' => 'web',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $roleId = $spatieRole->id;
                }

                if (Schema::hasTable('role_has_permissions')) {
                    DB::table('role_has_permissions')->where('role_id', $roleId)->delete();
                    foreach ($submittedPermissions as $pName) {
                        $pRow = DB::table('permissions')->where('name', $pName)->first();
                        if (!$pRow) {
                            $pId = DB::table('permissions')->insertGetId([
                                'name'       => $pName,
                                'guard_name' => 'web',
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        } else {
                            $pId = $pRow->id;
                        }

                        DB::table('role_has_permissions')->insert([
                            'permission_id' => $pId,
                            'role_id'       => $roleId
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Spatie fallback ignore
            }
        }

        Cache::flush();

        return redirect()->route('admin.roles.index')->with('success', "Permissions for role [{$rawName}] updated and applied instantly!");
    }

    /**
     * ৪. রোলের পারমিশন সম্পাদনা পেজ[cite: 63]
     */
    public function edit(Request $request, $roleName)
    {
        $roleSlug = Str::slug($roleName, '_');

        if ($request->isMethod('post') || $request->isMethod('put')) {
            $request->merge(['role_name' => $roleSlug, 'is_edit' => true, 'original_role' => $roleName]);
            return $this->store($request);
        }

        if ($roleSlug === 'customer') {
            return redirect()->route('admin.roles.index')->with('error', 'Customer accounts cannot have staff permissions.');
        }

        $modules = self::getSystemModules();
        $assignedPermissions = collect();

        // role_route_permissions থেকে বিদ্যমান সক্রিয় পারমিশন রিড
        if (Schema::hasTable('role_route_permissions')) {
            $records = DB::table('role_route_permissions')
                ->where('role_name', $roleSlug)
                ->orWhere('role_name', str_replace('_', '-', $roleSlug))
                ->orWhere('role_name', str_replace('_', ' ', $roleSlug))
                ->orWhere('role_name', $roleName)
                ->get();

            $matchedRoutes = [];
            foreach ($records as $row) {
                $targetModuleSlug = $row->route_module;
                foreach ($modules as $moduleTitle => $moduleInfo) {
                    if (Str::slug($moduleTitle, '_') === $targetModuleSlug) {
                        foreach (array_keys($moduleInfo['permissions'] ?? []) as $rKey) {
                            $act = self::detectActionType($rKey);
                            if ($act === 'create' && !empty($row->can_create)) {
                                $matchedRoutes[] = $rKey;
                            } elseif ($act === 'update' && !empty($row->can_update)) {
                                $matchedRoutes[] = $rKey;
                            } elseif ($act === 'delete' && !empty($row->can_delete)) {
                                $matchedRoutes[] = $rKey;
                            } elseif ($act === 'read' && !empty($row->can_read)) {
                                $matchedRoutes[] = $rKey;
                            }
                        }
                    }
                }
            }
            $assignedPermissions = collect(array_unique($matchedRoutes));
        }

        // Spatie role_has_permissions থেকেও চেক মার্জ করা
        if (Schema::hasTable('role_has_permissions') && Schema::hasTable('roles') && Schema::hasTable('permissions')) {
            $roleRow = DB::table('roles')->where('name', $roleSlug)->orWhere('name', $roleName)->first();
            if ($roleRow) {
                $spatiePerms = DB::table('role_has_permissions')
                    ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
                    ->where('role_has_permissions.role_id', $roleRow->id)
                    ->pluck('permissions.name');
                $assignedPermissions = $assignedPermissions->merge($spatiePerms)->unique();
            }
        }

        return view('admin.roles.edit', compact('roleSlug', 'roleName', 'modules', 'assignedPermissions'));
    }

    /**
     * ৫. রোল ডিলিট[cite: 63]
     */
    public function destroy($roleName)
    {
        $roleSlug = Str::slug($roleName, '_');

        if (in_array($roleSlug, ['super_admin', 'admin', 'customer'])) {
            return back()->with('error', "Core system role [{$roleSlug}] cannot be deleted.");
        }

        $activeUserCount = DB::table('users')
            ->where(function ($q) use ($roleSlug, $roleName) {
                $q->where('role', $roleSlug)
                  ->orWhere('role', $roleName)
                  ->orWhere('role', str_replace('_', '-', $roleSlug))
                  ->orWhere('role', str_replace('_', ' ', $roleSlug));
            })
            ->whereNull('deleted_at')
            ->count();

        if ($activeUserCount > 0) {
            return back()->with('error', "Cannot delete role [{$roleSlug}]. There are {$activeUserCount} active user(s) assigned to this role.");
        }

        if (Schema::hasTable('role_route_permissions')) {
            DB::table('role_route_permissions')
                ->where('role_name', $roleSlug)
                ->orWhere('role_name', $roleName)
                ->orWhere('role_name', str_replace('_', '-', $roleSlug))
                ->orWhere('role_name', str_replace('_', ' ', $roleSlug))
                ->delete();
        }

        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('name', $roleSlug)->orWhere('name', $roleName)->delete();
        }

        Cache::flush();

        return redirect()->route('admin.roles.index')->with('success', "Role [{$roleSlug}] deleted successfully.");
    }
}