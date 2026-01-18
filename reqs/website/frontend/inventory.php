<?php
// Start output buffering to help prevent header issues.
ob_start();

// Include the utilities and perform the login check.
// If the user is not logged in, they will be redirected before any HTML is output.
require_once __DIR__ . "/../backend/includes/utils.php";
require_once __DIR__ . "/../backend/includes/dbConn.php";

ensureUserIsLoggedIn("inventory");

// Ensure user is not a visitor (only admin and member can access)
ensureUserIsMember($_SESSION['id'], $conn);

// Get user role
$userId = $_SESSION['id'];
$query = "SELECT role FROM identity WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$userRole = $userData['role'] ?? 'member';
$isAdmin = ($userRole === 'admin');
$stmt->close();

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Fetch inventory data using utility function
$inventoryData = getInventoryData($conn);
$inventoryItems = $inventoryData['items'];
$totalItems = $inventoryData['totalItems'];
$availableItems = $inventoryData['availableItems'];
$unavailableItems = $inventoryData['unavailableItems'];
$totalQuantity = $inventoryData['totalQuantity'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--=============== REMIXICONS ===============-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css">
    <!--=============== FONT AWESOME ===============-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!--=============== BOXICONS ===============-->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <!--=============== CSS ===============-->
    <link rel="stylesheet" href="/assets/css/navSideBar.css">
    <link rel="stylesheet" href="/assets/css/notifier.css">
    <link rel="stylesheet" href="/assets/css/inventory.css">
    <link rel="shortcut icon" href="/assets/res/logo/leetmakers.ico" type="image/x-icon" sizes="64x64">
    <title>LEET MAKERS - Inventory</title>
</head>

<body>
    <!-- Include the navigation sidebar -->
    <?php require_once __DIR__ . "/components/navSideBar.php"; ?>
    <ul class="notifications nvsdbr"></ul>
    <div id="session-messages" data-error="<?php echo isset($_SESSION['error']) ? $_SESSION['error'] : ''; ?>"
        data-success="<?php echo isset($_SESSION['success']) ? $_SESSION['success'] : ''; ?>"
        data-warning="<?php echo isset($_SESSION['warning']) ? $_SESSION['warning'] : ''; ?>"
        data-info="<?php echo isset($_SESSION['info']) ? $_SESSION['info'] : ''; ?>">
    </div>
    <?php unset($_SESSION['error'], $_SESSION['success'], $_SESSION['warning'], $_SESSION['info']); ?>

    <main class="main container" id="main">
        <div class="inventory-container">
            <div class="inventory-header">
                <h1 class="inventory-title">Inventory Management</h1>
                <?php if ($isAdmin): ?>
                    <div class="header-actions">
                        <div class="import-dropdown" id="import-dropdown">
                            <button class="btn-import" id="import-btn">
                                <i class="ri-upload-2-line"></i> Import
                                <i class="ri-arrow-down-s-line dropdown-arrow"></i>
                            </button>
                            <div class="import-menu" id="import-menu">
                                <a href="#" class="import-option" data-format="csv">
                                    <i class="ri-file-text-line"></i> Import CSV
                                </a>
                                <a href="#" class="import-option" data-format="json">
                                    <i class="ri-code-s-slash-line"></i> Import JSON
                                </a>
                            </div>
                        </div>
                        <div class="export-dropdown" id="export-dropdown">
                            <button class="btn-export" id="export-btn">
                                <i class="ri-download-2-line"></i> Export
                                <i class="ri-arrow-down-s-line dropdown-arrow"></i>
                            </button>
                            <div class="export-menu" id="export-menu">
                                <a href="#" class="export-option" data-format="csv">
                                    <i class="ri-file-text-line"></i> Export as CSV
                                </a>
                                <a href="#" class="export-option" data-format="json">
                                    <i class="ri-code-s-slash-line"></i> Export as JSON
                                </a>
                            </div>
                        </div>
                        <button class="btn-add-item" id="add-item-btn">
                            <i class="fas fa-plus"></i> Add New Item
                        </button>
                        <button class="btn-clear-inventory" id="clear-inventory-btn" title="Clear all inventory">
                            <i class="ri-delete-bin-7-line"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($isAdmin): ?>
                <div class="inventory-stats">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Items</h3>
                            <p class="stat-number"><?php echo $totalItems; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Available</h3>
                            <p class="stat-number"><?php echo $availableItems; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Unavailable</h3>
                            <p class="stat-number"><?php echo $unavailableItems; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-warning">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Quantity</h3>
                            <p class="stat-number"><?php echo $totalQuantity; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <div class="table-wrapper">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>
                                    <span class="th-content">
                                        ID
                                        <i class="fa-solid fa-angle-down"></i>
                                    </span>
                                </th>
                                <th>
                                    <span class="th-content">
                                        Item Name
                                        <i class="fa-solid fa-angle-down"></i>
                                    </span>
                                </th>
                                <th class="hide-mobile">
                                    <span class="th-content">
                                        Image
                                    </span>
                                </th>
                                <th class="hide-tablet">
                                    <span class="th-content">
                                        Category
                                        <i class="fa-solid fa-angle-down"></i>
                                    </span>
                                </th>
                                <th>
                                    <span class="th-content">
                                        Quantity
                                        <i class="fa-solid fa-angle-down"></i>
                                    </span>
                                </th>
                                <th>
                                    <span class="th-content">
                                        Actions
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($inventoryItems)): ?>
                                <?php foreach ($inventoryItems as $item): ?>
                                    <tr data-id="<?php echo $item['id']; ?>">
                                        <td data-label="ID"><?php echo htmlspecialchars($item['id']); ?></td>
                                        <td data-label="Item Name">
                                            <div class="item-name">
                                                <span><?php echo htmlspecialchars($item['item_name']); ?></span>
                                            </div>
                                        </td>
                                        <td data-label="Image" class="hide-mobile">
                                            <?php if (!empty($item['item_image'])): ?>
                                                <img src="<?php echo htmlspecialchars($item['item_image']); ?>"
                                                    alt="<?php echo htmlspecialchars($item['item_name']); ?>" class="item-image">
                                            <?php else: ?>
                                                <img src="/assets/res/material/no-image.webp" alt="No image" class="item-image">
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Category" class="hide-tablet">
                                            <?php echo htmlspecialchars($item['category'] ?? 'Uncategorized'); ?>
                                        </td>
                                        <td data-label="Quantity">
                                            <?php
                                            $qtyClass = '';
                                            if ($item['quantity'] == 0) {
                                                $qtyClass = 'quantity-low';
                                            } elseif ($item['quantity'] <= 5) {
                                                $qtyClass = 'quantity-warning';
                                            }
                                            ?>
                                            <span class="quantity-badge <?php echo $qtyClass; ?>">
                                                <?php echo htmlspecialchars($item['quantity']); ?>
                                            </span>
                                        </td>
                                        <td data-label="Actions">
                                            <div class="action-buttons">
                                                <?php if ($isAdmin): ?>
                                                    <button class="btn-action btn-edit" title="Edit Item"
                                                        data-id="<?php echo $item['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($item['item_name']); ?>"
                                                        data-category="<?php echo htmlspecialchars($item['category'] ?? ''); ?>"
                                                        data-image="<?php echo htmlspecialchars($item['item_image'] ?? ''); ?>"
                                                        data-quantity="<?php echo $item['quantity']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn-action btn-delete" title="Delete"
                                                        data-id="<?php echo $item['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($item['item_name']); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <?php if ($item['quantity'] > 0): ?>
                                                        <button class="btn-action btn-reserve" title="Reserve"
                                                            data-id="<?php echo $item['id']; ?>">
                                                            <i class="ri-add-line"></i>
                                                        </button>
                                                        <input type="number" class="qty-input" min="1"
                                                            max="<?php echo $item['quantity']; ?>" value="1" title="Quantity">
                                                    <?php else: ?>
                                                        <button class="btn-action btn-reserve" title="Reserve" disabled>
                                                            <i class="ri-add-line"></i>
                                                        </button>
                                                        <input type="number" class="qty-input" min="0" max="0" value="0"
                                                            title="Quantity" disabled>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="empty-state" style="display: none;">
                    <img src="/assets/res/material/no-item.webp" alt="No items" class="empty-state-image">
                    <p class="empty-state-text">No items in inventory</p>
                </div>
            </div>
        </div>
    </main>

    <?php if ($isAdmin): ?>
        <!-- Add Item Modal -->
        <div id="add-item-modal" class="modal">
            <div class="modal-content">
                <i class="ri-close-fill close-modal" data-modal="add-item-modal"></i>
                <h2 class="modal-title">Add New Item</h2>
                <form id="add-item-form" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="image_type" id="image-type" value="default">
                    <input type="hidden" name="default_image" id="selected-default-image">

                    <div class="form-group">
                        <label for="item-name">Item Name <span class="required">*</span></label>
                        <input type="text" id="item-name" name="item_name" required placeholder="Enter item name">
                    </div>

                    <div class="form-group">
                        <label for="item-category">Category<span class="required">*</span></label>
                        <input type="text" id="item-category" name="category" required
                            placeholder="e.g., Microcontrollers, Sensors">
                    </div>

                    <div class="form-group">
                        <label for="item-quantity">Quantity <span class="required">*</span></label>
                        <input type="number" id="item-quantity" name="quantity" min="0" value="1" required>
                    </div>

                    <div class="form-group">
                        <label>Item Image</label>
                        <div class="modal-subtitles">
                            <p class="modal-subtitle active" id="default-img-tab">
                                Default Images
                                <i class="ri-gallery-line"></i>
                            </p>
                            <p class="modal-subtitle" id="upload-img-tab">
                                Upload Image
                                <i class="ri-upload-2-line"></i>
                            </p>
                        </div>
                        <div class="modal-separator"></div>
                        <div class="modal-options">
                            <div class="default-images option active">
                                <div class="default-grid" id="default-images-grid">
                                    <!-- Images will be loaded dynamically -->
                                </div>
                            </div>
                            <div class="upload-image option">
                                <div class="drop-area" id="item-drop-area">
                                    <i class="ri-upload-cloud-2-fill"></i>
                                    <h3>Drag and drop or click to select image</h3>
                                    <p>Image size must be less than <span>2MB</span></p>
                                    <input type="file" name="item_image" id="item-image-upload" accept="image/*" hidden>
                                </div>
                                <div class="upload-preview" id="upload-preview" style="display: none;">
                                    <img src="" alt="Preview" id="preview-image">
                                    <button type="button" class="btn-remove-preview" id="remove-preview">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" data-modal="add-item-modal">Cancel</button>
                        <button type="submit" class="btn-confirm">Add Item</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Item Modal -->
        <div id="edit-item-modal" class="modal">
            <div class="modal-content">
                <i class="ri-close-fill close-modal" data-modal="edit-item-modal"></i>
                <h2 class="modal-title">Edit Item</h2>
                <form id="edit-item-form" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="item_id" id="edit-item-id">
                    <input type="hidden" name="image_type" id="edit-image-type" value="keep">
                    <input type="hidden" name="default_image" id="edit-selected-default-image">

                    <div class="form-group">
                        <label for="edit-item-name">Item Name <span class="required">*</span></label>
                        <input type="text" id="edit-item-name" name="item_name" required placeholder="Enter item name">
                    </div>

                    <div class="form-group">
                        <label for="edit-item-category">Category <span class="required">*</span></label>
                        <input type="text" id="edit-item-category" name="category" required
                            placeholder="e.g., Microcontrollers, Sensors">
                    </div>

                    <div class="form-group">
                        <label for="edit-quantity">Quantity <span class="required">*</span></label>
                        <input type="number" id="edit-quantity" name="quantity" min="0" required>
                    </div>

                    <div class="form-group">
                        <label>Item Image <span class="optional-hint">(leave unchanged to keep current)</span></label>
                        <div class="current-image-preview" id="edit-current-image">
                            <img src="" alt="Current Image" id="edit-current-image-preview">
                            <span>Current Image</span>
                        </div>
                        <div class="modal-subtitles">
                            <p class="modal-subtitle active" id="edit-default-img-tab">
                                Select Default
                                <i class="ri-gallery-line"></i>
                            </p>
                            <p class="modal-subtitle" id="edit-upload-img-tab">
                                Upload New
                                <i class="ri-upload-2-line"></i>
                            </p>
                        </div>
                        <div class="modal-separator"></div>
                        <div class="modal-options">
                            <div class="edit-default-images option active">
                                <div class="default-grid" id="edit-default-images-grid">
                                    <!-- Images will be loaded dynamically -->
                                </div>
                            </div>
                            <div class="edit-upload-image option">
                                <div class="drop-area" id="edit-item-drop-area">
                                    <i class="ri-upload-cloud-2-fill"></i>
                                    <h3>Drag and drop or click to select image</h3>
                                    <p>Image size must be less than <span>2MB</span></p>
                                    <input type="file" name="item_image" id="edit-item-image-upload" accept="image/*"
                                        hidden>
                                </div>
                                <div class="upload-preview" id="edit-upload-preview" style="display: none;">
                                    <img src="" alt="Preview" id="edit-preview-image">
                                    <button type="button" class="btn-remove-preview" id="edit-remove-preview">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" data-modal="edit-item-modal">Cancel</button>
                        <button type="submit" class="btn-confirm">Update Item</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="delete-item-modal" class="modal modal-delete">
            <div class="modal-content">
                <i class="ri-close-fill close-modal" data-modal="delete-item-modal"></i>
                <div class="delete-modal-icon">
                    <i class="ri-delete-bin-line"></i>
                </div>
                <h2 class="modal-title modal-title-danger">Delete Item</h2>
                <form id="delete-item-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="item_id" id="delete-item-id">

                    <p class="delete-warning">
                        Are you sure you want to delete<br><strong id="delete-item-name"></strong>?
                    </p>
                    <p class="warning-subtext">This action cannot be undone.</p>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" data-modal="delete-item-modal">Cancel</button>
                        <button type="submit" class="btn-danger"><i class="ri-delete-bin-line"></i> Delete</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Merge Confirmation Modal -->
        <div id="merge-item-modal" class="modal modal-merge">
            <div class="modal-content">
                <i class="ri-close-fill close-modal" data-modal="merge-item-modal"></i>
                <div class="merge-modal-icon">
                    <i class="ri-git-merge-line"></i>
                </div>
                <h2 class="modal-title">Merge Items?</h2>
                <form id="merge-item-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="merge">
                    <input type="hidden" name="source_id" id="merge-source-id">
                    <input type="hidden" name="target_id" id="merge-target-id">

                    <p class="merge-description" id="merge-description"></p>

                    <div class="merge-preview">
                        <div class="merge-item merge-source">
                            <span class="merge-label">Current item</span>
                            <span class="merge-name" id="merge-source-name"></span>
                            <span class="merge-qty">Qty: <strong id="merge-source-qty"></strong></span>
                        </div>
                        <div class="merge-arrow">
                            <i class="ri-arrow-right-line"></i>
                        </div>
                        <div class="merge-item merge-target">
                            <span class="merge-label">Merges into</span>
                            <span class="merge-name" id="merge-target-name"></span>
                            <span class="merge-qty">Qty: <strong id="merge-target-qty"></strong></span>
                        </div>
                    </div>

                    <div class="merge-result">
                        <i class="ri-checkbox-circle-fill"></i>
                        <span>Combined quantity: <strong id="merge-combined-qty"></strong></span>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" data-modal="merge-item-modal">Keep Separate</button>
                        <button type="submit" class="btn-confirm btn-merge"><i class="ri-git-merge-line"></i> Merge
                            Items</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Clear Inventory Modal -->
        <div id="clear-inventory-modal" class="modal modal-clear">
            <div class="modal-content">
                <i class="ri-close-fill close-modal" data-modal="clear-inventory-modal"></i>
                <div class="clear-modal-icon">
                    <i class="ri-error-warning-line"></i>
                </div>
                <h2 class="modal-title modal-title-danger">Clear Entire Inventory</h2>
                <form id="clear-inventory-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="clear">

                    <div class="clear-warning-box">
                        <i class="ri-alarm-warning-line"></i>
                        <div>
                            <p class="warning-title">This action is irreversible!</p>
                            <p class="warning-text">All inventory items will be permanently deleted. This cannot be undone.
                            </p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="clear-password">Enter your password to confirm</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="clear-password" name="password" required
                                placeholder="Enter your password" autocomplete="current-password">
                            <button type="button" class="toggle-password" tabindex="-1">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" data-modal="clear-inventory-modal">Cancel</button>
                        <button type="submit" class="btn-danger btn-clear-confirm">
                            <i class="ri-delete-bin-7-line"></i> Clear All Items
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Import Modal -->
        <div id="import-item-modal" class="modal modal-import">
            <div class="modal-content">
                <i class="ri-close-fill close-modal" data-modal="import-item-modal"></i>
                <div class="import-modal-icon">
                    <i class="ri-upload-cloud-2-line"></i>
                </div>
                <h2 class="modal-title">Import Inventory</h2>
                <form id="import-item-form" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="import">

                    <p class="import-description">
                        Upload a CSV or JSON file to bulk import inventory items.
                        Existing items with matching name and category will have their quantities updated.
                    </p>

                    <div class="import-format-info">
                        <div class="format-tab active" data-format="csv">
                            <i class="ri-file-text-line"></i>
                            <span>CSV Format</span>
                        </div>
                        <div class="format-tab" data-format="json">
                            <i class="ri-code-s-slash-line"></i>
                            <span>JSON Format</span>
                        </div>
                    </div>

                    <div class="format-example" id="format-example-csv">
                        <p class="format-label">Expected CSV columns:</p>
                        <code>Item Name, Category, Quantity, Image Path</code>
                        <p class="format-hint">First row should contain headers. Image Path is optional.</p>
                    </div>

                    <div class="format-example" id="format-example-json" style="display: none;">
                        <p class="format-label">Expected JSON structure:</p>
                        <code>{"inventory": [{"item_name": "...", "category": "...", "quantity": 0}]}</code>
                        <p class="format-hint">Can also be a direct array of items. item_image is optional.</p>
                    </div>

                    <div class="form-group">
                        <div class="import-drop-area" id="import-drop-area">
                            <i class="ri-file-upload-line"></i>
                            <h3>Drag and drop or click to select file</h3>
                            <p>Supports <span>.csv</span> and <span>.json</span> files</p>
                            <input type="file" name="import_file" id="import-file-input" accept=".csv,.json" hidden>
                        </div>
                        <div class="import-file-preview" id="import-file-preview" style="display: none;">
                            <div class="file-info">
                                <i class="ri-file-text-line" id="import-file-icon"></i>
                                <div class="file-details">
                                    <span class="file-name" id="import-file-name"></span>
                                    <span class="file-size" id="import-file-size"></span>
                                </div>
                            </div>
                            <button type="button" class="btn-remove-file" id="remove-import-file">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" data-modal="import-item-modal">Cancel</button>
                        <button type="submit" class="btn-confirm btn-import-submit" disabled>
                            <i class="ri-upload-2-line"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="modal-overlay" class="modal-overlay"></div>
    <?php endif; ?>

    <script src="/assets/js/navSidebar.js"></script>
    <script src="/assets/js/logout.js"></script>
    <script src="/assets/js/notifier.js"></script>
    <script src="/assets/js/inventory.js"></script>
</body>

</html>
<?php
// Flush the output buffer.
ob_end_flush();
?>

