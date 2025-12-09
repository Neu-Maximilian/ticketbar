<?php
if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class PluginTicketbarTicket extends CommonDBTM
{
    public static $rightname = 'ticket';

    public static function canAddItems()
    {
        return Session::haveRight('ticket', UPDATE);
    }

    public static function displaySearchBar($params)
    {
        if (!isset($params['item']) || !($params['item'] instanceof Ticket)) {
            return;
        }

        if (!self::canAddItems()) {
            return;
        }

        $ticket = $params['item'];
        $ticket_id = $ticket->getID();
        $is_new = ($ticket_id <= 0 || $ticket->isNewItem());

        $plugin_root = Plugin::getWebDir('ticketbar');
        $js_path = $plugin_root . '/ticketbar.js';

        // Use PHP template (simpler than Twig namespace configuration)
        $template_path = PLUGIN_TICKETBAR_DIR . '/templates/asset_search.php';
        
        if (file_exists($template_path)) {
            // Variables for template
            include($template_path);
        } else {
            echo '<div class="alert alert-warning">Ticketbar: Template introuvable - ' . $template_path . '</div>';
        }
    }
}