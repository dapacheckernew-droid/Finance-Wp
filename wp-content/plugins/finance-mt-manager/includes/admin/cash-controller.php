<?php

class FMTM_Cash_Controller
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
        if (!current_user_can('manage_fmtm_cash')) {
            return null;
        }

        if (!empty($_POST['fmtm_action']) && $_POST['fmtm_action'] === 'add_cash_entry') {
            check_admin_referer('fmtm_add_cash_entry');
            return $this->create_entry();
        }
        return null;
    }

    public function list_entries(): array
    {
        return $this->db->get_results('SELECT * FROM fmtm_cash_ledger ORDER BY entry_date DESC', ARRAY_A) ?: [];
    }

    private function create_entry(): array
    {
        $type = sanitize_text_field($_POST['entry_type'] ?? 'receipt');
        $amount = (float)($_POST['amount'] ?? 0);
        $date = sanitize_text_field($_POST['entry_date'] ?? current_time('Y-m-d'));
        $description = sanitize_textarea_field($_POST['description'] ?? '');
        $reference = sanitize_text_field($_POST['reference'] ?? '');

        if ($amount <= 0) {
            return ['type' => 'error', 'message' => __('Amount must be positive.', 'finance-mt')];
        }

        $this->db->insert('fmtm_cash_ledger', [
            'entry_date' => $date,
            'reference' => $reference,
            'type' => $type,
            'amount' => $amount,
            'description' => $description,
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql'),
        ]);

        $this->log_action('cash_' . $type, $this->db->insert_id, compact('amount', 'type', 'date'));

        return ['type' => 'success', 'message' => __('Cash ledger entry saved.', 'finance-mt')];
    }

    private function log_action(string $action, int $entity_id, array $data): void
    {
        $this->db->insert('fmtm_audit_log', [
            'actor' => get_current_user_id(),
            'action' => $action,
            'entity_type' => 'cash_ledger',
            'entity_id' => $entity_id,
            'before_state' => null,
            'after_state' => wp_json_encode($data),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at' => current_time('mysql'),
        ]);
    }
}
