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
                            <p class="stat-number">0</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Available</h3>
                            <p class="stat-number">0</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Unavailable</h3>
                            <p class="stat-number">0</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-warning">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Quantity</h3>
                            <p class="stat-number">0</p>
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
                            <!-- Inventory items will be loaded dynamically from the database -->
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

