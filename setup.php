<?php
/**
 * -------------------------------------------------------------------------
 * TicketBar plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * MIT License
 */

define('PLUGIN_TICKETBAR_VERSION', '0.0.2');
define('PLUGIN_TICKETBAR_MIN_GLPI', '10.0.0');
define('PLUGIN_TICKETBAR_MAX_GLPI', '11.0.99');

// Define plugin directory path
if (!defined('PLUGIN_TICKETBAR_DIR')) {
    define('PLUGIN_TICKETBAR_DIR', __DIR__);
}

/**
 * Init the hooks of the plugins - Needed
 */
function plugin_init_ticketbar()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['ticketbar'] = true;
    
    // Initialize plugin translations
    Plugin::registerClass('PluginTicketbarTicket');

    // Register the search bar to be displayed in ticket forms
    require_once(__DIR__ . '/inc/ticket.class.php');
    $PLUGIN_HOOKS['post_item_form']['ticketbar'] = ['PluginTicketbarTicket', 'displaySearchBar'];
}

/**
 * Get the name and the version of the plugin - Needed
 */
function plugin_version_ticketbar()
{
    return [
        'name' => 'TicketBar',
        'version' => PLUGIN_TICKETBAR_VERSION,
        'author' => 'TicketBar Team',
        'license' => 'MIT',
        'homepage' => 'https://github.com/pluginsGLPI/ticketbar',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_TICKETBAR_MIN_GLPI,
                'max' => PLUGIN_TICKETBAR_MAX_GLPI,
            ]
        ]
    ];
}

/**
 * Optional: check prerequisites before install
 */
function plugin_ticketbar_check_prerequisites()
{
    if (
        version_compare(GLPI_VERSION, PLUGIN_TICKETBAR_MIN_GLPI, 'lt')
        || version_compare(GLPI_VERSION, PLUGIN_TICKETBAR_MAX_GLPI, 'gt')
    ) {
        echo "This plugin requires GLPI >= " . PLUGIN_TICKETBAR_MIN_GLPI
            . " and GLPI <= " . PLUGIN_TICKETBAR_MAX_GLPI;
        return false;
    }
    return true;
}

/**
 * Check configuration process
 */
function plugin_ticketbar_check_config()
{
    return true;
}