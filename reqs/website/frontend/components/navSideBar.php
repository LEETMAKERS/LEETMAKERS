<?php

// Start the session if not already started.
if (session_status() == PHP_SESSION_NONE) {
   session_start();
}

// This file assumes that the login check has already been done in the parent page.
require_once __DIR__ . "/../../backend/includes/dbConn.php";

// Check in case this file is used elsewhere.
if (!isset($_SESSION['id'])) {
   header("Location: /auth/authenticate?action=login");
   exit();
}

// Retrieve the user details.
$userDetails = getUserDetails($_SESSION['id'], $conn);

// Handle any errors returned from getUserDetails.
if (isset($userDetails['error'])) {
   $_SESSION['error'] = $userDetails['error'];
   session_unset();
   session_destroy();
   header("Location: /auth/authenticate?action=register");
   exit();
}

// Determine the current page.
$currentPage = basename($_SERVER['REQUEST_URI'], ".php");

?>
<!--=============== HEADER ===============-->
<header class="header left-pd" id="header">
   <div class="header__container">
      <button class="header__toggle" id="header-toggle">
         <i class="ri-menu-line"></i>
      </button>

      <div class="search-container">
         <input type="text" placeholder="Search" name="search" id="global-search" class="input" autocomplete="off">
         <button type="button" class="search-button" id="search-btn">
            <i class="ri-search-line"></i>
         </button>
      </div>

      <a href="/notifications" class="header__link">
         <i class="fa-regular fa-bell"></i>
      </a>

      <div class="header__user">
         <img src="<?= htmlspecialchars($userDetails['avatar']); ?>" alt="User Profile Picture">
      </div>
   </div>
</header>

<!--=============== SIDEBAR ===============-->
<nav class="sidebar show-sidebar" id="sidebar">
   <div class="sidebar__container">
      <div class="sidebar__logo">
         <div class="sidebar__img">
            <img src="/assets/res/logo/leetmakers.jpg" alt="">
         </div>
         <div class="sidebar__info">
            <h2>LEET MAKERS</h2>
            <span>
               Your Gateway to Technology
            </span>
         </div>
      </div>

      <div class="sidebar__content">
         <div>
            <h3 class="sidebar__title">MANAGE</h3>
            <div class="sidebar__list">
               <a href="/dashboard" class="sidebar__link <?= isActive('dashboard', $currentPage) ?>">
                  <i class="ri-dashboard-line"></i>
                  <span>Dashboard</span>
               </a>
               <a href="/resources" class="sidebar__link <?= isActive('resources', $currentPage) ?>">
                  <i class="ri-git-repository-line"></i>
                  <span>Resources</span>
               </a>
               <a href="/community" class="sidebar__link <?= isActive('community', $currentPage) ?>">
                  <i class="ri-team-line"></i>
                  <span>Community</span>
               </a>
               <a href="/events" class="sidebar__link <?= isActive('events', $currentPage) ?>">
                  <i class="ri-calendar-event-line"></i>
                  <span>Events</span>
               </a>
               <?php if (isset($userDetails['role']) && ($userDetails['role'] === 'admin') || ($userDetails['role'] === 'member')): ?>
                  <a href="/inventory" class="sidebar__link <?= isActive('inventory', $currentPage) ?>">
                     <i class="ri-archive-line"></i>
                     <span>Inventory</span>
                  </a>
               <?php endif; ?>
            </div>
         </div>

         <?php if (isset($userDetails['role']) && $userDetails['role'] === 'admin'): ?>
            <div>
               <h3 class="sidebar__title">ADMIN</h3>
               <div class="sidebar__list">
                  <a href="/activity" class="sidebar__link <?= isActive('activity', $currentPage) ?>">
                     <i class="ri-history-line"></i>
                     <span>Activity</span>
                  </a>
                  <a href="/memberops" class="sidebar__link <?= isActive('memberops', $currentPage) ?>">
                     <i class="ri-shield-user-line"></i>
                     <span>Members Ops</span>
                  </a>
               </div>
            </div>
         <?php endif; ?>

         <div>
            <h3 class="sidebar__title">SETTINGS</h3>
            <div class="sidebar__list">
               <a href="/settings" class="sidebar__link <?= isActive('settings', $currentPage) ?>">
                  <i class="ri-settings-3-line"></i>
                  <span>Settings</span>
               </a>
               <a href="/profile" class="sidebar__link <?= isActive('profile', $currentPage) ?>">
                  <i class="ri-account-circle-line"></i>
                  <span>Profile</span>
               </a>
               <a href="/notifications" class="sidebar__link notif <?= isActive('notifications', $currentPage) ?>">
                  <i class="fa-regular fa-bell"></i>
                  <span>Notifications</span>
               </a>
            </div>
         </div>
      </div>

      <div class="sidebar__actions">
         <button>
            <i class="ri-moon-clear-line sidebar__link sidebar__theme" id="theme-button">
               <span>Theme</span>
            </i>
         </button>
         <div class="sidebar__user">
            <div class="sidebar__user__img">
               <img src="<?= htmlspecialchars($userDetails['avatar']); ?>" alt="User Profile Picture">
            </div>
         </div>
         <a href="/auth/logout" class="sidebar__link">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Log Out</span>
         </a>
      </div>
   </div>
</nav>
