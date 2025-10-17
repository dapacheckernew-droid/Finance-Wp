<?php

class FMTM_Tenant_Migrator
{
    private wpdb $wpdb;
    private string $table_prefix;

    public function __construct(wpdb $wpdb, string $table_prefix)
    {
        $this->wpdb = $wpdb;
        $this->table_prefix = $table_prefix;
    }

    public function run()
    {
        $this->wpdb->query('SET sql_mode = ""');
        $schema = file_get_contents(FMTM_PLUGIN_DIR . 'migrations/tenant_schema.sql');
        $schema = str_replace('{{prefix}}', $this->table_prefix, $schema);

        foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
            if ($statement === '') {
                continue;
            }
            $result = $this->wpdb->query($statement);
            if ($result === false) {
                return new WP_Error('tenant_migration_failed', $this->wpdb->last_error ?: __('Unknown migration error', 'finance-mt'));
            }
        }

        return true;
    }
}
