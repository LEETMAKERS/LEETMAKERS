document.addEventListener("DOMContentLoaded", function () {
    const notifications = document.querySelector(".notifications");
    const sessionMessages = document.getElementById('session-messages');

    // Helper function to remove toast with fade-out effect
    const removeToast = (toast) => {
        toast.classList.add("hide");

        if (toast.timeoutId) clearTimeout(toast.timeoutId);

        setTimeout(() => toast.remove(), 500);
    };

    // Helper function to create and display a toast notification
    const createToast = (type, iconClass, message) => {
        const decodedMessage = decodeURIComponent(message);
        
        // Split the message into type and description
        const [msgType, ...descriptionParts] = decodedMessage.split(':');
        const description = descriptionParts.join(':').trim(); // Join back the rest of the message

        const toast = document.createElement("li");
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="column">
                <i class="fa-solid ${iconClass}"></i>
                <span class="type">${msgType.trim()}</span><span class="colon">:</span> <span class="description">${description}</span>
            </div>
            <i class="fa-solid fa-xmark close-btn"></i>
            <div class="progress-bar"></div>
        `;

        notifications.appendChild(toast);

        // Automatically remove the toast after 5 seconds
        toast.timeoutId = setTimeout(() => removeToast(toast), 5000);

        // Allow manual removal when clicking the close icon
        toast.querySelector('.close-btn').addEventListener('click', () => removeToast(toast));
    };

    // Object mapping notification types to corresponding icons
    const messageTypes = {
        error: "fa-circle-xmark",
        success: "fa-circle-check",
        warning: "fa-triangle-exclamation",
        info: "fa-circle-info"
    };

    // Loop through each message type and create toasts if messages exist
    Object.keys(messageTypes).forEach((type) => {
        const message = sessionMessages.dataset[type];
        if (message) {
            createToast(type, messageTypes[type], message);
        }
    });
});
