<?php
/**
 * -------------------------------------------------------------------------
 * TicketBar plugin for GLPI - AJAX Search Endpoint
 * -------------------------------------------------------------------------
 */

// Initialize GLPI
include ('../../../inc/includes.php');

header('Content-Type: application/json; charset=utf-8');

Html::header_nocache();

Session::checkLoginUser();

try {
    // Get search term
    $search_term = $_GET['q'] ?? '';

    if (strlen($search_term) < 2) {
        echo json_encode([]);
        exit;
    }

    // Perform search
    $results = plugin_ticketbar_search_items($search_term);

    echo json_encode($results);

} catch (Exception $e) {
    // Log the actual error
    Toolbox::logInFile('ticketbar-error', 
        "Search error: " . $e->getMessage() . "\n" . 
        "File: " . $e->getFile() . "\n" .
        "Line: " . $e->getLine() . "\n" .
        "Stack: " . $e->getTraceAsString() . "\n"
    );
    
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Search error: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}

/**
 * Search for assets, locations, and ITIL objects
 */
function plugin_ticketbar_search_items(string $search_term): array
{
    global $DB;

    $results = [];
    $search_term = $DB->escape($search_term);

    // Define searchable item types - Only Assets
    $itemtypes = [
        'Computer',
        'Monitor',
        'Printer',
        'NetworkEquipment',
        'Phone',
        'Peripheral',
        'Software',
        'SoftwareLicense'
    ];

    foreach ($itemtypes as $itemtype) {
        if (!class_exists($itemtype)) {
            continue;
        }

        // Check if user has right to view this itemtype
        if (!$itemtype::canView()) {
            continue;
        }

        $table = getTableForItemType($itemtype);
        $item = new $itemtype();

        // Get the name field for this itemtype
        if (method_exists($itemtype, 'getNameField')) {
            $name_field = $itemtype::getNameField();
        } else {
            $name_field = 'name';
        }

        // Build query
        $criteria = [
            'SELECT' => ['id', $name_field . ' AS name'],
            'FROM' => $table,
            'WHERE' => [
                $name_field => ['LIKE', "%$search_term%"]
            ],
            'LIMIT' => 5
        ];
        
        // Add serial field for assets if available
        if (in_array($itemtype, ['Computer', 'Monitor', 'Printer', 'NetworkEquipment', 'Phone', 'Peripheral'])) {
            $criteria['SELECT'][] = 'serial';
        }

        // Add deleted condition if applicable
        if ($item->maybeDeleted()) {
            $criteria['WHERE']['is_deleted'] = 0;
        }

        // Add template condition if applicable
        if ($item->maybeTemplate()) {
            $criteria['WHERE']['is_template'] = 0;
        }

        // Execute query
        $iterator = $DB->request($criteria);

        foreach ($iterator as $row) {
            $item_obj = new $itemtype();
            if ($item_obj->getFromDB($row['id'])) {
                $result_item = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'typename' => $itemtype::getTypeName(1),
                    'itemtype' => $itemtype,
                    'link' => $item_obj->getFormURLWithID($row['id'])
                ];
                
                // Add serial number if available
                if (isset($row['serial'])) {
                    $result_item['serial'] = $row['serial'];
                }
                
                $results[] = $result_item;
            }
        }
    }

    // Limit total results
    return array_slice($results, 0, 50);
}