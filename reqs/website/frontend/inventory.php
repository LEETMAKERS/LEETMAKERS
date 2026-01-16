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
                    <button class="btn-add-item" id="add-item-btn">
                        <i class="fas fa-plus"></i> Add New Item
                    </button>
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
                                <th class="hide-mobile">
                                    <span class="th-content">
                                        Status
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
                                                <img src="/assets/res/material/no-item.webp" alt="No image" class="item-image">
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
                                        <td data-label="Status" class="hide-mobile">
                                            <?php
                                            $statusClass = $item['status'] === 'available' ? 'status-available' : 'status-unavailable';
                                            $statusText = ucfirst($item['status']);
                                            ?>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo $statusText; ?>
                                            </span>
                                        </td>
                                        <td data-label="Actions">
                                            <div class="action-buttons">
                                                <?php if ($isAdmin): ?>
                                                    <button class="btn-action btn-edit" title="Edit"
                                                        data-id="<?php echo $item['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn-action btn-delete" title="Delete"
                                                        data-id="<?php echo $item['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <?php if ($item['status'] === 'available' && $item['quantity'] > 0): ?>
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

