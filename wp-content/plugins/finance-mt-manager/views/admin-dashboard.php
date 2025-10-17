<div class="wrap">
    <h1><?php esc_html_e('Finance MT Dashboard', 'finance-mt'); ?></h1>
    <?php if (isset($_GET['fmtm_created'])) : ?>
        <div class="notice notice-success"><p><?php printf(esc_html__('Tenant %s created.', 'finance-mt'), esc_html($_GET['fmtm_created'])); ?></p></div>
    <?php endif; ?>
    <div class="fmtm-grid">
        <div class="fmtm-card">
            <h2><?php esc_html_e('Create New Tenant', 'finance-mt'); ?> / <span><?php esc_html_e('Naya Company', 'finance-mt'); ?></span></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('fmtm_create_tenant'); ?>
                <input type="hidden" name="action" value="fmtm_create_tenant" />
                <p>
                    <label><?php esc_html_e('Company Name', 'finance-mt'); ?> / <span><?php esc_html_e('Company Naam', 'finance-mt'); ?></span></label>
                    <input type="text" name="company_name" class="regular-text" required />
                </p>
                <p>
                    <label><?php esc_html_e('Slug (optional)', 'finance-mt'); ?> / <span><?php esc_html_e('Short Naam', 'finance-mt'); ?></span></label>
                    <input type="text" name="company_slug" class="regular-text" />
                </p>
                <p>
                    <label><?php esc_html_e('Admin Email', 'finance-mt'); ?> / <span><?php esc_html_e('Admin Email', 'finance-mt'); ?></span></label>
                    <input type="email" name="admin_email" class="regular-text" required />
                </p>
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Create Tenant', 'finance-mt'); ?> / <span><?php esc_html_e('Nayi Tenant Banao', 'finance-mt'); ?></span></button>
                </p>
            </form>
        </div>
        <div class="fmtm-card">
            <h2><?php esc_html_e('Existing Tenants', 'finance-mt'); ?> / <span><?php esc_html_e('Mojooda Companies', 'finance-mt'); ?></span></h2>
            <table class="fmtm-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Name', 'finance-mt'); ?></th>
                        <th><?php esc_html_e('Slug', 'finance-mt'); ?></th>
                        <th><?php esc_html_e('Database', 'finance-mt'); ?></th>
                        <th><?php esc_html_e('Created', 'finance-mt'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tenants as $tenant) : ?>
                        <tr>
                            <td><?php echo esc_html($tenant['name']); ?></td>
                            <td><code><?php echo esc_html($tenant['slug']); ?></code></td>
                            <td><code><?php echo esc_html($tenant['db_name']); ?></code></td>
                            <td><?php echo esc_html($tenant['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($tenants)) : ?>
                        <tr><td colspan="4"><?php esc_html_e('No tenants yet.', 'finance-mt'); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
