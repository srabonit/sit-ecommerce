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
     * Explicit Canonical Route to Module Slug Mapper (Prevents Mismatch & False 403s across all 27 Modules)
     */
    public static function resolveModuleSlugForRoute(string $routeName): string
    {
        $prefixMap = [
            'admin.sms.'               => 'sms_marketing_gateways',
            'admin.email_marketing.'   => 'advanced_email_marketing_system',
            'admin.inventory.'         => 'inventory_management',
            'admin.tax.'               => 'enterprise_tax_vat_nbr_mushak_6_3',
            'admin.security.'          => 'cyber_security_threat_prevention',
            'admin.transactions.'      => 'payment_transactions_verification',
            'admin.procurement'        => 'procurement_supply_chain',
            'admin.suppliers.'         => 'procurement_supply_chain',
            'admin.purchase-orders.'   => 'procurement_supply_chain',
            'admin.purchases.'         => 'procurement_supply_chain',
            'admin.purchase-receipts.' => 'procurement_supply_chain',
            'admin.supplier-payments.' => 'procurement_supply_chain',
            'admin.purchase-returns.'  => 'procurement_supply_chain',
            'admin.supplier-exchanges.'=> 'procurement_supply_chain',
            'admin.supplier-debit-credit-notes.' => 'procurement_supply_chain',
            'admin.procurement-assets.'=> 'procurement_supply_chain',
            'admin.order.'             => 'order_management',
            'admin.courier.'           => 'courier_logistics',
            'admin.pos.'               => 'pos_terminal',
            'admin.product.reviews.'   => 'product_reviews',
            'admin.review.'            => 'product_reviews',
            'admin.product.'           => 'product_management',
            'admin.products.'          => 'product_management',
            'admin.branch.'            => 'branch_hubs',
            'admin.accounts.'          => 'accounts_ledger',
            'admin.report.'            => 'reports_audit',
            'admin.promo.coupons.'     => 'promo_campaigns',
            'admin.promo.'             => 'promo_campaigns',
            'admin.forms.'             => 'dynamic_form_builder',
            'admin.content.'           => 'site_content_cms',
            'admin.shipping.'          => 'shipping_configuration',
            'admin.theme.setting.'     => 'theme_storefront',
            'admin.roles.'             => 'users_roles_rbac_security',
            'admin.sessions.'          => 'live_user_sessions',
            'admin.activity.'          => 'activity_logs_audit_trail',
            'admin.support.'           => 'customer_support_contacts',
            'admin.contact.'           => 'customer_support_contacts',
            'admin.media.'             => 'media_library',
            'admin.setting.'           => 'system_gateway_settings',
            'admin.settings.auth.'     => 'system_gateway_settings',
            'admin.payment.setting.'   => 'system_gateway_settings',
            'admin.system.setting.'    => 'system_gateway_settings',
            'admin.ecommerce.setting.' => 'system_gateway_settings',
            'admin.banner.setting.'    => 'system_gateway_settings',
            'admin.frontend.setting.'  => 'system_gateway_settings',
            'admin.seo.'               => 'system_gateway_settings',
            'admin.dashboard'          => 'dashboard',
            'admin.search.ajax'        => 'dashboard',
        ];

        foreach ($prefixMap as $prefix => $slug) {
            if (str_starts_with($routeName, $prefix)) {
                return $slug;
            }
        }

        $parts = explode('.', $routeName);
        return count($parts) >= 2 ? Str::slug($parts[1], '_') : 'dashboard';
    }

    /**
     * Automatic Companion Routes Extender (Binds AJAX, Modals & Store/Update across all modules)
     */
    public static function getImplicitCompanionRoutes(array $submittedPermissions): array
    {
        $expanded = $submittedPermissions;

        foreach ($submittedPermissions as $perm) {
            // 1. SMS Module Companions
            if ($perm === 'admin.sms.campaigns.create') {
                $expanded[] = 'admin.sms.campaigns.store';
                $expanded[] = 'admin.sms.campaigns.filter_recipients_ajax';
            }
            if ($perm === 'admin.sms.campaigns.index') {
                $expanded[] = 'admin.sms.campaigns.show';
                $expanded[] = 'admin.sms.campaigns.filter_recipients_ajax';
            }
            if ($perm === 'admin.sms.logs.index') {
                $expanded[] = 'admin.sms.logs.show';
                $expanded[] = 'admin.sms.logs.check_status';
            }
            if ($perm === 'admin.sms.templates.create') {
                $expanded[] = 'admin.sms.templates.store';
                $expanded[] = 'admin.sms.templates.preview_metrics';
            }
            if ($perm === 'admin.sms.templates.edit') {
                $expanded[] = 'admin.sms.templates.update';
                $expanded[] = 'admin.sms.templates.preview_metrics';
            }

            // 2. Order Module Companions
            if ($perm === 'admin.order.create_manual') {
                $expanded[] = 'admin.order.store_manual';
                $expanded[] = 'admin.order.search_products';
                $expanded[] = 'admin.order.search_customers';
            }
            if ($perm === 'admin.order.online') {
                $expanded[] = 'admin.order.view';
                $expanded[] = 'admin.order.invoice.print';
                $expanded[] = 'admin.order.invoice';
            }
            if ($perm === 'admin.order.status.update') {
                $expanded[] = 'admin.order.update_status';
            }
            if ($perm === 'admin.order.courier.send') {
                $expanded[] = 'admin.order.send_courier';
            }

            // 3. Inventory Module Companions
            if ($perm === 'admin.inventory.stock_in') {
                $expanded[] = 'admin.inventory.stock_in.store';
                $expanded[] = 'admin.inventory.search.products.ajax';
            }
            if ($perm === 'admin.inventory.stock_out') {
                $expanded[] = 'admin.inventory.stock_out.store';
                $expanded[] = 'admin.inventory.search.products.ajax';
            }
            if ($perm === 'admin.inventory.balance') {
                $expanded[] = 'admin.inventory.product.details';
            }

            // 4. Procurement Module Companions
            if ($perm === 'admin.purchase-orders.create') {
                $expanded[] = 'admin.purchase-orders.store';
                $expanded[] = 'admin.purchase-orders.search-products';
                $expanded[] = 'admin.purchase-orders.find-by-sku';
                $expanded[] = 'admin.purchase-orders.quick-store-product';
            }
            if ($perm === 'admin.purchase-orders.index') {
                $expanded[] = 'admin.purchase-orders.show';
            }
            if ($perm === 'admin.purchases.create') {
                $expanded[] = 'admin.purchases.store';
                $expanded[] = 'admin.purchases.po-items';
            }
            if ($perm === 'admin.purchases.index') {
                $expanded[] = 'admin.purchases.show';
            }
            if ($perm === 'admin.suppliers.index') {
                $expanded[] = 'admin.suppliers.show';
                $expanded[] = 'admin.suppliers.ledger';
            }
            if ($perm === 'admin.supplier-payments.create') {
                $expanded[] = 'admin.supplier-payments.store';
                $expanded[] = 'admin.supplier-payments.unpaid-purchases';
            }

            // 5. Transactions Module Companions
            if ($perm === 'admin.transactions.index') {
                $expanded[] = 'admin.transactions.show';
            }
            if ($perm === 'admin.transactions.manual') {
                $expanded[] = 'admin.transactions.search_order';
                $expanded[] = 'admin.transactions.store_manual';
            }

            // 6. Email Marketing Companions
            if ($perm === 'admin.email_marketing.campaigns.create') {
                $expanded[] = 'admin.email_marketing.campaigns.store';
                $expanded[] = 'admin.email_marketing.templates.preview';
                $expanded[] = 'admin.email_marketing.segments.preview_count';
            }
            if ($perm === 'admin.email_marketing.campaigns.index') {
                $expanded[] = 'admin.email_marketing.campaigns.show';
                $expanded[] = 'admin.email_marketing.campaigns.recipients';
            }
            if ($perm === 'admin.email_marketing.templates.create') {
                $expanded[] = 'admin.email_marketing.templates.store';
                $expanded[] = 'admin.email_marketing.templates.preview';
            }
            if ($perm === 'admin.email_marketing.templates.edit') {
                $expanded[] = 'admin.email_marketing.templates.update';
                $expanded[] = 'admin.email_marketing.templates.preview';
            }

            // 7. General CRUD Action Companions
            if (str_ends_with($perm, '.create')) {
                $expanded[] = substr($perm, 0, -7) . '.store';
            }
            if (str_ends_with($perm, '.edit')) {
                $expanded[] = substr($perm, 0, -5) . '.update';
            }
        }

        return array_values(array_unique(array_filter($expanded)));
    }

    /**
     * সিস্টেমের সকল মডিউল ও তাদের রাউট-পারমিশন ডেফিনিশন (১০০% সম্পূর্ণ তালিকা)
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
                    'admin.order.online'                 => 'View All Online Orders List',
                    'admin.order.view'                   => 'View Order Details & Full Invoice',
                    'admin.order.create_manual'          => 'Create Manual Order (Phone/FB/WhatsApp)',
                    'admin.order.store_manual'           => 'Save & Process Manual Order',
                    'admin.order.status.update'          => 'Update Order Processing Status',
                    'admin.order.send_manual_sms'        => 'Send Manual SMS to Customer',
                    'admin.order.courier.send'           => 'Send / Dispatch Order to Courier API',
                    'admin.order.invoice.print'          => 'Print Official Customer Invoice',
                    'admin.order.destroy'                => 'Delete Order Permanently',
                    'admin.order.bulk.action'            => 'Execute Bulk Order Operations',
                    'admin.order.incomplete'             => 'Manage Incomplete / Abandoned Orders',
                    'admin.order.return'                 => 'Manage Returned Parcel Records',
                    'admin.order.exchange'               => 'Manage Customer Exchange Orders',
                    'admin.order.process_return_exchange'=> 'Process Return, Exchange & Damaged Restock',
                    'admin.order.fraud'                  => 'Use FraudBD Customer Phone Checker',
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
            'Product Reviews' => [
                'icon'  => 'fas fa-star-half-stroke',
                'color' => 'warning',
                'permissions' => [
                    'admin.product.reviews.index'             => 'View All Customer Reviews',
                    'admin.product.reviews.dashboard'         => 'View Reviews Analytics Dashboard',
                    'admin.product.reviews.show'              => 'Inspect Review Details & Modal',
                    'admin.product.reviews.status'            => 'Approve / Reject Reviews',
                    'admin.product.reviews.toggle_visibility' => 'Hide / Unhide Reviews (Storefront)',
                    'admin.product.reviews.destroy'           => 'Delete Review Permanently',
                    'admin.product.reviews.bulk_action'       => 'Execute Bulk Review Actions',
                    'admin.product.reviews.reply'             => 'Post & Edit Admin Replies',
                    'admin.product.reviews.reply.delete'      => 'Delete Admin Reply',
                    'admin.product.reviews.toggle_pin'        => 'Pin / Unpin Reviews to Top',
                    'admin.product.reviews.pin_order'         => 'Reorder Pinned Reviews Display',
                    'admin.product.reviews.product'           => 'Manage Product-Wise Reviews & Settings',
                    'admin.product.reviews.customer'          => 'View Customer Review History',
                    'admin.product.reviews.auto_rules'        => 'Manage Auto-Reply Automation Rules',
                    'admin.product.reviews.settings'          => 'Configure Global Review Settings',
                    'admin.review.index'                      => 'Moderate Product Reviews (Legacy)',
                ]
            ],
            'Inventory Management' => [
                'icon'  => 'fas fa-warehouse',
                'color' => 'success',
                'permissions' => [
                    'admin.inventory.dashboard'          => 'View Inventory Analytics Dashboard',
                    'admin.inventory.balance'            => 'View Live Stock Balances & Quantities',
                    'admin.inventory.stock_in'           => 'Record Stock In (Add Stock to Inventory)',
                    'admin.inventory.stock_out'          => 'Record Stock Out (Deduct Stock / Damaged)',
                    'admin.inventory.movements'          => 'View Inventory Stock Movements Ledger',
                    'admin.inventory.reports'            => 'View Inventory Valuation & Stock Reports',
                    'admin.inventory.adjust'             => 'Execute Physical Stock Count Adjustments',
                    'admin.inventory.product.details'    => 'View Product Stock Card & Ledger History',
                    'admin.inventory.search.products.ajax'=> 'Live Product Search in Inventory',
                ]
            ],
            'Procurement & Supply Chain' => [
                'icon'  => 'fas fa-truck-ramp-box',
                'color' => 'dark',
                'permissions' => [
                    'admin.suppliers.index'                       => 'View Suppliers Directory & Ledger',
                    'admin.suppliers.create'                      => 'Register New Vendor / Supplier',
                    'admin.suppliers.edit'                        => 'Edit Supplier Profile & Commercial Terms',
                    'admin.suppliers.destroy'                     => 'Delete Supplier Record',
                    'admin.suppliers.ledger'                      => 'View Supplier Running Ledger & Statements',
                    'admin.purchase-orders.index'                 => 'View Purchase Requisitions & Orders',
                    'admin.purchase-orders.create'                => 'Create New Purchase Order (Requisition)',
                    'admin.purchase-orders.approve'               => 'Approve Purchase Orders (Management)',
                    'admin.purchase-orders.cancel'                => 'Cancel Purchase Orders',
                    'admin.purchase-orders.destroy'               => 'Delete Purchase Orders',
                    'admin.purchases.index'                       => 'View Vendor Purchase Invoices / Bills',
                    'admin.purchases.create'                      => 'Record Purchase Invoice & Vendor Bills',
                    'admin.purchase-receipts.index'               => 'Goods Received Notes (GRN Quality Inspection)',
                    'admin.purchase-receipts.create'              => 'Inspect & Receive Shipments into Inventory',
                    'admin.supplier-payments.index'               => 'Supplier Payment Vouchers & History',
                    'admin.supplier-payments.create'              => 'Disburse Payment / Advance to Supplier',
                    'admin.supplier-payments.cancel'              => 'Cancel Supplier Payment Voucher',
                    'admin.purchase-returns.index'                => 'Return to Vendor (RTV Records)',
                    'admin.purchase-returns.create'               => 'Issue Purchase Return Note',
                    'admin.purchase-returns.approve'              => 'Approve Vendor Return & Stock Deduction',
                    'admin.supplier-exchanges.index'              => 'Supplier Defective Replacement Swaps',
                    'admin.supplier-exchanges.create'             => 'Initiate Defective Product Exchange',
                    'admin.supplier-exchanges.approve'            => 'Approve Replacement Stock Exchange',
                    'admin.supplier-debit-credit-notes.index'     => 'Supplier Debit & Credit Notes',
                    'admin.supplier-debit-credit-notes.create'    => 'Issue Debit / Credit Adjustment Note',
                    'admin.supplier-debit-credit-notes.approve'   => 'Approve Debit / Credit Note',
                    'admin.procurement-assets.index'              => 'Fixed Asset Procurement & Registry',
                    'admin.procurement-assets.create'             => 'Capitalize / Register New Company Asset',
                    'admin.procurement-assets.transfer'           => 'Transfer Asset to Branch or Staff',
                    'admin.procurement-assets.depreciate'         => 'Post Asset Monthly/Yearly Depreciation',
                    'admin.procurement-assets.dispose'            => 'Dispose / Write-off Fixed Asset',
                ]
            ],
            'Payment Transactions & Verification' => [
                'icon'  => 'fas fa-money-bill-transfer',
                'color' => 'success',
                'permissions' => [
                    'admin.transactions.dashboard'    => 'View Transactions Summary Dashboard',
                    'admin.transactions.index'        => 'View All Payment Transactions Ledger',
                    'admin.transactions.checkout'     => 'Review Online Checkout Payment Proofs',
                    'admin.transactions.pos'          => 'View POS Cash & Terminal Transactions',
                    'admin.transactions.cod'          => 'View Cash On Delivery (COD) Collections',
                    'admin.transactions.manual'       => 'View Manual Mobile Banking Transactions',
                    'admin.transactions.unchecked'    => 'Unchecked Pending Payment Verifications',
                    'admin.transactions.verify'       => 'Verify / Approve / Reject Transactions',
                    'admin.transactions.store_manual' => 'Record Manual Payment Transaction',
                    'admin.transactions.history'      => 'View Verification Audit Log History',
                    'admin.transactions.reports'      => 'View Financial Transaction Reports',
                    'admin.transactions.destroy'      => 'Delete / Trash Payment Transaction',
                ]
            ],
            'Enterprise Tax & VAT (NBR Mushak 6.3)' => [
                'icon'  => 'fas fa-file-invoice-dollar',
                'color' => 'danger',
                'permissions' => [
                    'admin.tax.dashboard'          => 'View Tax & VAT Overview Dashboard',
                    'admin.tax.classes.index'      => 'Manage NBR Tax Classes (Standard/Exempt)',
                    'admin.tax.classes.store'      => 'Create New Tax Class',
                    'admin.tax.classes.update'     => 'Edit Tax Class',
                    'admin.tax.classes.destroy'    => 'Delete Tax Class',
                    'admin.tax.rules.index'        => 'Manage Tax Rules & Calculation Logic',
                    'admin.tax.rules.create'       => 'Create New Tax Rule',
                    'admin.tax.rules.update'       => 'Edit Tax Rule & Conditions',
                    'admin.tax.rules.destroy'      => 'Delete Tax Rule',
                    'admin.tax.payments.index'     => 'View Treasury Tax Challans & Payments',
                    'admin.tax.payments.store'     => 'Record Treasury Tax Payment Challan',
                    'admin.tax.payments.download'  => 'Download Treasury Challan Copy',
                    'admin.tax.documents.index'    => 'Generate Mushak 6.3 & VAT Documents',
                    'admin.tax.documents.store'    => 'Issue Official Mushak 6.3 Tax Invoice',
                    'admin.tax.documents.download' => 'Download Official Mushak 6.3 PDF',
                    'admin.tax.reports.index'      => 'View Tax Summary & Compliance Reports',
                    'admin.tax.reports.export'     => 'Export Tax Ledger to CSV / Excel',
                    'admin.tax.periods.index'      => 'Tax Filing Periods & Month Closing',
                    'admin.tax.periods.close'      => 'Close & Lock Monthly Tax Period',
                    'admin.tax.settings.index'     => 'Configure NBR Tax Rates & BIN Settings',
                    'admin.tax.audit_logs'         => 'View Tax Alteration & Audit Logs',
                ]
            ],
            'Cyber Security & Threat Prevention' => [
                'icon'  => 'fas fa-shield-virus',
                'color' => 'danger',
                'permissions' => [
                    'admin.security.dashboard'                => 'View Security Threat Center & Traffic',
                    'admin.security.ips.index'                => 'Live IP Traffic & Threat Detection',
                    'admin.security.ips.block'                => 'Block / Blacklist Suspicious IP',
                    'admin.security.ips.unblock'              => 'Unblock / Whitelist IP Address',
                    'admin.security.orders.index'             => 'Suspicious Order Radar & Fake Orders',
                    'admin.security.orders.clusters'          => 'Order Clusters & Collusion Detection',
                    'admin.security.orders.quick_block'       => 'Quick Block Fraudulent Customer',
                    'admin.security.urls.index'               => '404 / Exploit URL Attacking Monitor',
                    'admin.security.urls.block_all'           => 'Block All Attacking IPs on URL',
                    'admin.security.blocking.ips'             => 'Manage Blocked IP Addresses',
                    'admin.security.blocking.networks'        => 'Block Entire CIDR Network Ranges',
                    'admin.security.blocking.users'           => 'Manage Restricted Customer Accounts',
                    'admin.security.blocking.emergency_flush' => 'Emergency Firewall Cache Flush',
                    'admin.security.settings.index'           => 'Configure Firewall & Threshold Rules',
                    'admin.security.logs.index'               => 'Security Threat Logs & Evidence',
                    'admin.security.alerts.index'             => 'Security Alert Center Notifications',
                ]
            ],
            'SMS Marketing & Gateways' => [
                'icon'  => 'fas fa-comment-sms',
                'color' => 'info',
                'permissions' => [
                    'admin.sms.settings.index'        => 'SMS Gateway Provider & Balance Config',
                    'admin.sms.settings.test'         => 'Send Test SMS Message',
                    'admin.sms.settings.sync_balance' => 'Live Sync Gateway SMS Balance',
                    'admin.sms.templates.index'       => 'Manage Automated Notification Templates',
                    'admin.sms.templates.create'      => 'Create Custom SMS Template',
                    'admin.sms.templates.edit'        => 'Edit SMS Template & Placeholders',
                    'admin.sms.templates.destroy'     => 'Delete SMS Template',
                    'admin.sms.campaigns.index'       => 'View Bulk SMS Marketing Campaigns',
                    'admin.sms.campaigns.create'      => 'Create & Target Bulk SMS Campaign',
                    'admin.sms.campaigns.show'        => 'Inspect SMS Campaign Details & Recipients',
                    'admin.sms.campaigns.dispatch_now'=> 'Dispatch Scheduled SMS Campaign',
                    'admin.sms.campaigns.cancel'      => 'Cancel Pending SMS Campaign',
                    'admin.sms.logs.index'            => 'View Sent SMS History & Delivery Status',
                    'admin.sms.logs.show'             => 'Inspect SMS Delivery Log Details (Modal)',
                    'admin.sms.logs.resend'           => 'Resend Failed SMS Message',
                    'admin.sms.logs.bulk_delete'      => 'Delete Old SMS Delivery Logs',
                    'admin.sms.blacklist.index'       => 'Manage SMS DND & Number Blacklist',
                ]
            ],
            'Advanced Email Marketing System' => [
                'icon'  => 'fas fa-envelope-open-text',
                'color' => 'primary',
                'permissions' => [
                    'admin.email_marketing.dashboard'                   => 'View Email Marketing Performance',
                    'admin.email_marketing.subscribers.index'           => 'Manage Email Subscribers Directory',
                    'admin.email_marketing.subscribers.store'           => 'Add New Email Subscriber',
                    'admin.email_marketing.subscribers.bulk_send_instant'=> 'Send Instant Bulk Email Blast',
                    'admin.email_marketing.segments.index'              => 'Manage Smart Audience Segments',
                    'admin.email_marketing.segments.create'             => 'Create Audience Segment Filter',
                    'admin.email_marketing.campaigns.index'             => 'View Newsletter & Email Campaigns',
                    'admin.email_marketing.campaigns.create'            => 'Design & Schedule Email Campaign',
                    'admin.email_marketing.campaigns.show'              => 'View Campaign Details & Analytics',
                    'admin.email_marketing.campaigns.dispatch_now'      => 'Send Email Campaign Instantly',
                    'admin.email_marketing.campaigns.send_test'         => 'Send Test Email Preview',
                    'admin.email_marketing.templates.index'             => 'Drag & Drop HTML Template Builder',
                    'admin.email_marketing.templates.create'            => 'Create Email Template Design',
                    'admin.email_marketing.automations.index'           => 'Manage Event Drip Automations',
                    'admin.email_marketing.automations.create'          => 'Setup Automated Trigger Flow',
                    'admin.email_marketing.cart_wishlist.index'         => 'Abandoned Cart Recovery Center',
                    'admin.email_marketing.cart_wishlist.send_recovery' => 'Send Direct Cart Recovery Email',
                    'admin.email_marketing.product_marketing'           => 'Targeted Product Promo Blasts',
                    'admin.email_marketing.analytics'                   => 'Campaign Open & Click Analytics',
                    'admin.email_marketing.ab_testing'                  => 'Run A/B Subject & Template Tests',
                    'admin.email_marketing.suppression'                 => 'Manage Suppressed & Bounced Emails',
                    'admin.email_marketing.providers'                   => 'Setup SMTP & ESP Mailing Servers',
                    'admin.email_marketing.settings'                    => 'Sender Identity & Unsubscribe Settings',
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
                    'admin.branch.assign.details' => 'View Stock Transfer Consignment Details',
                    'admin.branch.products'       => 'View Branch Stock Levels',
                    'admin.branch.stock.update'   => 'Update Product Branch Stock Manually',
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
                    'admin.accounts.methods'              => 'View Payment Methods Ledger',
                    'admin.accounts.transactions'         => 'View All Financial Transactions',
                    'admin.accounts.ledger.index'         => 'General Financial Account Ledger',
                    'admin.accounts.ledger.export'        => 'Export Financial Ledger to CSV',
                    'admin.accounts.expenses.index'       => 'Manage Operating Expenses',
                    'admin.accounts.expenses.store'       => 'Record New Expense Voucher',
                    'admin.accounts.expenses.destroy'     => 'Delete Expense Record',
                    'admin.accounts.income.index'         => 'Manage Store Other Incomes',
                    'admin.accounts.income.store'         => 'Record Store Income',
                    'admin.accounts.income.destroy'       => 'Delete Income Record',
                    'admin.accounts.bank.index'           => 'Manage Bank Accounts & Cash Drawers',
                    'admin.accounts.bank.account.store'   => 'Create New Bank / Cash Account',
                    'admin.accounts.bank.transfer'        => 'Transfer Funds Between Accounts',
                    'admin.accounts.bank.deposit'         => 'Deposit Funds to Bank',
                    'admin.accounts.bank.statement'       => 'View Detailed Bank Statement',
                    'admin.accounts.investment.index'     => 'Manage Investor Capital & Equity',
                    'admin.accounts.investment.store'     => 'Record Capital Investment',
                    'admin.accounts.assets.index'         => 'Manage Business Tangible Assets',
                    'admin.accounts.assets.store'         => 'Record New Company Asset',
                ]
            ],
            'Reports & Audit' => [
                'icon'  => 'fas fa-chart-line',
                'color' => 'primary',
                'permissions' => [
                    'admin.report.sales'               => 'View Analytical Sales Reports',
                    'admin.report.stock'               => 'View Stock Inventory Reports',
                    'admin.report.purchase'            => 'View Purchase & Expense Reports',
                    'admin.report.profit_loss'         => 'View Net Profit & Loss (P&L) Statement',
                    'admin.report.cogs'                => 'View Cost of Goods Sold (COGS) Analysis',
                    'admin.report.export_pnl'          => 'Export Profit & Loss Statement',
                    'admin.report.balance_sheet'       => 'View Comprehensive Balance Sheet',
                    'admin.report.export_balance_sheet'=> 'Export Balance Sheet to Excel',
                    'admin.report.cash_flow'           => 'View Cash Flow Statement',
                    'admin.report.export_cash_flow'    => 'Export Cash Flow Statement',
                ]
            ],
            'Customer Support & Contacts' => [
                'icon'  => 'fas fa-headset',
                'color' => 'info',
                'permissions' => [
                    'admin.support.index'    => 'View Customer Support Tickets',
                    'admin.support.show'     => 'Read & Reply to Support Tickets',
                    'admin.contact.customer' => 'View Registered Customers Directory',
                    'admin.contact.supplier' => 'View Vendor & Suppliers Directory',
                ]
            ],
            'Promo & Campaigns' => [
                'icon'  => 'fas fa-tags',
                'color' => 'danger',
                'permissions' => [
                    'admin.promo.flashsale'                 => 'Configure Flash Sale Campaigns',
                    'admin.promo.coupons.index'             => 'Manage Discount Coupons List',
                    'admin.promo.coupons.create'            => 'Create New Store Coupon',
                    'admin.promo.coupons.edit'              => 'Edit Coupon Rules & Discounts',
                    'admin.promo.coupons.destroy'           => 'Delete Discount Coupon',
                    'admin.promo.coupons.vouchers.create'   => 'Issue Customer Wallet Vouchers',
                    'admin.promo.coupons.vouchers.edit'     => 'Edit Customer Vouchers',
                    'admin.promo.coupons.vouchers.destroy'  => 'Delete Customer Voucher',
                ]
            ],
            'Dynamic Form Builder' => [
                'icon'  => 'fas fa-table-list',
                'color' => 'secondary',
                'permissions' => [
                    'admin.forms.index'               => 'View All Dynamic Custom Forms',
                    'admin.forms.create'              => 'Create New Custom Forms',
                    'admin.forms.edit'                => 'Build & Edit Custom Form Fields',
                    'admin.forms.destroy'             => 'Delete Custom Forms',
                    'admin.forms.submissions.index'   => 'View Form Leads & Customer Submissions',
                    'admin.forms.submissions.show'    => 'Inspect Individual Form Submission',
                    'admin.forms.submissions.reply'   => 'Reply to Form Submissions via Email',
                    'admin.forms.submissions.destroy' => 'Delete Form Submission',
                ]
            ],
            'Site Content & CMS' => [
                'icon'  => 'fas fa-newspaper',
                'color' => 'primary',
                'permissions' => [
                    'admin.content.pages.index'                    => 'Manage Custom CMS Pages',
                    'admin.content.pages.create'                   => 'Create New CMS Page',
                    'admin.content.pages.edit'                     => 'Edit Custom CMS Page',
                    'admin.content.pages.destroy'                  => 'Delete Custom CMS Page',
                    'admin.content.blogs.index'                    => 'Manage Blog Articles & Posts',
                    'admin.content.blogs.create'                   => 'Write & Publish New Blog Post',
                    'admin.content.blogs.edit'                     => 'Edit Blog Post Content',
                    'admin.content.blogs.destroy'                  => 'Delete Blog Post',
                    'admin.content.blogs.comments'                 => 'Moderate Blog Post Comments',
                    'admin.content.blogs.comments.toggleAutoApprove'=> 'Toggle Auto-Approve Blog Comments',
                    'admin.content.menus.index'                    => 'Configure Header & Footer Menus',
                    'admin.content.widgets.index'                  => 'Manage Storefront Content Widgets',
                ]
            ],
            'Shipping Configuration' => [
                'icon'  => 'fas fa-boxes-packing',
                'color' => 'warning',
                'permissions' => [
                    'admin.shipping.index'         => 'Manage Shipping Delivery Zones',
                    'admin.shipping.zone.create'   => 'Create New Shipping Zone',
                    'admin.shipping.zone.edit'     => 'Edit Shipping Zone Rates',
                    'admin.shipping.zone.destroy'  => 'Delete Shipping Zone',
                    'admin.shipping.classes'       => 'Manage Weight-Based Shipping Classes',
                    'admin.shipping.pickup'        => 'Manage Local Store Pickup Hubs',
                    'admin.shipping.settings'      => 'General Shipping Calculations & Rules',
                ]
            ],
            'Theme & Storefront' => [
                'icon'  => 'fas fa-paintbrush',
                'color' => 'success',
                'permissions' => [
                    'admin.theme.setting.home_builder'                   => 'Drag & Drop Homepage Builder',
                    'admin.theme.setting.featured_products'              => 'Configure Featured Products',
                    'admin.theme.setting.slider'                         => 'Configure Hero Banner Sliders',
                    'admin.theme.setting.flash_sale'                     => 'Hero Slider & Flash Cards Setup',
                    'admin.theme.setting.popular_categories'             => 'Popular Category Slider Setup',
                    'admin.theme.setting.topbar'                         => 'Storefront Topbar Configuration',
                    'admin.theme.setting.colors'                         => 'Customize Theme Brand Colors',
                    'admin.theme.setting.extended_colors'                => 'Extended Palette & UI Color Control',
                    'admin.theme.setting.footer'                         => 'Storefront Footer Builder',
                    'admin.theme.setting.product_card'                   => 'Product Grid Card Display Mode',
                    'admin.theme.setting.cart_features'                  => 'Cart & Instant Checkout Features',
                    'admin.theme.setting.display.index'                  => 'Central Dynamic Display & Popups Engine',
                    'admin.theme.setting.display.popup.create'           => 'Create Marketing Popups & Notice',
                    'admin.theme.setting.display.popup.edit'             => 'Edit Marketing Popups',
                    'admin.theme.setting.display.popup.destroy'          => 'Delete Marketing Popup',
                    'admin.theme.setting.display.sticky_bar_rule.update' => 'Configure Storefront Sticky Bars',
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
                    'admin.roles.users.create_customer'=> 'Register Customer Account',
                    'admin.roles.users.destroy'      => 'Move User to Trash',
                    'admin.roles.users.reset_password'=> 'Reset Staff Password',
                    'admin.roles.users.force_logout' => 'Force Terminate Staff Session',
                    'admin.roles.users.unlock'       => 'Unlock Locked Staff Account',
                    'admin.roles.trash'              => 'View & Restore Trashed Accounts',
                    'admin.roles.trash.force_delete' => 'Permanently Delete Accounts',
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
                    'admin.settings.auth.index'        => 'Central Dynamic Authentication & 2FA Settings',
                    'admin.payment.setting.gateways'   => 'Configure Payment Gateways (bKash/SSL/Stripe)',
                    'admin.payment.setting.manual'     => 'Configure Manual Payment Gateways (Bangla QR)',
                    'admin.system.setting.courier'     => 'Setup Steadfast / Pathao Courier APIs',
                    'admin.system.setting.fraud'       => 'Setup FraudBD Security API Credentials',
                    'admin.system.setting.smtp'        => 'Configure SMTP Email Server',
                    'admin.system.setting.sms'         => 'Configure SMS Gateway Credentials',
                    'admin.setting.general'            => 'General Store & Branding Settings',
                    'admin.ecommerce.setting.shipping' => 'Ecommerce Shipping Global Settings',
                    'admin.ecommerce.setting.company'  => 'Company Information & Invoice Meta',
                    'admin.banner.setting.main'        => 'Banner Setup - Main Banner',
                    'admin.banner.setting.campaign'    => 'Banner Setup - Campaign Banners',
                    'admin.banner.setting.shop'        => 'Banner Setup - Shop Page Banner',
                    'admin.frontend.setting.logo'      => 'Frontend Logo & Brand Favicon',
                    'admin.frontend.setting.about'     => 'About Us Page Content',
                    'admin.frontend.setting.contact'   => 'Contact Us Page Content',
                    'admin.frontend.setting.faq'       => 'Frequently Asked Questions (FAQ)',
                    'admin.seo.index'                  => 'Search Engine Optimization (SEO)',
                ]
            ]
        ];
    }

    /**
     * রাউটের নাম থেকে CRUD অ্যাকশন টাইপ নির্ণয়
     */
    public static function detectActionType(string $routeName): string
    {
        $r = strtolower($routeName);
        if (str_contains($r, 'destroy') || str_contains($r, 'delete') || str_contains($r, 'force_delete') || str_contains($r, 'cleanup') || str_contains($r, 'revoke') || str_contains($r, 'dispose')) {
            return 'delete';
        }
        if (str_contains($r, 'create') || str_contains($r, 'store') || str_contains($r, 'upload') || str_contains($r, 'booking') || str_contains($r, 'new.assign') || str_contains($r, 'assign.store') || str_contains($r, 'deposit') || str_contains($r, 'transfer') || str_contains($r, 'close') || str_contains($r, 'send') || str_contains($r, 'dispatch')) {
            return 'create';
        }
        if (str_contains($r, 'edit') || str_contains($r, 'update') || str_contains($r, 'toggle') || str_contains($r, 'status') || str_contains($r, 'reply') || str_contains($r, 'reorder') || str_contains($r, 'reset') || str_contains($r, 'replace') || str_contains($r, 'pin') || str_contains($r, 'bulk') || str_contains($r, 'approve') || str_contains($r, 'cancel') || str_contains($r, 'adjust') || str_contains($r, 'depreciate') || str_contains($r, 'allocate') || str_contains($r, 'unlock') || str_contains($r, 'verify') || str_contains($r, 'mark_read') || str_contains($r, 'pause') || str_contains($r, 'resume') || str_contains($r, 'unblock') || str_contains($r, 'lift') || str_contains($r, 'block')) {
            return 'update';
        }
        return 'read';
    }

    /**
     * ১. সকল রোলের তালিকা
     */
    public function index()
    {
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
     * ২. নতুন রোল তৈরির ফর্ম
     */
    public function create()
    {
        $modules = self::getSystemModules();
        return view('admin.roles.create', compact('modules'));
    }

    /**
     * ৩. রোল ও পারমিশন সংরক্ষণ (কম্প্যানিয়ন এক্সপেনশন সহ)
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
            return back()->withInput()->with('error', "The role '{$rawName}' already exists! Duplicate roles are strictly forbidden.");
        }

        if ($isEdit && $originalRole) {
            $cleanOriginal = strtolower(preg_replace('/[^a-z0-9]/', '', $originalRole));
            if ($cleanOriginal !== $cleanTarget && $existingCleanMap->has($cleanTarget)) {
                return back()->withInput()->with('error', "Another role named '{$rawName}' already exists! Please choose a unique name.");
            }
        }

        $submittedPermissions = $request->input('permissions', []);
        
        // Auto-expand explicit permissions with their mandatory companion routes (AJAX, Modals, Store/Update)
        $expandedPermissions = self::getImplicitCompanionRoutes($submittedPermissions);
        $systemModules = self::getSystemModules();

        // ১. role_route_permissions টেবিল আপডেট
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
                    if (in_array($routeKey, $expandedPermissions)) {
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

        // ২. Spatie Role ও Permission সিঙ্ক
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
                    foreach ($expandedPermissions as $pName) {
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
     * ৪. রোলের পারমিশন সম্পাদনা পেজ
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

        // Spatie role_has_permissions থেকে রুট রিড
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
            $assignedPermissions = $assignedPermissions->merge($matchedRoutes)->unique();
        }

        return view('admin.roles.edit', compact('roleSlug', 'roleName', 'modules', 'assignedPermissions'));
    }

    /**
     * ৫. রোল ডিলিট
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