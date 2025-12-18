// Set the inactivity logout time in milliseconds (42 minutes)
const inactivityLogoutTime = 42 * 60 * 1000; // 2,520,000 ms
let lastActivityTime = Date.now(); // Store the last activity timestamp

// Function to start the inactivity timer
function startInactivityTimer() {
    let timeout;

    // Function to reset the timer on user activity
    function resetTimer() {
        lastActivityTime = Date.now(); // Update last activity time
        clearTimeout(timeout);
        timeout = setTimeout(logout, inactivityLogoutTime);
    }

    // Function to log the user out
    function logout() {
        console.log("Logging out due to inactivity...");
        window.location.href = "/auth/logout";
    }

    // Function to log the remaining time before logout
    function logTimeLeft() {
        const timeElapsed = Date.now() - lastActivityTime;
        const timeLeft = Math.max(inactivityLogoutTime - timeElapsed, 0);
        console.log(`Time left before logout: ${Math.ceil(timeLeft / 1000)} seconds`);
    }

    // Event listeners to detect user activity
    // document.addEventListener("mousemove", resetTimer);
    document.addEventListener("keypress", resetTimer);
    // document.addEventListener("scroll", resetTimer);
    // document.addEventListener("touchstart", resetTimer);
    document.addEventListener("click", resetTimer);

    // Start the timer initially
    resetTimer();

    // Log time left every second
    setInterval(logTimeLeft, 1000);
}

// Start the inactivity timer when the page loads
window.addEventListener("load", startInactivityTimer);
