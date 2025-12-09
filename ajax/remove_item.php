<?php
/**
 * -------------------------------------------------------------------------
 * TicketBar plugin for GLPI - AJAX Remove Item from Ticket
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
        throw new Exception('Missing required parameters');
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

    // Check if user can update ticket
    if (!$ticket->can($ticket_id, UPDATE)) {
        throw new Exception('No permission to update this ticket');
    }

    // Find the link
    $item_ticket = new Item_Ticket();
    $found = $item_ticket->find([
        'tickets_id' => $ticket_id,
        'itemtype' => $itemtype,
        'items_id' => $items_id
    ]);

    if (empty($found)) {
        echo json_encode([
            'success' => false,
            'message' => __('Item not linked to this ticket', 'ticketbar')
        ]);
        exit;
    }

    // Remove the link
    $result = $item_ticket->delete(['id' => key($found)]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => __('Item successfully removed from ticket', 'ticketbar')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => __('Failed to remove item from ticket', 'ticketbar')
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
