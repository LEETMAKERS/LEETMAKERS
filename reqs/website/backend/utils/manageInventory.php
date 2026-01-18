<?php
/**
 * Inventory Management API
 * Handles CRUD operations for inventory items (admin only)
 * 
 * Actions: add, update, delete
 * On delete: Re-indexes all item IDs to maintain sequential order
 */

session_start();
require_once __DIR__ . "/../includes/dbConn.php";
require_once __DIR__ . "/../includes/utils.php";

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
ensureUserIsLoggedIn();

// Check if user is admin
$userId = $_SESSION['id'];
$query = "SELECT role FROM identity WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

if (!$userData || $userData['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Forbidden: Admin access required']);
    exit();
}

// Get the action
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        addItem($conn);
        break;
    case 'update':
        updateItem($conn);
        break;
    case 'merge':
        mergeItems($conn);
        break;
    case 'delete':
        deleteItem($conn);
        break;
    case 'get':
        getItem($conn);
        break;
    case 'getDefaultImages':
        getDefaultImages();
        break;
    case 'export':
        exportInventory($conn);
        break;
    case 'import':
        importInventory($conn);
        break;
    case 'clear':
        clearInventory($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit();
}

/**
 * Add a new inventory item or update quantity if item already exists
 */
function addItem($conn)
{
    $itemName = validate($_POST['item_name'] ?? '');
    $category = validate($_POST['category'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 0);
    $imageType = $_POST['image_type'] ?? 'default'; // 'default' or 'upload'
    $defaultImage = $_POST['default_image'] ?? '';

    if (empty($itemName)) {
        echo json_encode(['success' => false, 'message' => 'Warning: Item name is required']);
        exit();
    }

    if ($quantity < 0) {
        echo json_encode(['success' => false, 'message' => 'Warning: Quantity cannot be negative']);
        exit();
    }

    // Check if item with same name AND category already exists
    $checkQuery = "SELECT id, item_name, category, quantity FROM inventory WHERE LOWER(item_name) = LOWER(?) AND LOWER(category) = LOWER(?)";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ss", $itemName, $category);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $existingItem = $checkResult->fetch_assoc();
    $checkStmt->close();

    if ($existingItem) {
        // Item exists in same category - update quantity by adding to existing
        $newQuantity = $existingItem['quantity'] + $quantity;
        $updateQuery = "UPDATE inventory SET quantity = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ii", $newQuantity, $existingItem['id']);

        if ($updateStmt->execute()) {
            $updateStmt->close();

            // Log the action
            recordLogs("Admin updated inventory item quantity: {$existingItem['item_name']} in {$existingItem['category']} (ID: {$existingItem['id']}) added $quantity, new total: $newQuantity", 'INFO');

            echo json_encode([
                'success' => true,
                'message' => "Success: Item already exists. Updated quantity to $newQuantity",
                'updated' => true,
                'item' => [
                    'id' => $existingItem['id'],
                    'item_name' => $existingItem['item_name'],
                    'category' => $existingItem['category'],
                    'quantity' => $newQuantity
                ]
            ]);
        } else {
            $updateStmt->close();
            echo json_encode(['success' => false, 'message' => 'Error: Failed to update item quantity: ' . $conn->error]);
        }
        return;
    }

    // Item doesn't exist - create new item
    // Handle image
    $itemImage = null;
    if ($imageType === 'default' && !empty($defaultImage)) {
        $itemImage = '/assets/res/material/' . basename($defaultImage);
    } elseif ($imageType === 'upload' && isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleImageUpload($_FILES['item_image']);
        if ($uploadResult['success']) {
            $itemImage = $uploadResult['path'];
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $uploadResult['message']]);
            exit();
        }
    }

    // Insert item
    $query = "INSERT INTO inventory (item_name, item_image, category, quantity) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $itemName, $itemImage, $category, $quantity);

    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        $stmt->close();

        // Log the action
        recordLogs("Admin added inventory item: $itemName (ID: $newId)", 'INFO');

        echo json_encode([
            'success' => true,
            'message' => 'Success: Item added successfully!',
            'updated' => false,
            'item' => [
                'id' => $newId,
                'item_name' => $itemName,
                'item_image' => $itemImage,
                'category' => $category,
                'quantity' => $quantity
            ]
        ]);
    } else {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Error: Failed to add item: ' . $conn->error]);
    }
}

/**
 * Update an existing inventory item (all fields)
 */
