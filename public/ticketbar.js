/**
 * TicketBar - Asset Search and Link Manager
 */
var TicketBar = (function () {
  "use strict";

  let config = {
    ticketId: 0,
    pluginRoot: "",
    isNew: false,
  };

  let currentCsrfToken = null;
  let searchInput = null;
  let resultsDiv = null;
  let searchTimeout = null;

  /**
   * Initialize the plugin
   */
  function init(options) {
    config = Object.assign(config, options);

    searchInput = document.getElementById("ticketbar-search-input");
    resultsDiv = document.getElementById("ticketbar-search-results");

    if (!searchInput || !resultsDiv) {
      console.error("TicketBar: Required elements not found");
      return;
    }

    initCsrfToken();
    bindEvents();

    // If new ticket, display basket if any
    if (config.isNew) {
      displayBasket();
    } else {
      // Existing ticket: always check if there's a basket to process
      const basket = JSON.parse(
        sessionStorage.getItem("ticketbar_basket") || "[]"
      );
      
      if (basket.length > 0) {
        // Basket found, processing...
        processBasket();
      }
    }
  }


  /**
   * Initialize CSRF token
   */
  function initCsrfToken() {
    if (typeof $glpi !== "undefined" && $glpi.csrf_token) {
      currentCsrfToken = $glpi.csrf_token;
    } else if (typeof _glpi_csrf_token !== "undefined") {
      currentCsrfToken = _glpi_csrf_token;
    } else {
      const metaToken = document.querySelector(
        'meta[property="glpi:csrf_token"]'
      );
      if (metaToken) {
        currentCsrfToken = metaToken.getAttribute("content");
      }
    }
  }

  /**
   * Update CSRF token from response
   */
  function updateCsrfToken(response) {
    const newToken = response.headers.get("X-Glpi-Csrf-Token");
    if (newToken) {
      currentCsrfToken = newToken;
      if (typeof $glpi !== "undefined") {
        $glpi.csrf_token = newToken;
      }
      if (typeof _glpi_csrf_token !== "undefined") {
        _glpi_csrf_token = newToken;
      }
    }
  }

  /**
   * Bind events
   */
  function bindEvents() {
    // Auto-search while typing with debounce
    searchInput.addEventListener("input", function () {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(performSearch, 300);
    });

    // Search on Enter key
    searchInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        clearTimeout(searchTimeout);
        performSearch();
      }
    });
  }

  /**
   * Perform search
   */
  function performSearch() {
    const query = searchInput.value.trim();

    if (query.length < 2) {
      resultsDiv.innerHTML = "";
      return;
    }

    resultsDiv.innerHTML =
      '<div class="text-center py-2"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div> <small class="text-muted">Searching...</small></div>';

    fetch(config.pluginRoot + "/ajax/search.php?q=" + encodeURIComponent(query))
      .then((response) => response.json())
      .then((data) => {
        displayResults(data);
      })
      .catch((error) => {
        resultsDiv.innerHTML =
          '<small class="text-danger"><i class="ti ti-alert-circle"></i> Search error</small>';
        console.error("Search error:", error);
      });
  }

  /**
   * Display search results
   */
  function displayResults(data) {
    if (!Array.isArray(data) || data.length === 0) {
      resultsDiv.innerHTML =
        '<small class="text-muted"><i class="ti ti-info-circle"></i> No assets found</small>';
      return;
    }

    let html = '<div class="list-group list-group-flush border rounded">';
    data.forEach((item) => {
      html +=
        '<div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2">';
      html += '<div class="flex-grow-1">';
      html += '<span class="fw-bold">' + escapeHtml(item.name) + "</span> ";
      html +=
        '<span class="badge badge-sm bg-blue-lt ms-1">' +
        escapeHtml(item.typename) +
        "</span>";
      if (item.serial) {
        html +=
          '<br><small class="text-muted"><i class="ti ti-barcode"></i> ' +
          escapeHtml(item.serial) +
          "</small>";
      }
      html += "</div>";
      html +=
        '<button type="button" class="btn btn-sm btn-outline-success" onclick="TicketBar.addItem(' +
        item.id +
        ", '" +
        escapeHtml(item.itemtype) +
        '\')" title="Add to ticket">';
      html += '<i class="ti ti-plus"></i></button>';
      html += "</div>";
    });
    html += "</div>";
    resultsDiv.innerHTML = html;
  }

  /**
   * Add item to temporary basket (for new tickets)
   */
  function saveTicketAndAddItem(itemsId, itemtype) {
    const lang = document.documentElement.lang || "en";
    const isFrench = lang.startsWith("fr");

    // Get or create the basket
    let basket = JSON.parse(sessionStorage.getItem("ticketbar_basket") || "[]");

    // Check if item already in basket
    const exists = basket.some(
      (item) => item.items_id === itemsId && item.itemtype === itemtype
    );
    if (exists) {
      showNotification(
        "info",
        isFrench
          ? "Cet équipement est déjà dans le panier"
          : "This equipment is already in the basket"
      );
      return;
    }

    // Add to basket
    basket.push({ items_id: itemsId, itemtype: itemtype });
    sessionStorage.setItem("ticketbar_basket", JSON.stringify(basket));

    // Show confirmation
    const message = isFrench
      ? `Équipement ajouté au panier (${basket.length}). Les équipements seront liés après la création du ticket.`
      : `Equipment added to basket (${basket.length}). Equipment will be linked after ticket creation.`;

    showNotification("success", message);

    // Clear search
    searchInput.value = "";
    resultsDiv.innerHTML = "";

    // Show basket
    displayBasket();
  }

  /**
   * Display the basket of items to add
   */
  function displayBasket() {
    const basket = JSON.parse(
      sessionStorage.getItem("ticketbar_basket") || "[]"
    );

    if (basket.length === 0) {
      return;
    }

    const lang = document.documentElement.lang || "en";
    const isFrench = lang.startsWith("fr");

    let html = '<div class="alert alert-info mt-2">';
    html +=
      '<strong><i class="ti ti-shopping-cart"></i> ' +
      (isFrench ? "Panier temporaire" : "Temporary basket") +
      " (" +
      basket.length +
      "):</strong><br>";
    html +=
      "<small>" +
      (isFrench
        ? "Ces équipements seront ajoutés après la sauvegarde du ticket"
        : "These equipment will be added after saving the ticket") +
      "</small>";
    html +=
      '<button type="button" class="btn btn-sm btn-outline-danger float-end" onclick="TicketBar.clearBasket()">';
    html +=
      '<i class="ti ti-trash"></i> ' +
      (isFrench ? "Vider" : "Clear") +
      "</button>";
    html += "</div>";

    resultsDiv.innerHTML = html;
  }

  /**
   * Clear the basket
   */
  function clearBasket() {
    sessionStorage.removeItem("ticketbar_basket");
    resultsDiv.innerHTML = "";
    const lang = document.documentElement.lang || "en";
    const isFrench = lang.startsWith("fr");
    showNotification("info", isFrench ? "Panier vidé" : "Basket cleared");
  }

  /**
   * Add item to ticket
   */
  function addItem(itemsId, itemtype) {
    // If this is a new ticket, save it first
    if (config.isNew || config.ticketId <= 0) {
      saveTicketAndAddItem(itemsId, itemtype);
      return;
    }

    if (!currentCsrfToken) {
      initCsrfToken();
    }

    if (!currentCsrfToken) {
      showNotification(
        "error",
        "CSRF token not found. Please reload the page."
      );
      return;
    }

    const formData = new FormData();
    formData.append("ticket_id", config.ticketId);
    formData.append("items_id", itemsId);
    formData.append("itemtype", itemtype);
    formData.append("_glpi_csrf_token", currentCsrfToken);

    fetch(config.pluginRoot + "/ajax/add_item.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        updateCsrfToken(response);
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          searchInput.value = "";
          resultsDiv.innerHTML = "";
          showNotification(
            "success",
            "Asset added successfully! The page will reload."
          );
          setTimeout(() => {
            // Disable all beforeunload confirmations
            window.onbeforeunload = null;
            $(window).off("beforeunload");

            // Disable GLPI's dirty form check
            if (typeof glpi_disable_leave_check !== "undefined") {
              glpi_disable_leave_check = true;
            }
            if (typeof window.glpi_disable_leave_check !== "undefined") {
              window.glpi_disable_leave_check = true;
            }

            // Mark form as clean
            $("form").each(function () {
              $(this).data("changed", false);
            });

            // Force reload
            window.location.href = window.location.href;
          }, 1500);
        } else {
          showNotification(
            "error",
            "Error: " + (data.message || "Failed to add asset")
          );
        }
      })
      .catch((error) => {
        showNotification("error", "Error adding asset");
        console.error("Error:", error);
      });
  }

  /**
   * Show notification using GLPI's toast system if available
   */
  function showNotification(type, message) {
    // Try to use GLPI's native toast notification
    if (typeof glpi_toast_info !== "undefined" && type === "success") {
      glpi_toast_info(message);
      return;
    }
    if (typeof glpi_toast_error !== "undefined" && type === "error") {
      glpi_toast_error(message);
      return;
    }

    // Fallback to custom notification
    const notifDiv = document.createElement("div");
    const icon = type === "success" ? "ti-check" : "ti-alert-circle";
    notifDiv.className =
      "alert alert-" +
      (type === "success" ? "success" : "danger") +
      " alert-dismissible fade show position-fixed shadow-sm";
    notifDiv.style.top = "80px";
    notifDiv.style.right = "20px";
    notifDiv.style.zIndex = "9999";
    notifDiv.style.minWidth = "300px";
    notifDiv.innerHTML =
      '<i class="ti ' +
      icon +
      '"></i> ' +
      message +
      ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    document.body.appendChild(notifDiv);
    setTimeout(() => notifDiv.remove(), 3000);
  }

  /**
   * Escape HTML
   */
  function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  /**
   * Process basket after ticket creation
   * This should be called automatically after ticket is saved
   */
  function processBasket() {
    const basket = JSON.parse(
      sessionStorage.getItem("ticketbar_basket") || "[]"
    );

    if (basket.length === 0 || config.ticketId <= 0) {
      return;
    }

    const lang = document.documentElement.lang || "en";
    const isFrench = lang.startsWith("fr");

    const message = isFrench
      ? `Ajout de ${basket.length} équipement(s)...`
      : `Adding ${basket.length} equipment(s)...`;
    showNotification("info", message);

    // Wait a bit for page to fully load, then reinit CSRF token
    setTimeout(() => {
      initCsrfToken();

      // Wait another moment to ensure token is ready
      setTimeout(() => {
        if (!currentCsrfToken) {
          showNotification(
            "error",
            isFrench ? "Token CSRF introuvable" : "CSRF token not found"
          );
          sessionStorage.removeItem("ticketbar_basket");
          return;
        }

        // Add items sequentially (will reload after each item)
        addItemsSequentially(basket);
      }, 300);
    }, 500);
  }

  /**
   * Add items from basket one by one
   */
  function addItemsSequentially(basket, index) {
    if (basket.length === 0) {
      // All items processed
      const lang = document.documentElement.lang || "en";
      const isFrench = lang.startsWith("fr");

      sessionStorage.removeItem("ticketbar_basket");

      const message = isFrench
        ? `Tous les équipements ont été ajoutés avec succès`
        : `All equipment added successfully`;
      showNotification("success", message);
      return;
    }

    // Take the first item from basket
    const item = basket[0];
    const remaining = basket.length;
    
    const lang = document.documentElement.lang || "en";
    const isFrench = lang.startsWith("fr");
    const message = isFrench
      ? `Ajout de l'équipement (${remaining} restant${remaining > 1 ? 's' : ''})...`
      : `Adding equipment (${remaining} remaining)...`;
    
    // Processing item

    addItemDirect(item.items_id, item.itemtype, (data) => {
      if (data && data.success) {
        // Item added successfully
        
        // Remove this item from basket
        basket.shift();
        sessionStorage.setItem("ticketbar_basket", JSON.stringify(basket));
        
        // Reload page to get fresh CSRF token and continue
        window.onbeforeunload = null;
        $(window).off("beforeunload");
        window.location.reload();
      } else {
        console.error(`Error adding item:`, data);
        
        // Remove failed item and continue with next
        basket.shift();
        sessionStorage.setItem("ticketbar_basket", JSON.stringify(basket));
        
        const errorMsg = isFrench
          ? `Erreur lors de l'ajout. Tentative suivante...`
          : `Error adding item. Trying next...`;
        showNotification("error", errorMsg);
        
        // Reload to try next item with fresh token
        setTimeout(() => {
          window.onbeforeunload = null;
          $(window).off("beforeunload");
          window.location.reload();
        }, 1000);
      }
    });
  }

  /**
   * Add item directly without basket logic
   */
  function addItemDirect(itemsId, itemtype, callback) {
    if (!currentCsrfToken) {
      console.error("No CSRF token available for addItemDirect");
      if (callback) callback({ success: false, message: "No CSRF token" });
      return;
    }

    // Adding item

    const formData = new FormData();
    formData.append("ticket_id", config.ticketId);
    formData.append("items_id", itemsId);
    formData.append("itemtype", itemtype);
    formData.append("_glpi_csrf_token", currentCsrfToken);

    fetch(config.pluginRoot + "/ajax/add_item.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        // Update token before parsing response
        updateCsrfToken(response);

        // Check if response is OK
        if (!response.ok) {
          console.error("HTTP error:", response.status);
          return { success: false, message: "HTTP " + response.status };
        }

        return response.json();
      })
      .then((data) => {
        // Add item result
        if (callback) callback(data);
      })
      .catch((error) => {
        console.error("Error adding item:", error);
        if (callback) callback({ success: false, message: error.message });
      });
  }

  // Public API
  return {
    init: init,
    addItem: addItem,
    clearBasket: clearBasket,
    processBasket: processBasket,
  };
})();
