<?php

class FMTM_Tenant_Migrator
{
    private string $db_name;
    private string $db_user;
    private string $db_password;
    private string $db_host;

    public function __construct(string $db_name, string $db_user, string $db_password, string $db_host)
    {
        $this->db_name = $db_name;
        $this->db_user = $db_user;
        $this->db_password = $db_password;
        $this->db_host = $db_host;
    }

    public function run(): void
    {
        $wpdb = new wpdb($this->db_user, $this->db_password, $this->db_name, $this->db_host);
        $wpdb->query('SET sql_mode = ""');

        $schema = file_get_contents(FMTM_PLUGIN_DIR . 'migrations/tenant_schema.sql');
        foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
            $wpdb->query($statement);
        }
    }
}
