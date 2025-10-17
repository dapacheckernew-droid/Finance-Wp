<?php

class FMTM_Rest_Routes
{
    public static function init(): void
    {
        add_action('rest_api_init', [self::class, 'register']);
    }

    public static function register(): void
    {
        register_rest_route('fmtm/v1', '/tenants/(?P<slug>[a-z0-9\-]+)/invoices', [
            'methods' => 'GET',
            'callback' => [self::class, 'list_invoices'],
            'permission_callback' => [self::class, 'permission_tenant'],
        ]);

        register_rest_route('fmtm/v1', '/tenants/(?P<slug>[a-z0-9\-]+)/invoices', [
            'methods' => 'POST',
            'callback' => [self::class, 'create_invoice'],
            'permission_callback' => [self::class, 'permission_tenant_manage'],
        ]);

        register_rest_route('fmtm/v1', '/tenants/(?P<slug>[a-z0-9\-]+)/invoices/(?P<id>\d+)/pdf', [
            'methods' => 'GET',
            'callback' => [self::class, 'invoice_pdf'],
            'permission_callback' => [self::class, 'permission_tenant'],
        ]);
    }

    public static function permission_tenant(WP_REST_Request $request): bool
    {
        $slug = $request['slug'];
        return self::user_can_access($slug, ['fmtm_owner', 'fmtm_accountant', 'fmtm_staff', 'fmtm_viewer']);
    }

    public static function permission_tenant_manage(WP_REST_Request $request): bool
    {
        $slug = $request['slug'];
        return self::user_can_access($slug, ['fmtm_owner', 'fmtm_accountant']);
    }

    private static function user_can_access(string $slug, array $roles): bool
    {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return false;
        }
        $default = get_user_meta($user_id, 'fmtm_default_tenant', true);
        if ($default !== $slug) {
            return current_user_can('manage_options');
        }
        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }
        foreach ($user->roles as $role) {
            if (in_array($role, $roles, true)) {
                return true;
            }
        }
        return false;
    }

    public static function list_invoices(WP_REST_Request $request)
    {
        $connection = FMTM_Tenant_Manager::get_tenant_connection($request['slug']);
        if (!$connection) {
            return new WP_Error('tenant_not_found', __('Tenant not found', 'finance-mt'), ['status' => 404]);
        }
        $invoice_table = $connection->prefix . 'invoices';
        $results = $connection->get_results("SELECT * FROM {$invoice_table} ORDER BY created_at DESC", ARRAY_A);
        return rest_ensure_response($results ?: []);
    }

    public static function create_invoice(WP_REST_Request $request)
    {
        $connection = FMTM_Tenant_Manager::get_tenant_connection($request['slug']);
        if (!$connection) {
            return new WP_Error('tenant_not_found', __('Tenant not found', 'finance-mt'), ['status' => 404]);
        }

        $payload = $request->get_json_params();
        $controller = new FMTM_Invoices_Controller($connection, $request['slug']);
        $_POST = [
            'fmtm_action' => 'create_invoice',
            'invoice_number' => $payload['invoice_number'] ?? '',
            'customer_name' => $payload['customer_name'] ?? '',
            'issue_date' => $payload['issue_date'] ?? '',
            'due_date' => $payload['due_date'] ?? '',
            'status' => $payload['status'] ?? 'draft',
            'currency' => $payload['currency'] ?? 'PKR',
            'tax' => $payload['tax'] ?? 0,
            'items' => $payload['items'] ?? [],
        ];
        $result = $controller->handle_request();
        if ($result && $result['type'] === 'error') {
            return new WP_Error('invoice_error', $result['message'], ['status' => 422]);
        }
        return rest_ensure_response(['status' => 'ok']);
    }

    public static function invoice_pdf(WP_REST_Request $request)
    {
        $connection = FMTM_Tenant_Manager::get_tenant_connection($request['slug']);
        if (!$connection) {
            return new WP_Error('tenant_not_found', __('Tenant not found', 'finance-mt'), ['status' => 404]);
        }
        $invoice_table = $connection->prefix . 'invoices';
        $invoice = $connection->get_row($connection->prepare("SELECT * FROM {$invoice_table} WHERE id = %d", $request['id']), ARRAY_A);
        if (!$invoice) {
            return new WP_Error('invoice_not_found', __('Invoice not found', 'finance-mt'), ['status' => 404]);
        }
        $items_table = $connection->prefix . 'invoice_items';
        $items = $connection->get_results($connection->prepare("SELECT * FROM {$items_table} WHERE invoice_id = %d", $request['id']), ARRAY_A);

        $html = FMTM_View_Renderer::render('invoice-pdf', [
            'invoice' => $invoice,
            'items' => $items,
        ]);

        $pdf = FMTM_Pdf_Service::generate($html, 'invoice-' . $invoice['invoice_number'] . '.pdf');
        return $pdf;
    }
}
