<div class="wrap">
    <h1><?php esc_html_e('Cash & Bank Ledger', 'finance-mt'); ?> / <span><?php esc_html_e('Cash Bank Hisab', 'finance-mt'); ?></span></h1>
    <?php if (!empty($notice)) : ?>
        <div class="notice notice-<?php echo esc_attr($notice['type']); ?>"><p><?php echo esc_html($notice['message']); ?></p></div>
    <?php endif; ?>
    <div class="fmtm-card">
        <h2><?php esc_html_e('Record Entry', 'finance-mt'); ?> / <span><?php esc_html_e('Entry Likho', 'finance-mt'); ?></span></h2>
        <form method="post">
            <?php wp_nonce_field('fmtm_add_cash_entry'); ?>
            <input type="hidden" name="fmtm_action" value="add_cash_entry" />
            <div class="fmtm-flex">
                <p><label><?php esc_html_e('Date', 'finance-mt'); ?><br/><input type="date" name="entry_date" value="<?php echo esc_attr(date('Y-m-d')); ?>" required /></label></p>
                <p><label><?php esc_html_e('Reference', 'finance-mt'); ?><br/><input type="text" name="reference" /></label></p>
                <p><label><?php esc_html_e('Type', 'finance-mt'); ?><br/>
                    <select name="entry_type">
                        <option value="receipt"><?php esc_html_e('Receipt', 'finance-mt'); ?> / <?php esc_html_e('Raseed', 'finance-mt'); ?></option>
                        <option value="payment"><?php esc_html_e('Payment', 'finance-mt'); ?> / <?php esc_html_e('Bhugtaan', 'finance-mt'); ?></option>
                        <option value="transfer"><?php esc_html_e('Transfer', 'finance-mt'); ?> / <?php esc_html_e('Muntransfer', 'finance-mt'); ?></option>
                    </select>
                </label></p>
                <p><label><?php esc_html_e('Amount', 'finance-mt'); ?><br/><input type="number" step="0.01" name="amount" required /></label></p>
            </div>
            <p><label><?php esc_html_e('Description', 'finance-mt'); ?><br/><textarea name="description" rows="3"></textarea></label></p>
            <p><button type="submit" class="button button-primary"><?php esc_html_e('Save Entry', 'finance-mt'); ?> / <span><?php esc_html_e('Entry Save karo', 'finance-mt'); ?></span></button></p>
        </form>
    </div>
    <div class="fmtm-card">
        <h2><?php esc_html_e('Ledger', 'finance-mt'); ?> / <span><?php esc_html_e('Hisab Kitaab', 'finance-mt'); ?></span></h2>
        <table class="fmtm-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Date', 'finance-mt'); ?></th>
                    <th><?php esc_html_e('Reference', 'finance-mt'); ?></th>
                    <th><?php esc_html_e('Type', 'finance-mt'); ?></th>
                    <th><?php esc_html_e('Amount', 'finance-mt'); ?></th>
                    <th><?php esc_html_e('Description', 'finance-mt'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry) : ?>
                    <tr>
                        <td><?php echo esc_html($entry['entry_date']); ?></td>
                        <td><?php echo esc_html($entry['reference']); ?></td>
                        <td><?php echo esc_html(ucfirst($entry['type'])); ?></td>
                        <td><?php echo esc_html(number_format((float)$entry['amount'], 2)); ?></td>
                        <td><?php echo esc_html($entry['description']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($entries)) : ?>
                    <tr><td colspan="5"><?php esc_html_e('No ledger entries yet.', 'finance-mt'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
