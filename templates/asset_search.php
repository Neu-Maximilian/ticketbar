<?php
/**
 * TicketBar - Asset Search Interface Template
 * Variables disponibles: $ticket_id, $plugin_root, $js_path
 */
if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

// Detect user language
$lang = $_SESSION['glpilanguage'] ?? 'fr_FR';
$is_french = (strpos($lang, 'fr') !== false);

$label = $is_french ? 'Recherche rapide d\'équipements' : 'Quick equipment search';
$placeholder = $is_french ? 'Ordinateurs, moniteurs, imprimantes, téléphones...' : 'Computers, monitors, printers, phones...';
?>

<div class="mb-3" id="ticketbar-container">
    <label class="col-form-label mb-1">
        <i class="ti ti-plus"></i>
        <?php echo $label; ?>
    </label>
    <div>
        <input type="text" 
               class="form-control" 
               id="ticketbar-search-input" 
               placeholder="<?php echo $placeholder; ?>"
               autocomplete="off" />
        <div id="ticketbar-search-results" class="mt-2"></div>
    </div>
</div>

<script src="<?php echo $js_path; ?>"></script>
<script>
    if (typeof TicketBar !== 'undefined') {
        TicketBar.init({
            ticketId: <?php echo $ticket_id; ?>,
            pluginRoot: '<?php echo $plugin_root; ?>',
            isNew: <?php echo $is_new ? 'true' : 'false'; ?>
        });
    } else {
        console.error('TicketBar JavaScript module not loaded');
    }
</script>