function updateItem($conn)
{
    $itemId = intval($_POST['item_id'] ?? 0);
    $itemName = validate($_POST['item_name'] ?? '');
    $category = validate($_POST['category'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 0);
    $imageType = $_POST['image_type'] ?? 'keep'; // 'keep', 'default', or 'upload'
    $defaultImage = $_POST['default_image'] ?? '';

    if ($itemId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Error: Invalid item ID']);
        exit();
    }

    if (empty($itemName)) {
        echo json_encode(['success' => false, 'message' => 'Error: Item name is required']);
        exit();
    }

    if ($quantity < 0) {
        echo json_encode(['success' => false, 'message' => 'Warning: Quantity cannot be negative']);
        exit();
    }

    // Check if item exists
    $checkQuery = "SELECT id, item_name, item_image, category, quantity FROM inventory WHERE id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $itemId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $existingItem = $checkResult->fetch_assoc();
    $checkStmt->close();

    if (!$existingItem) {
        echo json_encode(['success' => false, 'message' => 'Error: Item not found']);
        exit();
    }

    // Check if another item with same name + category already exists (excluding current item)
    $duplicateQuery = "SELECT id, item_name, category, quantity FROM inventory WHERE LOWER(item_name) = LOWER(?) AND LOWER(category) = LOWER(?) AND id != ?";
    $duplicateStmt = $conn->prepare($duplicateQuery);
    $duplicateStmt->bind_param("ssi", $itemName, $category, $itemId);
    $duplicateStmt->execute();
    $duplicateResult = $duplicateStmt->get_result();
    $duplicateItem = $duplicateResult->fetch_assoc();
    $duplicateStmt->close();

    if ($duplicateItem) {
        // Return conflict info for frontend to show merge confirmation
        $combinedQuantity = $existingItem['quantity'] + $duplicateItem['quantity'];
        echo json_encode([
            'success' => false,
            'conflict' => true,
            'message' => "An item '{$duplicateItem['item_name']}' already exists in '{$duplicateItem['category']}' with quantity {$duplicateItem['quantity']}.",
            'sourceItem' => [
                'id' => $itemId,
                'item_name' => $existingItem['item_name'],
                'quantity' => $existingItem['quantity']
            ],
            'targetItem' => [
                'id' => $duplicateItem['id'],
                'item_name' => $duplicateItem['item_name'],
                'category' => $duplicateItem['category'],
                'quantity' => $duplicateItem['quantity']
            ],
            'combinedQuantity' => $combinedQuantity
        ]);
        exit();
    }

    // Handle image
    $itemImage = $existingItem['item_image']; // Keep existing by default
    if ($imageType === 'default' && !empty($defaultImage)) {
        $itemImage = '/assets/res/material/' . basename($defaultImage);
    } elseif ($imageType === 'upload' && isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleImageUpload($_FILES['item_image']);
        if ($uploadResult['success']) {
            // Delete old uploaded image if exists
            if ($existingItem['item_image'] && strpos($existingItem['item_image'], '/assets/res/material/uploads/') !== false) {
                $oldImagePath = $_SERVER['DOCUMENT_ROOT'] . $existingItem['item_image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $itemImage = $uploadResult['path'];
        } else {
            echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
            exit();
        }
    }

    // Update item
    $query = "UPDATE inventory SET item_name = ?, item_image = ?, category = ?, quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssii", $itemName, $itemImage, $category, $quantity, $itemId);

    if ($stmt->execute()) {
        $stmt->close();

        // Log the action
        recordLogs("Admin updated inventory item: $itemName (ID: $itemId)", 'INFO');

        echo json_encode([
            'success' => true,
            'message' => 'Item updated successfully',
            'item' => [
                'id' => $itemId,
                'item_name' => $itemName,
                'item_image' => $itemImage,
                'category' => $category,
                'quantity' => $quantity
            ]
        ]);
    } else {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Error: Failed to update item: ' . $conn->error]);
    }
}

/**
 * Merge two inventory items into one
 * Adds quantities together and deletes the source item
 */
function mergeItems($conn)
{
    $sourceId = intval($_POST['source_id'] ?? 0);
    $targetId = intval($_POST['target_id'] ?? 0);

    if ($sourceId <= 0 || $targetId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Error: Invalid item IDs']);
        exit();
    }

    if ($sourceId === $targetId) {
        echo json_encode(['success' => false, 'message' => 'Error: Cannot merge item with itself']);
        exit();
    }

    // Get source item
    $sourceQuery = "SELECT id, item_name, item_image, quantity FROM inventory WHERE id = ?";
    $sourceStmt = $conn->prepare($sourceQuery);
    $sourceStmt->bind_param("i", $sourceId);
    $sourceStmt->execute();
    $sourceResult = $sourceStmt->get_result();
    $sourceItem = $sourceResult->fetch_assoc();
    $sourceStmt->close();

    if (!$sourceItem) {
        echo json_encode(['success' => false, 'message' => 'Error: Source item not found']);
        exit();
    }

    // Get target item
    $targetQuery = "SELECT id, item_name, category, quantity FROM inventory WHERE id = ?";
    $targetStmt = $conn->prepare($targetQuery);
    $targetStmt->bind_param("i", $targetId);
    $targetStmt->execute();
    $targetResult = $targetStmt->get_result();
    $targetItem = $targetResult->fetch_assoc();
    $targetStmt->close();

    if (!$targetItem) {
        echo json_encode(['success' => false, 'message' => 'Error: Target item not found']);
        exit();
    }

    // Calculate combined quantity
    $combinedQuantity = $sourceItem['quantity'] + $targetItem['quantity'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update target item quantity
        $updateQuery = "UPDATE inventory SET quantity = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ii", $combinedQuantity, $targetId);
        if (!$updateStmt->execute()) {
            throw new Exception('Failed to update target item');
        }
        $updateStmt->close();

        // Delete source item
        $deleteQuery = "DELETE FROM inventory WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("i", $sourceId);
        if (!$deleteStmt->execute()) {
            throw new Exception('Failed to delete source item');
        }
        $deleteStmt->close();

        // Delete source item's uploaded image if exists
        if ($sourceItem['item_image'] && strpos($sourceItem['item_image'], '/assets/res/material/uploads/') !== false) {
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . $sourceItem['item_image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Commit transaction
        $conn->commit();

        // Re-index IDs
        reindexInventoryIds($conn);

        // Log the action
        recordLogs("Admin merged inventory items: '{$sourceItem['item_name']}' (ID: $sourceId) into '{$targetItem['item_name']}' (ID: $targetId). Combined quantity: $combinedQuantity", 'INFO');

        echo json_encode([
            'success' => true,
            'message' => "Items merged! '{$targetItem['item_name']}' now has quantity $combinedQuantity",
            'item' => [
                'id' => $targetId,
                'item_name' => $targetItem['item_name'],
                'category' => $targetItem['category'],
                'quantity' => $combinedQuantity
            ]
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

/**
 * Delete an inventory item and re-index all IDs
 */
function deleteItem($conn)
{
    $itemId = intval($_POST['item_id'] ?? 0);

    if ($itemId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Error: Invalid item ID']);
        exit();
    }

    // Check if item exists and get its image
    $checkQuery = "SELECT id, item_name, item_image FROM inventory WHERE id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $itemId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $existingItem = $checkResult->fetch_assoc();
    $checkStmt->close();

    if (!$existingItem) {
        echo json_encode(['success' => false, 'message' => 'Error: Item not found']);
        exit();
    }

    // Delete the item
    $deleteQuery = "DELETE FROM inventory WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $itemId);

    if (!$deleteStmt->execute()) {
        $deleteStmt->close();
        echo json_encode(['success' => false, 'message' => 'Error: Failed to delete item: ' . $conn->error]);
        exit();
    }
    $deleteStmt->close();

    // Delete uploaded image if it's not a default image
    if ($existingItem['item_image'] && strpos($existingItem['item_image'], '/assets/res/material/') === false) {
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . $existingItem['item_image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Re-index all IDs to maintain sequential order
    reindexInventoryIds($conn);

    // Log the action
    recordLogs("Admin deleted inventory item: {$existingItem['item_name']} (ID: $itemId)", 'INFO');

    echo json_encode([
        'success' => true,
        'message' => 'Success: Item deleted successfully'
    ]);
}

/**
 * Get a single inventory item
 */
function getItem($conn)
{
    $itemId = intval($_GET['item_id'] ?? 0);

    if ($itemId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Error: Invalid item ID']);
        exit();
    }

    $query = "SELECT id, item_name, item_image, category, quantity FROM inventory WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();

    if ($item) {
        echo json_encode(['success' => true, 'item' => $item]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: Item not found']);
    }
}

/**
 * Export inventory data as CSV or JSON
 */
function exportInventory($conn)
{
    $format = $_GET['format'] ?? 'csv';

    // Fetch all inventory items
    $query = "SELECT id, item_name, category, quantity, item_image FROM inventory ORDER BY id ASC";
    $result = $conn->query($query);

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $result->free();

    $filename = 'inventory_' . date('Y-m-d_H-i-s');

    if ($format === 'json') {
        // Export as JSON
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '.json"');
        header('Cache-Control: no-cache, must-revalidate');

        echo json_encode([
            'exported_at' => date('Y-m-d H:i:s'),
            'total_items' => count($items),
            'inventory' => $items
        ], JSON_PRETTY_PRINT);
    } else {
        // Export as CSV (default)
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Cache-Control: no-cache, must-revalidate');

        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write header row
        fputcsv($output, ['ID', 'Item Name', 'Category', 'Quantity', 'Image Path']);

        // Write data rows
        foreach ($items as $item) {
            fputcsv($output, [
                $item['id'],
                $item['item_name'],
                $item['category'],
                $item['quantity'],
                $item['item_image']
            ]);
        }

        fclose($output);
    }

    // Log the export action
    recordLogs("Admin exported inventory data as " . strtoupper($format) . " (" . count($items) . " items)", 'INFO');

    exit();
}

/**
 * Import inventory data from CSV or JSON file
 */
function importInventory($conn)
{
    // Check if file was uploaded
    if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        $errorCode = $_FILES['import_file']['error'] ?? UPLOAD_ERR_NO_FILE;
        $message = $errorMessages[$errorCode] ?? 'Unknown upload error';
        echo json_encode(['success' => false, 'message' => "Error: $message"]);
        exit();
    }

    $file = $_FILES['import_file'];
    $filename = $file['name'];
    $tmpPath = $file['tmp_name'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Validate file type
    if (!in_array($extension, ['csv', 'json'])) {
        echo json_encode(['success' => false, 'message' => 'Error: Only CSV and JSON files are supported']);
        exit();
    }

    // Read file content
    $content = file_get_contents($tmpPath);
    if ($content === false) {
        echo json_encode(['success' => false, 'message' => 'Error: Failed to read file']);
        exit();
    }

    // Parse file based on format
    $items = [];
    if ($extension === 'json') {
        $items = parseJsonImport($content);
    } else {
        $items = parseCsvImport($content);
    }

    if ($items === false) {
        echo json_encode(['success' => false, 'message' => 'Error: Failed to parse file. Please check the file format']);
        exit();
    }

    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Warning: No valid items found in the file']);
        exit();
    }

    // Import items
    $result = importItems($conn, $items);

    // Log the action
    recordLogs("Admin imported inventory data from " . strtoupper($extension) . " ({$result['added']} added, {$result['updated']} updated, {$result['failed']} failed)", 'INFO');

    echo json_encode([
        'success' => true,
        'message' => "Import complete: {$result['added']} items added, {$result['updated']} items updated" . ($result['failed'] > 0 ? ", {$result['failed']} failed" : ""),
        'details' => $result
    ]);
}

/**
 * Parse JSON import file
 */
function parseJsonImport($content)
{
    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return false;
    }

    // Handle both formats: direct array or wrapped in 'inventory' key
    if (isset($data['inventory']) && is_array($data['inventory'])) {
        return normalizeImportItems($data['inventory']);
    } elseif (is_array($data) && !isset($data['inventory'])) {
        // Check if it's a direct array of items
        if (isset($data[0]) || empty($data)) {
            return normalizeImportItems($data);
        }
    }

    return false;
}

/**
 * Parse CSV import file
 */
function parseCsvImport($content)
{
    // Remove UTF-8 BOM if present
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

    $lines = preg_split('/\r\n|\r|\n/', $content);
    $lines = array_filter($lines, fn($line) => trim($line) !== '');

    if (count($lines) < 2) {
        return []; // No data rows
    }

    // Parse header row
    $headerLine = array_shift($lines);
    $headers = str_getcsv($headerLine);
    $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

    // Map column names to expected fields
    $columnMap = [
        'item_name' => ['item_name', 'item name', 'name', 'itemname'],
        'category' => ['category', 'cat', 'type'],
        'quantity' => ['quantity', 'qty', 'count', 'amount'],
        'item_image' => ['item_image', 'item image', 'image', 'image_path', 'image path', 'imagepath']
    ];

    // Find column indices
    $indices = [];
    foreach ($columnMap as $field => $possibleNames) {
        foreach ($possibleNames as $name) {
            $index = array_search($name, $headers);
            if ($index !== false) {
                $indices[$field] = $index;
                break;
            }
        }
    }

    // item_name is required
    if (!isset($indices['item_name'])) {
        return false;
    }

    // Parse data rows
    $items = [];
    foreach ($lines as $line) {
        $row = str_getcsv($line);
        if (count($row) < count($headers)) {
            continue; // Skip malformed rows
        }

        $item = [
            'item_name' => isset($indices['item_name']) ? trim($row[$indices['item_name']]) : '',
            'category' => isset($indices['category']) ? trim($row[$indices['category']]) : '',
            'quantity' => isset($indices['quantity']) ? intval($row[$indices['quantity']]) : 0,
            'item_image' => isset($indices['item_image']) ? trim($row[$indices['item_image']]) : null
        ];

        if (!empty($item['item_name'])) {
            $items[] = $item;
        }
    }

    return $items;
}

/**
 * Normalize import items to ensure consistent structure
 */
function normalizeImportItems($items)
{
    $normalized = [];
    foreach ($items as $item) {
        // Handle different possible key names
        $itemName = $item['item_name'] ?? $item['name'] ?? $item['itemName'] ?? '';
        $category = $item['category'] ?? $item['cat'] ?? $item['type'] ?? '';
        $quantity = $item['quantity'] ?? $item['qty'] ?? $item['count'] ?? 0;
        $itemImage = $item['item_image'] ?? $item['image'] ?? $item['imagePath'] ?? null;

        if (!empty($itemName)) {
            $normalized[] = [
                'item_name' => trim($itemName),
                'category' => trim($category),
                'quantity' => intval($quantity),
                'item_image' => $itemImage ? trim($itemImage) : null
            ];
        }
    }
    return $normalized;
}

/**
 * Import items into the database
 */
function importItems($conn, $items)
{
    $added = 0;
    $updated = 0;
    $failed = 0;
    $errors = [];

    foreach ($items as $item) {
        $itemName = validate($item['item_name']);
        $category = validate($item['category']);
        $quantity = intval($item['quantity']);
        $itemImage = $item['item_image'];

        if (empty($itemName)) {
            $failed++;
            continue;
        }

        if ($quantity < 0) {
            $quantity = 0;
        }

        // Check if item with same name AND category already exists
        $checkQuery = "SELECT id, quantity FROM inventory WHERE LOWER(item_name) = LOWER(?) AND LOWER(category) = LOWER(?)";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("ss", $itemName, $category);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $existingItem = $checkResult->fetch_assoc();
        $checkStmt->close();

        if ($existingItem) {
            // Update existing item - add quantities
            $newQuantity = $existingItem['quantity'] + $quantity;
            $updateQuery = "UPDATE inventory SET quantity = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("ii", $newQuantity, $existingItem['id']);

            if ($updateStmt->execute()) {
                $updated++;
            } else {
                $failed++;
                $errors[] = "Failed to update: $itemName";
            }
            $updateStmt->close();
        } else {
            // Insert new item
            $query = "INSERT INTO inventory (item_name, item_image, category, quantity) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $itemName, $itemImage, $category, $quantity);

            if ($stmt->execute()) {
                $added++;
            } else {
                $failed++;
                $errors[] = "Failed to add: $itemName";
            }
            $stmt->close();
        }
    }

    return [
        'added' => $added,
        'updated' => $updated,
        'failed' => $failed,
        'errors' => $errors,
        'total' => count($items)
    ];
}

/**
 * Clear all inventory items (requires password verification)
 */
function clearInventory($conn)
{
    global $userId;

    // Get password from POST
    $password = $_POST['password'] ?? '';

    if (empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Error: Password is required to clear inventory']);
        exit();
    }

    // Verify user's password
    $query = "SELECT password FROM identity WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Error: Incorrect password']);
        exit();
    }

    // Get count of items before deletion
    $countResult = $conn->query("SELECT COUNT(*) as count FROM inventory");
    $itemCount = $countResult->fetch_assoc()['count'];
    $countResult->free();

    if ($itemCount == 0) {
        echo json_encode(['success' => false, 'message' => 'Warning: Inventory is already empty']);
        exit();
    }

    // Get all uploaded images to delete
    $imagesQuery = "SELECT item_image FROM inventory WHERE item_image LIKE '%/uploads/%'";
    $imagesResult = $conn->query($imagesQuery);
    $uploadedImages = [];
    while ($row = $imagesResult->fetch_assoc()) {
        if ($row['item_image']) {
            $uploadedImages[] = $row['item_image'];
        }
    }
    $imagesResult->free();

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete all inventory items
        if (!$conn->query("DELETE FROM inventory")) {
            throw new Exception('Failed to delete inventory items');
        }

        // Reset auto increment
        if (!$conn->query("ALTER TABLE inventory AUTO_INCREMENT = 1")) {
            throw new Exception('Failed to reset auto increment');
        }

        // Commit transaction
        $conn->commit();

        // Delete uploaded images after successful database operation
        foreach ($uploadedImages as $imagePath) {
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        // Log the action
        recordLogs("Admin cleared entire inventory ($itemCount items deleted)", 'WARNING');

        echo json_encode([
            'success' => true,
            'message' => "Success: Inventory cleared! $itemCount items deleted.",
            'deleted_count' => $itemCount
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

/**
 * Get list of default material images
 */
function getDefaultImages()
{
    $imagesDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/res/material/';
    $images = [];

    if (is_dir($imagesDir)) {
        $files = scandir($imagesDir);
        foreach ($files as $file) {
            if (preg_match('/\.(webp|png|jpg|jpeg|gif)$/i', $file) && $file !== 'no-item.webp') {
                // Generate a human-readable display name from filename
                $displayName = generateDisplayName($file);
                $images[] = [
                    'name' => $file,
                    'path' => '/assets/res/material/' . $file,
                    'displayName' => $displayName
                ];
            }
        }
    }

    echo json_encode(['success' => true, 'images' => $images]);
}

/**
 * Generate a human-readable display name from a filename
 * e.g., "displayLCD16x2I2C.webp" -> "Display LCD 16x2 I2C"
 */
function generateDisplayName($filename)
{
    // Remove file extension
    $name = pathinfo($filename, PATHINFO_FILENAME);

    // Insert space before uppercase letters and numbers (camelCase to words)
    $name = preg_replace('/([a-z])([A-Z])/', '$1 $2', $name);
    $name = preg_replace('/([a-zA-Z])(\d)/', '$1 $2', $name);
    $name = preg_replace('/(\d)([a-zA-Z])/', '$1 $2', $name);

    // Handle common abbreviations - keep them uppercase
    $abbreviations = ['LCD', 'LED', 'RGB', 'I2C', 'SPI', 'USB', 'DC', 'AC', 'IR', 'NPN', 'PNP', 'SPDT', 'DPDT', 'SPST', 'IC', 'RFID'];

    // Capitalize first letter of each word
    $name = ucwords(strtolower($name));

    // Restore abbreviations to uppercase
    foreach ($abbreviations as $abbr) {
        $name = preg_replace('/\b' . ucfirst(strtolower($abbr)) . '\b/i', $abbr, $name);
    }

    // Fix common patterns
    $name = str_replace(['x 2', 'x 4', 'x 6', 'x 8', 'x 12'], ['x2', 'x4', 'x6', 'x8', 'x12'], $name);
    $name = preg_replace('/(\d) X (\d)/i', '$1x$2', $name);

    return trim($name);
}

/**
 * Re-index all inventory IDs to maintain sequential order
 */
function reindexInventoryIds($conn)
{
    // Get all items ordered by current ID
    $selectQuery = "SELECT id FROM inventory ORDER BY id ASC";
    $result = $conn->query($selectQuery);

    if (!$result || $result->num_rows === 0) {
        return;
    }

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row['id'];
    }
    $result->free();

    // Disable foreign key checks temporarily
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    // Update each item's ID sequentially
    $newId = 1;
    foreach ($items as $oldId) {
        if ($oldId !== $newId) {
            $updateQuery = "UPDATE inventory SET id = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("ii", $newId, $oldId);
            $updateStmt->execute();
            $updateStmt->close();
        }
        $newId++;
    }

    // Reset auto increment to next available ID
    $conn->query("ALTER TABLE inventory AUTO_INCREMENT = $newId");

    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
}

/**
 * Handle image upload
 */
function handleImageUpload($file)
{
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    // Validate file type
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Error: Invalid file type. Allowed: JPG, PNG, GIF, WEBP'];
    }

    // Validate file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Error: File size exceeds 2MB limit'];
    }

    // Create upload directory if it doesn't exist
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/res/material/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'item_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $uploadPath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'path' => '/assets/res/material/uploads/' . $filename];
    } else {
        return ['success' => false, 'message' => 'Error: Failed to upload image'];
    }
}
