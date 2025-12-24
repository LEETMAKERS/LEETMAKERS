<?php
// Start output buffering to help prevent header issues.
ob_start();

// Include the utilities and perform the login check.
// If the user is not logged in, they will be redirected before any HTML is output.
require_once __DIR__ . "/../backend/includes/utils.php";
ensureUserIsLoggedIn("profile");

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
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
    <link rel="stylesheet" href="/assets/css/profile.css">
    <link rel="shortcut icon" href="/assets/res/logo/leetmakers.ico" type="image/x-icon" sizes="64x64">
    <title>LEET MAKERS - Profile</title>
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
        <div class="profile-container">
            <form action="/utils/updateProfile" method="POST" enctype="multipart/form-data" class="profile-form"
                autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="default_picture" id="default-picture">

                <div class="profile-picture">
                    <img src="<?= htmlspecialchars($userDetails['avatar']); ?>" alt="Profile Picture"
                        id="current-avatar">
                    <label class="upload-icon" id="open-profile-modal">
                        <i class="ri-exchange-2-line"></i>
                    </label>
                </div>

                <div id="profile-pic-modal" class="modal">
                    <div class="modal-content">
                        <i class="ri-close-fill close-modal"></i>
                        <h2 class="modal-title">Change Profile Picture</h2>
                        <div class="modal-subtitles">
                            <p class="modal-subtitle" id="ilstr-img">
                                Illustrations
                                <i class="ri-palette-line"></i>
                            </p>
                            <p class="modal-subtitle" id="dflt-img">
                                From Computer
                                <i class="ri-macbook-line"></i>
                            </p>
                        </div>
                        <div class="modal-separator"></div>
                        <div class="modal-options">
                            <div class="upload option">
                                <div class="drop-area">
                                    <i class="ri-upload-cloud-2-fill" id="trigger-file-upload"></i>
                                    <h3>Drag and drop or click here to select image</h3>
                                    <p>Image size must be less than <span>2MB</span></p>
                                    <input type="file" name="profile_picture" class="profile-input"
                                        id="profile-pic-upload" accept="image/*" hidden>
                                </div>
                            </div>
                            <div class="default option">
                                <div class="default-grid">
                                    <?php
                                    $imageCount = 32;
                                    $genders = [];

                                    if ($userDetails['gender'] === 'male') {
                                        $genders = ['m'];
                                    } elseif ($userDetails['gender'] === 'female') {
                                        $genders = ['f'];
                                    } else {
                                        $genders = ['m', 'f'];
                                    }

                                    foreach ($genders as $gender) {
                                        for ($i = 1; $i <= $imageCount; $i++) {
                                            $imgSrc = "/assets/res/avatars/default/{$gender}{$i}.webp";
                                            $imgName = "{$gender}{$i}.webp";
                                            echo "<img src='{$imgSrc}' alt='Default {$gender} avatar {$i}' class='def-avatars' loading='lazy' data-value='{$imgName}'>";
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="modal-overlay" class="modal-overlay"></div>

                <div class="input-row">
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input type="text" name="fst-name" id="fstname" autocomplete="nope"
                            value="<?= htmlspecialchars($userDetails['firstname']); ?>"
                            placeholder="Enter your firstname" class="disabled-input" readonly>
                        <label for="fstname">First Name</label>
                        <i class="fa-solid fa-pen edit-icon" data-target="fstname"></i>
                    </div>
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input type="text" name="lst-name" id="lstname" autocomplete="nope"
                            value="<?= htmlspecialchars($userDetails['lastname']); ?>" placeholder="Enter your lastname"
                            class="disabled-input" readonly>
                        <label for="lstname">Last Name</label>
                        <i class="fa-solid fa-pen edit-icon" data-target="lstname"></i>
                    </div>
                </div>

                <div class="input-row">
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input type="text" name="usr-name" id="usrname" autocomplete="nope"
                            value="<?= htmlspecialchars($userDetails['username']); ?>" placeholder="Enter your username"
                            class="disabled-input" readonly>
                        <label for="usrname">Username</label>
                        <i class="fa-solid fa-pen edit-icon" data-target="usrname"></i>
                    </div>
                    <div class="input-field">
                        <i class="fas fa-envelope"></i>
                        <input type="email" class="disabled-input" name="e-mail" id="e-mail" autocomplete="nope"
                            readonly value="<?= htmlspecialchars($userDetails['email']); ?>">
                        <label for="e-mail">Email</label>
                    </div>
                </div>

                <?php if (!empty($userDetails['password']) && $userDetails['password'] !== null): ?>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="crntPassword" name="crnt-password" autocomplete="nope"
                            placeholder="Enter your current password">
                        <label for="crntPassword">Current Password</label>
                        <i class="fa-solid fa-eye-slash toggle-password" data-target="crntPassword"></i>
                    </div>
                <?php endif; ?>

                <div class="input-row">
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="newPassword" name="new-password" autocomplete="nope"
                            placeholder="Enter your new password">
                        <label for="newPassword">New Password</label>
                        <i class="fa-solid fa-eye-slash toggle-password" data-target="newPassword"></i>
                    </div>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="cnfrmPassword" name="cnfrm-password" autocomplete="nope"
                            placeholder="Confirm your new password">
                        <label for="cnfrmPassword">Confirm Password</label>
                        <i class="fa-solid fa-eye-slash toggle-password" data-target="cnfrmPassword"></i>
                    </div>
                </div>

                <div class="link-gnrtpswrd">
                    <a id="generatePasswordprofile" class="genpswrdlink" role="button">
                        <i class="fa-solid fa-gear"></i>
                        Generate a Secure Password
                    </a>
                </div>

                <button type="submit" class="save-btn">Save Changes</button>
            </form>
        </div>
    </main>
    <script src="assets/js/navSidebar.js"></script>
    <script src="assets/js/logout.js"></script>
    <script src="assets/js/notifier.js"></script>
    <script src="assets/js/showHidePswrd.js"></script>
    <script src="assets/js/genRandPswrd.js"></script>
    <script src="assets/js/editFields.js"></script>
    <script src="assets/js/profilePicModal.js"></script>
</body>

</html>
<?php
// Flush the output buffer.
ob_end_flush();
?>

