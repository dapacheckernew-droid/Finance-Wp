<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/includes/tenant-manager.php';

class TenantSlugTest extends TestCase
{
    public function test_slug_sanitization(): void
    {
        $name = 'Liaquat Fabrics & Co';
        $slug = sanitize_title($name);
        $this->assertEquals('liaquat-fabrics-co', $slug);
    }
}
