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
                            <p class="stat-number">5</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Available</h3>
                            <p class="stat-number">4</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Unavailable</h3>
                            <p class="stat-number">1</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-warning">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Quantity</h3>
                            <p class="stat-number">162</p>
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
                            <tr>
                                <td data-label="ID">1</td>
                                <td data-label="Item Name">
                                    <div class="item-name">
                                        <span>Arduino Uno R3</span>
                                    </div>
                                </td>
                                <td data-label="Image" class="hide-mobile">
                                    <img src="/assets/res/material/arduinoUnoR3.webp" alt="Arduino" class="item-image">
                                </td>
                                <td data-label="Category" class="hide-tablet">Development Board</td>
                                <td data-label="Quantity">
                                    <span class="quantity-badge">13</span>
                                </td>
                                <td data-label="Status" class="hide-mobile">
                                    <span class="status-badge status-available">Available</span>
                                </td>
                                <td data-label="Actions">
                                    <div class="action-buttons">
                                        <?php if ($isAdmin): ?>
                                            <button class="btn-action btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-action btn-reserve" title="Reserve">
                                                <i class="ri-add-line"></i>
                                            </button>
                                            <input type="number" class="qty-input" min="1" max="13" value="1"
                                                title="Quantity">
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <!-- led -->
                                <td data-label="ID">2</td>
                                <td data-label="Item Name">
                                    <div class="item-name">
                                        <span>Raspberry PI</span>
                                    </div>
                                </td>
                                <td data-label="Image" class="hide-mobile">
                                    <img src="/assets/res/material/raspberryPi.webp" alt="Raspberry PI"
                                        class="item-image">
                                </td>
                                <td data-label="Category" class="hide-tablet">Development Board</td>
                                <td data-label="Quantity">
                                    <span class="quantity-badge">3</span>
                                </td>
                                <td data-label="Status" class="hide-mobile">
                                    <span class="status-badge status-available">Available</span>
                                </td>
                                <td data-label="Actions">
                                    <div class="action-buttons">
                                        <?php if ($isAdmin): ?>
                                            <button class="btn-action btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-action btn-reserve" title="Reserve">
                                                <i class="ri-add-line"></i>
                                            </button>
                                            <input type="number" class="qty-input" min="1" max="3" value="1"
                                                title="Quantity">
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td data-label="ID">3</td>
                                <td data-label="Item Name">
                                    <div class="item-name">
                                        <span>ESP8266 01 Module</span>
                                    </div>
                                </td>
                                <td data-label="Image" class="hide-mobile">
                                    <img src="/assets/res/material/esp8266 01.webp" alt="ESP8266" class="item-image">
                                </td>
                                <td data-label="Category" class="hide-tablet">Development Board</td>
                                <td data-label="Quantity">
                                    <span class="quantity-badge quantity-low">0</span>
                                </td>
                                <td data-label="Status" class="hide-mobile">
                                    <span class="status-badge status-unavailable">Unavailable</span>
                                </td>
                                <td data-label="Actions">
                                    <div class="action-buttons">
                                        <?php if ($isAdmin): ?>
                                            <button class="btn-action btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-action btn-reserve" title="Reserve" disabled>
                                                <i class="ri-add-line"></i>
                                            </button>
                                            <input type="number" class="qty-input" min="0" max="0" value="0"
                                                title="Quantity" disabled>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <!-- led -->
                                <td data-label="ID">4</td>
                                <td data-label="Item Name">
                                    <div class="item-name">
                                        <span>Red LED</span>
                                    </div>
                                </td>
                                <td data-label="Image" class="hide-mobile">
                                    <img src="/assets/res/material/ledRed.webp" alt="LED" class="item-image">
                                </td>
                                <td data-label="Category" class="hide-tablet">Components</td>
                                <td data-label="Quantity">
                                    <span class="quantity-badge">128</span>
                                </td>
                                <td data-label="Status" class="hide-mobile">
                                    <span class="status-badge status-available">Available</span>
                                </td>
                                <td data-label="Actions">
                                    <div class="action-buttons">
                                        <?php if ($isAdmin): ?>
                                            <button class="btn-action btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-action btn-reserve" title="Reserve">
                                                <i class="ri-add-line"></i>
                                            </button>
                                            <input type="number" class="qty-input" min="1" max="128" value="1"
                                                title="Quantity">
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <!-- led -->
                                <td data-label="ID">5</td>
                                <td data-label="Item Name">
                                    <div class="item-name">
                                        <span>Blue LED</span>
                                    </div>
                                </td>
                                <td data-label="Image" class="hide-mobile">
                                    <img src="/assets/res/material/ledBlue.webp" alt="LED" class="item-image">
                                </td>
                                <td data-label="Category" class="hide-tablet">Components</td>
                                <td data-label="Quantity">
                                    <span class="quantity-badge">256</span>
                                </td>
                                <td data-label="Status" class="hide-mobile">
                                    <span class="status-badge status-available">Available</span>
                                </td>
                                <td data-label="Actions">
                                    <div class="action-buttons">
                                        <?php if ($isAdmin): ?>
                                            <button class="btn-action btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-action btn-reserve" title="Reserve">
                                                <i class="ri-add-line"></i>
                                            </button>
                                            <input type="number" class="qty-input" min="1" max="256" value="1"
                                                title="Quantity">
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <!-- led -->
                                <td data-label="ID">6</td>
                                <td data-label="Item Name">
                                    <div class="item-name">
                                        <span>Green LED</span>
                                    </div>
                                </td>
                                <td data-label="Image" class="hide-mobile">
                                    <img src="/assets/res/material/ledGreen.webp" alt="LED" class="item-image">
                                </td>
                                <td data-label="Category" class="hide-tablet">Components</td>
                                <td data-label="Quantity">
                                    <span class="quantity-badge">96</span>
                                </td>
                                <td data-label="Status" class="hide-mobile">
                                    <span class="status-badge status-available">Available</span>
                                </td>
                                <td data-label="Actions">
                                    <div class="action-buttons">
                                        <?php if ($isAdmin): ?>
                                            <button class="btn-action btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-action btn-reserve" title="Reserve">
                                                <i class="ri-add-line"></i>
                                            </button>
                                            <input type="number" class="qty-input" min="1" max="96" value="1"
                                                title="Quantity">
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <!-- led -->
                                <td data-label="ID">7</td>
                                <td data-label="Item Name">
                                    <div class="item-name">
                                        <span>White LED</span>
                                    </div>
                                </td>
                                <td data-label="Image" class="hide-mobile">
                                    <img src="/assets/res/material/ledWhite.webp" alt="LED" class="item-image">
                                </td>
                                <td data-label="Category" class="hide-tablet">Components</td>
                                <td data-label="Quantity">
                                    <span class="quantity-badge">32</span>
                                </td>
                                <td data-label="Status" class="hide-mobile">
                                    <span class="status-badge status-available">Available</span>
                                </td>
                                <td data-label="Actions">
                                    <div class="action-buttons">
                                        <?php if ($isAdmin): ?>
                                            <button class="btn-action btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-action btn-reserve" title="Reserve">
                                                <i class="ri-add-line"></i>
                                            </button>
                                            <input type="number" class="qty-input" min="1" max="32" value="1"
                                                title="Quantity">
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <!-- led -->
                                <td data-label="ID">8</td>
                                <td data-label="Item Name">
                                    <div class="item-name">
                                        <span>Orange LED</span>
                                    </div>
                                </td>
                                <td data-label="Image" class="hide-mobile">
                                    <img src="/assets/res/material/ledOrange.webp" alt="LED" class="item-image">
                                </td>
                                <td data-label="Category" class="hide-tablet">Components</td>
                                <td data-label="Quantity">
                                    <span class="quantity-badge">24</span>
                                </td>
                                <td data-label="Status" class="hide-mobile">
                                    <span class="status-badge status-available">Available</span>
                                </td>
                                <td data-label="Actions">
                                    <div class="action-buttons">
                                        <?php if ($isAdmin): ?>
                                            <button class="btn-action btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-action btn-reserve" title="Reserve">
                                                <i class="ri-add-line"></i>
                                            </button>
                                            <input type="number" class="qty-input" min="1" max="24" value="1"
                                                title="Quantity">
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <!-- motorServo -->
                                <td data-label="ID">9</td>
                                <td data-label="Item Name">
                                    <div class="item-name">
                                        <span>Servo Motor 180deg</span>
                                    </div>
                                </td>
                                <td data-label="Image" class="hide-mobile">
                                    <img src="/assets/res/material/motorServoDFRobotKit.webp" alt="Motor & Servo Kit"
                                        class="item-image">
                                </td>
                                <td data-label="Category" class="hide-tablet">Motors</td>
                                <td data-label="Quantity">
                                    <span class="quantity-badge">17</span>
                                </td>
                                <td data-label="Status" class="hide-mobile">
                                    <span class="status-badge status-available">Available</span>
                                </td>
                                <td data-label="Actions">
                                    <div class="action-buttons">
                                        <?php if ($isAdmin): ?>
                                            <button class="btn-action btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-action btn-reserve" title="Reserve">
                                                <i class="ri-add-line"></i>
                                            </button>
                                            <input type="number" class="qty-input" min="1" max="17" value="1"
                                                title="Quantity">
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td data-label="ID">10</td>
                                <td data-label="Item Name">
                                    <div class="item-name">
                                        <span>Breadboard</span>
                                    </div>
                                </td>
                                <td data-label="Image" class="hide-mobile">
                                    <img src="/assets/res/material/breadboardSmall.webp" alt="Breadboard"
                                        class="item-image">
                                </td>
                                <td data-label="Category" class="hide-tablet">Accessories</td>
                                <td data-label="Quantity">
                                    <span class="quantity-badge quantity-warning">4</span>
                                </td>
                                <td data-label="Status" class="hide-mobile">
                                    <span class="status-badge status-available">Available</span>
                                </td>
                                <td data-label="Actions">
                                    <div class="action-buttons">
                                        <?php if ($isAdmin): ?>
                                            <button class="btn-action btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-action btn-reserve" title="Reserve">
                                                <i class="ri-add-line"></i>
                                            </button>
                                            <input type="number" class="qty-input" min="1" max="4" value="1"
                                                title="Quantity">
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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

