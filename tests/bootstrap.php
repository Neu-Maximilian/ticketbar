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

// Bootstrap file for PHPUnit tests
// This file is loaded before running tests

// Define GLPI_ROOT if not already defined (for standalone testing)
// Assumes plugin is installed in GLPI at: glpi/plugins/ticketbar/
// So GLPI_ROOT is 4 levels up: glpi/plugins/ticketbar/tests/ -> glpi/
if (!defined('GLPI_ROOT')) {
    define('GLPI_ROOT', dirname(__DIR__, 4));
}

// Include GLPI's test bootstrap if it exists (when running in CI with GLPI)
$glpi_test_bootstrap = GLPI_ROOT . '/tests/bootstrap.php';
if (file_exists($glpi_test_bootstrap)) {
    require_once $glpi_test_bootstrap;
}

// Plugin-specific initialization can be added here if needed
