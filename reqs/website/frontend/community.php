<?php
// Start output buffering to help prevent header issues.
ob_start();

// Include the utilities and perform the login check.
// If the user is not logged in, they will be redirected before any HTML is output.
require_once __DIR__ . "/../backend/includes/utils.php";
ensureUserIsLoggedIn("community");
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
   <!--=============== CSS ===============-->
   <link rel="stylesheet" href="/assets/css/navSideBar.css">
   <link rel="stylesheet" href="/assets/css/notifier.css">
   <link rel="shortcut icon" href="/assets/res/logo/leetmakers.ico" type="image/x-icon" sizes="64x64">
   <title>LEET MAKERS - Community</title>
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
      <h1>Welcome to your Community Page</h1>
   </main>
   <script src="/assets/js/navSidebar.js"></script>
   <script src="/assets/js/logout.js"></script>
   <script src="/assets/js/notifier.js"></script>
</body>

</html>
<?php
// Flush the output buffer.
ob_end_flush();
?>

