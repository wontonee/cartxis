<?php

declare(strict_types=1);

namespace Cartxis\CMS\Database\Seeders;

use Illuminate\Database\Seeder;

class BlockSeeder extends Seeder
{
    /**
     * Homepage layout is now managed entirely by the UIEditor (PageLayout model).
     * Legacy CMS block records have been removed.
     */
    public function run(): void
    {
        // No legacy blocks to seed — use UIEditor for homepage content.
    }
}
