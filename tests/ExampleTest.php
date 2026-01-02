<?php

/**
 * -------------------------------------------------------------------------
 * TicketBar plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of TicketBar.
 *
 * TicketBar is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * TicketBar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TicketBar. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 */

namespace GlpiPlugin\Ticketbar\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Simple example test to verify PHPUnit is working
 */
class ExampleTest extends TestCase
{
    /**
     * Test that basic assertions work
     */
    public function testExample(): void
    {
        $this->assertTrue(true);
        $this->assertEquals(2, 1 + 1);
        $this->assertIsString('hello');
    }

    /**
     * Test that plugin directory exists
     */
    public function testPluginDirectoryExists(): void
    {
        $pluginDir = dirname(__DIR__);
        $this->assertDirectoryExists($pluginDir);
        $this->assertFileExists($pluginDir . '/setup.php');
    }
}
