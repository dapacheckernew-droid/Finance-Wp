<?php

class FMTM_Invoices_Controller
{
    private wpdb $db;
    private string $tenant_slug;

    public function __construct(?wpdb $db, string $tenant_slug)
    {
        if (!$db) {
            wp_die(__('Tenant database unavailable', 'finance-mt'));
        }
        $this->db = $db;
        $this->tenant_slug = $tenant_slug;
    }

    public function handle_request(): ?array
    {
        if (!current_user_can('manage_fmtm_invoices')) {
            return null;
        }

        if (!empty($_POST['fmtm_action']) && $_POST['fmtm_action'] === 'create_invoice') {
            check_admin_referer('fmtm_create_invoice');
            return $this->create_invoice();
        }

        return null;
    }

    public function list_invoices(): array
    {
        return $this->db->get_results('SELECT * FROM ' . $this->db->prefix . 'invoices ORDER BY created_at DESC', ARRAY_A) ?: [];
    }

    private function create_invoice(): array
    {
        $data = [
            'invoice_number' => sanitize_text_field($_POST['invoice_number'] ?? ''),
            'customer_name' => sanitize_text_field($_POST['customer_name'] ?? ''),
            'issue_date' => sanitize_text_field($_POST['issue_date'] ?? ''),
            'due_date' => sanitize_text_field($_POST['due_date'] ?? ''),
            'status' => sanitize_text_field($_POST['status'] ?? 'draft'),
            'currency' => sanitize_text_field($_POST['currency'] ?? 'PKR'),
        ];

        if (!$data['invoice_number'] || !$data['customer_name']) {
            return ['type' => 'error', 'message' => __('Invoice number and customer name required.', 'finance-mt')];
        }

        $items = $this->prepare_items($_POST['items'] ?? []);
        if (empty($items)) {
            return ['type' => 'error', 'message' => __('At least one line item required.', 'finance-mt')];
        }

        $subtotal = array_sum(array_column($items, 'total'));
        $tax = (float)($_POST['tax'] ?? 0);
        $total = $subtotal + $tax;

        $this->db->insert($this->db->prefix . 'invoices', [
            'invoice_number' => $data['invoice_number'],
            'customer_name' => $data['customer_name'],
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'],
            'status' => $data['status'],
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'currency' => $data['currency'],
            'created_at' => current_time('mysql'),
        ]);

        $invoice_id = $this->db->insert_id;

        foreach ($items as $item) {
            $this->db->insert($this->db->prefix . 'invoice_items', [
                'invoice_id' => $invoice_id,
                'item_name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['total'],
            ]);
        }

        $this->log_action('create_invoice', $invoice_id, $data);

        return ['type' => 'success', 'message' => __('Invoice created successfully.', 'finance-mt')];
    }

    private function prepare_items($items): array
    {
        $prepared = [];
        if (!is_array($items)) {
            return $prepared;
        }
        foreach ($items as $item) {
            $name = sanitize_text_field($item['name'] ?? '');
            $qty = (float)($item['quantity'] ?? 0);
            $price = (float)($item['unit_price'] ?? 0);
            if (!$name || $qty <= 0 || $price < 0) {
                continue;
            }
            $prepared[] = [
                'name' => $name,
                'quantity' => $qty,
                'unit_price' => $price,
                'total' => $qty * $price,
            ];
        }
        return $prepared;
    }

    private function log_action(string $action, int $entity_id, array $data): void
    {
        $this->db->insert($this->db->prefix . 'audit_log', [
            'actor' => get_current_user_id(),
            'action' => $action,
            'entity_type' => 'invoice',
            'entity_id' => $entity_id,
            'before_state' => null,
            'after_state' => wp_json_encode($data),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at' => current_time('mysql'),
        ]);
    }
}
