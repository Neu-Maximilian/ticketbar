<?php
/**
 * -------------------------------------------------------------------------
 * TicketBar plugin for GLPI - AJAX Add Item to Ticket
 * -------------------------------------------------------------------------
 */

// Initialize GLPI
include ('../../../inc/includes.php');

header('Content-Type: application/json; charset=utf-8');

Html::header_nocache();

Session::checkLoginUser();

try {
    // Get parameters from POST
    $ticket_id = (int)($_POST['ticket_id'] ?? $_REQUEST['ticket_id'] ?? 0);
    $items_id = (int)($_POST['items_id'] ?? $_REQUEST['items_id'] ?? 0);
    $itemtype = $_POST['itemtype'] ?? $_REQUEST['itemtype'] ?? '';

    // Validate inputs
    if ($ticket_id <= 0 || $items_id <= 0 || empty($itemtype)) {
        throw new Exception('Missing required parameters: ticket_id=' . $ticket_id . ', items_id=' . $items_id . ', itemtype=' . $itemtype);
    }

    // Validate itemtype
    if (!class_exists($itemtype)) {
        throw new Exception('Invalid item type');
    }

    // Check if ticket exists
    $ticket = new Ticket();
    if (!$ticket->getFromDB($ticket_id)) {
        throw new Exception('Ticket not found');
    }

    // Check if item exists
    $item = new $itemtype();
    if (!$item->getFromDB($items_id)) {
        throw new Exception('Item not found');
    }

    // Check if user can update ticket
    if (!$ticket->can($ticket_id, UPDATE)) {
        throw new Exception('No permission to update this ticket');
    }

    // Check if already linked
    $item_ticket = new Item_Ticket();
    $found = $item_ticket->find([
        'tickets_id' => $ticket_id,
        'itemtype' => $itemtype,
        'items_id' => $items_id
    ]);

    if (!empty($found)) {
        echo json_encode([
            'success' => false,
            'message' => __('Item already linked to this ticket', 'ticketbar')
        ]);
        exit;
    }

    // Add the link
    $result = $item_ticket->add([
        'tickets_id' => $ticket_id,
        'itemtype' => $itemtype,
        'items_id' => $items_id
    ]);

    if ($result) {
        // Get item icon
        $icons = [
            'Computer' => 'fas fa-desktop',
            'Monitor' => 'fas fa-tv',
            'Printer' => 'fas fa-print',
            'NetworkEquipment' => 'fas fa-network-wired',
            'Phone' => 'fas fa-phone',
            'Peripheral' => 'fas fa-keyboard',
            'Location' => 'fas fa-map-marker-alt',
            'Problem' => 'fas fa-exclamation-triangle',
            'Change' => 'fas fa-sync-alt',
            'User' => 'fas fa-user',
            'Supplier' => 'fas fa-truck',
        ];
        
        echo json_encode([
            'success' => true,
            'message' => __('Item successfully linked to ticket', 'ticketbar'),
            'item' => [
                'id' => $items_id,
                'name' => $item->getName(),
                'typename' => $itemtype::getTypeName(1),
                'itemtype' => $itemtype,
                'serial' => $item->fields['serial'] ?? '',
                'icon' => $icons[$itemtype] ?? 'fas fa-cube'
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => __('Failed to link item to ticket', 'ticketbar')
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
