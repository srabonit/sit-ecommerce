<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DynamicPopup;
use App\Models\FloatingWidget;
use App\Models\DisplayCampaignRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class DynamicDisplayController extends Controller
{
    /**
     * Central safe redirect helper to avoid RouteNotFoundException
     */
    protected function redirectToIndex(string $message = '', string $type = 'success')
    {
        $routeName = Route::has('admin.theme.setting.display.index')
            ? 'admin.theme.setting.display.index'
            : (Route::has('admin.theme.display.index') ? 'admin.theme.display.index' : null);

        if ($routeName) {
            return redirect()->route($routeName)->with($type, $message);
        }

        return redirect('admin/theme-setting/display')->with($type, $message);
    }

    /**
     * Ensure floating_widgets table has settings column safely
     */
    protected function ensureWidgetSettingsColumn()
    {
        try {
            if (Schema::hasTable('floating_widgets') && !Schema::hasColumn('floating_widgets', 'settings')) {
                Schema::table('floating_widgets', function ($table) {
                    $table->json('settings')->nullable();
                });
            }
        } catch (\Throwable $e) {
            // Silently pass if column exists or schema alteration isn't permitted
        }
    }

    /**
     * Ensure display_campaign_rules table has settings column safely
     */
    protected function ensureCampaignRuleSettingsColumn()
    {
        try {
            if (Schema::hasTable('display_campaign_rules') && !Schema::hasColumn('display_campaign_rules', 'settings')) {
                Schema::table('display_campaign_rules', function ($table) {
                    $table->json('settings')->nullable();
                });
            }
        } catch (\Throwable $e) {
            // Silently pass
        }
    }

    /**
     * Display central hub for Dynamic Displays (Popups, Floating Widgets & Widget Hub Settings)
     */
    public function index()
    {
        $popups  = DynamicPopup::with('rule')->latest('id')->paginate(10, ['*'], 'popups_page');
        $widgets = FloatingWidget::with('rule')->orderBy('order_index', 'asc')->get();

        // Fetch display style settings for Floating Widgets (Speed-Dial vs Direct Stack)
        $widgetHubRule = DisplayCampaignRule::where('rule_scope', 'floating_widget_hub')->first();

        return view('admin.theme.dynamic_display.index', compact('popups', 'widgets', 'widgetHubRule'));
    }

    // ==========================================
    // 1. DYNAMIC POPUPS MANAGEMENT
    // ==========================================

    public function popupCreate()
    {
        return view('admin.theme.dynamic_display.popup_create');
    }

    public function popupStore(Request $request)
    {
        $request->validate([
            'title'          => 'nullable|string|max:255',
            'popup_type'     => 'required|string|max:255',
            'trigger_type'   => 'required|string|in:delay,scroll,immediate',
            'trigger_value'  => 'nullable|numeric|min:0',
            'frequency_days' => 'required|integer|min:0',
            'image'          => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $safeInternalTitle = $request->filled('title') 
                ? trim($request->input('title')) 
                : (ucfirst($request->input('popup_type', 'Promotion')) . ' Campaign #' . (DynamicPopup::max('id') + 1));

            $popup = DynamicPopup::create([
                'title'          => $safeInternalTitle,
                'popup_type'     => $request->input('popup_type'),
                'content'        => $request->input('content'),
                'image'          => $request->input('image'),
                'button_text'    => $request->input('button_text'),
                'button_url'     => $request->input('button_url'),
                'trigger_type'   => $request->input('trigger_type'),
                'trigger_value'  => $request->input('trigger_value', 3),
                'frequency_days' => $request->input('frequency_days', 0),
                'is_active'      => $request->boolean('is_active', true),
                'settings'       => [
                    'show_title'       => $request->boolean('show_title', false),
                    'show_button'      => $request->boolean('show_button', false),
                    'frequency_scope'  => $request->input('frequency_scope', 'per_page'),
                    'bg_color'         => $request->input('bg_color', '#ffffff'),
                    'text_color'       => $request->input('text_color', '#0f172a'),
                    'btn_bg_color'     => $request->input('btn_bg_color', '#2563eb'),
                    'max_width'        => $request->input('max_width', '480px'),
                ],
            ]);

            $this->syncDisplayRule($popup, $request, 'popup');

            DB::commit();
            return $this->redirectToIndex('Dynamic Popup created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create popup: ' . $e->getMessage());
        }
    }

    public function popupEdit($id)
    {
        $popup = DynamicPopup::with('rule')->findOrFail($id);
        return view('admin.theme.dynamic_display.popup_edit', compact('popup'));
    }

    public function popupUpdate(Request $request, $id)
    {
        $popup = DynamicPopup::findOrFail($id);

        $request->validate([
            'title'          => 'nullable|string|max:255',
            'popup_type'     => 'required|string|max:255',
            'trigger_type'   => 'required|string|in:delay,scroll,immediate',
            'trigger_value'  => 'nullable|numeric|min:0',
            'frequency_days' => 'required|integer|min:0',
            'image'          => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $safeInternalTitle = $request->filled('title') 
                ? trim($request->input('title')) 
                : ($popup->title ?: (ucfirst($request->input('popup_type', 'Promotion')) . ' Campaign #' . $popup->id));

            $popup->update([
                'title'          => $safeInternalTitle,
                'popup_type'     => $request->input('popup_type'),
                'content'        => $request->input('content'),
                'image'          => $request->input('image'),
                'button_text'    => $request->input('button_text'),
                'button_url'     => $request->input('button_url'),
                'trigger_type'   => $request->input('trigger_type'),
                'trigger_value'  => $request->input('trigger_value', 3),
                'frequency_days' => $request->input('frequency_days', 0),
                'is_active'      => $request->boolean('is_active', true),
                'settings'       => [
                    'show_title'       => $request->boolean('show_title', false),
                    'show_button'      => $request->boolean('show_button', false),
                    'frequency_scope'  => $request->input('frequency_scope', 'per_page'),
                    'bg_color'         => $request->input('bg_color', '#ffffff'),
                    'text_color'       => $request->input('text_color', '#0f172a'),
                    'btn_bg_color'     => $request->input('btn_bg_color', '#2563eb'),
                    'max_width'        => $request->input('max_width', '480px'),
                ],
            ]);

            $this->syncDisplayRule($popup, $request, 'popup');

            DB::commit();
            return $this->redirectToIndex('Dynamic Popup updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function popupToggle($id)
    {
        $popup = DynamicPopup::findOrFail($id);
        $popup->is_active = !$popup->is_active;
        $popup->save();

        return response()->json([
            'success'   => true,
            'is_active' => $popup->is_active,
            'message'   => 'Popup status toggled successfully.'
        ]);
    }

    public function popupDestroy($id)
    {
        $popup = DynamicPopup::findOrFail($id);
        if ($popup->rule) {
            $popup->rule()->delete();
        }
        $popup->delete();

        return $this->redirectToIndex('Popup removed.');
    }

    // ==========================================
    // 2. ADVANCED FLOATING WIDGETS MANAGEMENT
    // ==========================================

    public function widgetStore(Request $request)
    {
        $request->validate([
            'widget_type' => 'required|string|max:255',
            'position'    => 'required|string|in:bottom_right,bottom_left',
            'value'       => 'nullable|string|max:255',
        ]);

        $this->ensureWidgetSettingsColumn();

        $actionType = $request->input('action_type', 'link');
        $value = ($actionType === 'modal') ? '#' : ($request->input('value') ?: '#');

        $widgetData = [
            'widget_type'   => $request->input('widget_type'),
            'label'         => $request->input('label'),
            'value'         => $value,
            'icon'          => $request->input('icon') ?: 'fas fa-comment-dots',
            'position'      => $request->input('position', 'bottom_right'),
            'bottom_offset' => 20,
            'side_offset'   => 20,
            'bg_color'      => $request->input('bg_color', '#25d366'),
            'icon_color'    => $request->input('icon_color', '#ffffff'),
            'order_index'   => $request->input('order_index', 0),
            'is_active'     => $request->boolean('is_active', true),
        ];

        if (Schema::hasColumn('floating_widgets', 'settings')) {
            $settingsPayload = [
                'action_type'   => $actionType,
                'modal_title'   => $request->input('modal_title'),
                'modal_content' => $request->input('modal_content'),
            ];

            $dummyWidget = new FloatingWidget();
            $widgetData['settings'] = (method_exists($dummyWidget, 'hasCast') && $dummyWidget->hasCast('settings'))
                ? $settingsPayload
                : json_encode($settingsPayload);
        }

        $widget = FloatingWidget::create($widgetData);

        $this->syncDisplayRule($widget, $request, 'widget');

        return $this->redirectToIndex('Floating Widget saved successfully.');
    }

    public function widgetUpdate(Request $request, $id)
    {
        $widget = FloatingWidget::findOrFail($id);

        $request->validate([
            'widget_type' => 'required|string|max:255',
            'position'    => 'required|string|in:bottom_right,bottom_left',
            'value'       => 'nullable|string|max:255',
        ]);

        $this->ensureWidgetSettingsColumn();

        $actionType = $request->input('action_type', 'link');
        $value = ($actionType === 'modal') ? '#' : ($request->input('value') ?: '#');

        $widgetData = [
            'widget_type'   => $request->input('widget_type'),
            'label'         => $request->input('label'),
            'value'         => $value,
            'icon'          => $request->input('icon') ?: 'fas fa-comment-dots',
            'position'      => $request->input('position', 'bottom_right'),
            'bottom_offset' => 20,
            'side_offset'   => 20,
            'bg_color'      => $request->input('bg_color', '#25d366'),
            'icon_color'    => $request->input('icon_color', '#ffffff'),
            'order_index'   => $request->input('order_index', 0),
            'is_active'     => $request->boolean('is_active', true),
        ];

        if (Schema::hasColumn('floating_widgets', 'settings')) {
            $settingsPayload = [
                'action_type'   => $actionType,
                'modal_title'   => $request->input('modal_title'),
                'modal_content' => $request->input('modal_content'),
            ];

            $widgetData['settings'] = (method_exists($widget, 'hasCast') && $widget->hasCast('settings'))
                ? $settingsPayload
                : json_encode($settingsPayload);
        }

        $widget->update($widgetData);

        $this->syncDisplayRule($widget, $request, 'widget');

        return $this->redirectToIndex('Floating Widget updated successfully.');
    }

    public function widgetToggle($id)
    {
        $widget = FloatingWidget::findOrFail($id);
        $widget->is_active = !$widget->is_active;
        $widget->save();

        return response()->json([
            'success'   => true,
            'is_active' => $widget->is_active,
            'message'   => 'Widget status toggled successfully.'
        ]);
    }

    public function widgetDestroy($id)
    {
        $widget = FloatingWidget::findOrFail($id);
        if ($widget->rule) {
            $widget->rule()->delete();
        }
        $widget->delete();

        return $this->redirectToIndex('Widget deleted.');
    }

    // ==========================================
    // 3. WIDGET DISPLAY MODE & TOGGLE HUB SETTINGS
    // ==========================================

    public function updateWidgetHubSettings(Request $request)
    {
        $this->ensureCampaignRuleSettingsColumn();

        $rule = DisplayCampaignRule::firstOrNew(['rule_scope' => 'floating_widget_hub']);

        $settingsData = [
            'display_style'    => $request->input('display_style', 'speed_dial'),
            'hub_label'        => $request->input('hub_label', 'Chat with Us'),
            // Fixed: boolean default false so unchecking correctly saves as false
            'show_label'       => $request->boolean('show_label'),
            'hub_icon'         => $request->input('hub_icon', 'fas fa-comment-dots'),
            'hub_bg_color'     => $request->input('hub_bg_color', '#1e3a8a'),
            'hub_icon_color'   => $request->input('hub_icon_color', '#ffffff'),
            'badge_bg_color'   => $request->input('badge_bg_color', '#ffffff'),
            'badge_text_color' => $request->input('badge_text_color', '#1e3a8a'),
        ];

        // Prevents SQL "Array to string conversion" QueryException
        $isCasted = method_exists($rule, 'hasCast') && $rule->hasCast('settings');
        $rule->settings = $isCasted ? $settingsData : json_encode($settingsData);

        $rule->is_active = true;
        $rule->save();

        return $this->redirectToIndex('Floating widget display style updated successfully.');
    }

    /**
     * Fallback to prevent Route/Method error if the form posts to sticky-bar-rule URL
     */
    public function updateStickyBarRule(Request $request)
    {
        return $this->updateWidgetHubSettings($request);
    }

    // ==========================================
    // HELPER: SYNC DISPLAY RULE
    // ==========================================

    protected function syncDisplayRule($model, Request $request, string $scope)
    {
        $targetPages = array_filter(array_map('trim', explode("\n", $request->input('target_pages', ''))));
        $excludedPages = array_filter(array_map('trim', explode("\n", $request->input('excluded_pages', ''))));

        $ruleData = [
            'rule_scope'        => $scope,
            'device_visibility' => [
                'desktop' => $request->boolean('device_desktop', true),
                'tablet'  => $request->boolean('device_tablet', true),
                'mobile'  => $request->boolean('device_mobile', true),
            ],
            'page_target_type'  => $request->input('page_target_type', 'all'),
            'target_pages'      => array_values($targetPages),
            'excluded_pages'    => array_values($excludedPages),
            'start_at'          => $request->filled('start_at') ? $request->input('start_at') : null,
            'end_at'            => $request->filled('end_at') ? $request->input('end_at') : null,
            'user_type'         => $request->input('user_type', 'all'),
            'is_active'         => true,
        ];

        if ($model->rule) {
            $model->rule()->update($ruleData);
        } else {
            $model->rule()->create($ruleData);
        }
    }
}