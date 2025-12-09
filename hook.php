
<?php
/**
 * -------------------------------------------------------------------------
 * TicketBar plugin for GLPI
 * -------------------------------------------------------------------------
 */

/**
 * Plugin install process
 */
function plugin_ticketbar_install(): bool
{
    // No database tables needed for this plugin
    return true;
}

/**
 * Plugin uninstall process
 */
function plugin_ticketbar_uninstall(): bool
{
    // Nothing to clean up
    return true;
}

