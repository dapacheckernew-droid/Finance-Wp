<div class="wrap">
    <h1><?php esc_html_e('Tenant Invoices', 'finance-mt'); ?> / <span><?php esc_html_e('Company Invoices', 'finance-mt'); ?></span></h1>
    <?php if (!empty($notice)) : ?>
        <div class="notice notice-<?php echo esc_attr($notice['type']); ?>"><p><?php echo esc_html($notice['message']); ?></p></div>
    <?php endif; ?>
    <div class="fmtm-card">
        <h2><?php esc_html_e('Create Invoice', 'finance-mt'); ?> / <span><?php esc_html_e('Invoice Banao', 'finance-mt'); ?></span></h2>
        <form method="post">
            <?php wp_nonce_field('fmtm_create_invoice'); ?>
            <input type="hidden" name="fmtm_action" value="create_invoice" />
            <div class="fmtm-flex">
                <p><label><?php esc_html_e('Invoice #', 'finance-mt'); ?><br/><input type="text" name="invoice_number" required /></label></p>
                <p><label><?php esc_html_e('Customer', 'finance-mt'); ?><br/><input type="text" name="customer_name" required /></label></p>
                <p><label><?php esc_html_e('Issue Date', 'finance-mt'); ?><br/><input type="date" name="issue_date" required /></label></p>
                <p><label><?php esc_html_e('Due Date', 'finance-mt'); ?><br/><input type="date" name="due_date" required /></label></p>
                <p><label><?php esc_html_e('Status', 'finance-mt'); ?><br/>
                    <select name="status">
                        <option value="draft"><?php esc_html_e('Draft', 'finance-mt'); ?></option>
                        <option value="sent"><?php esc_html_e('Sent', 'finance-mt'); ?></option>
                        <option value="paid"><?php esc_html_e('Paid', 'finance-mt'); ?></option>
                    </select>
                </label></p>
                <p><label><?php esc_html_e('Tax', 'finance-mt'); ?><br/><input type="number" step="0.01" name="tax" value="0" /></label></p>
            </div>
            <div class="fmtm-card">
                <h3><?php esc_html_e('Line Items', 'finance-mt'); ?> / <span><?php esc_html_e('Items Tafseel', 'finance-mt'); ?></span></h3>
                <div class="fmtm-line-items"></div>
                <button class="button fmtm-add-line"><?php esc_html_e('Add Line', 'finance-mt'); ?> / <span><?php esc_html_e('Line Shamil', 'finance-mt'); ?></span></button>
                <script type="text/html" id="fmtm-line-template">
                    <div class="fmtm-line-item" style="display:flex;gap:10px;margin-top:10px;">
                        <input type="text" name="items[][name]" placeholder="<?php esc_attr_e('Item', 'finance-mt'); ?>" required />
                        <input type="number" step="0.01" name="items[][quantity]" placeholder="<?php esc_attr_e('Qty', 'finance-mt'); ?>" required />
                        <input type="number" step="0.01" name="items[][unit_price]" placeholder="<?php esc_attr_e('Rate', 'finance-mt'); ?>" required />
                        <a href="#" class="fmtm-remove-line">&times;</a>
                    </div>
                </script>
            </div>
            <p><button type="submit" class="button button-primary"><?php esc_html_e('Save Invoice', 'finance-mt'); ?> / <span><?php esc_html_e('Invoice Save karo', 'finance-mt'); ?></span></button></p>
        </form>
    </div>
    <div class="fmtm-card">
        <h2><?php esc_html_e('Invoice List', 'finance-mt'); ?> / <span><?php esc_html_e('Invoice Fehrist', 'finance-mt'); ?></span></h2>
        <table class="fmtm-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Invoice #', 'finance-mt'); ?></th>
                    <th><?php esc_html_e('Customer', 'finance-mt'); ?></th>
                    <th><?php esc_html_e('Status', 'finance-mt'); ?></th>
                    <th><?php esc_html_e('Total', 'finance-mt'); ?></th>
                    <th><?php esc_html_e('Issue Date', 'finance-mt'); ?></th>
                    <th><?php esc_html_e('PDF', 'finance-mt'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $invoice) : ?>
                    <tr>
                        <td><?php echo esc_html($invoice['invoice_number']); ?></td>
                        <td><?php echo esc_html($invoice['customer_name']); ?></td>
                        <td><span class="fmtm-badge"><?php echo esc_html(ucfirst($invoice['status'])); ?></span></td>
                        <td><?php echo esc_html(number_format((float)$invoice['total'], 2)); ?></td>
                        <td><?php echo esc_html($invoice['issue_date']); ?></td>
                        <td><a href="<?php echo esc_url(rest_url('fmtm/v1/tenants/' . $tenant_slug . '/invoices/' . $invoice['id'] . '/pdf')); ?>" target="_blank"><?php esc_html_e('PDF', 'finance-mt'); ?></a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($invoices)) : ?>
                    <tr><td colspan="6"><?php esc_html_e('No invoices yet.', 'finance-mt'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
