"use strict";
document.addEventListener("DOMContentLoaded", function () {
    const errorDetails = {
        400: {
            title: "LEETMAKERS - Bad Request",
            description: "The server cannot process the request due to a client error."
        },
        401: {
            title: "LEETMAKERS - Unauthorized",
            description: "Authentication is required to access this resource."
        },
        403: {
            title: "LEETMAKERS - Forbidden",
            description: "You don't have permission to access this resource."
        },
        404: {
            title: "LEETMAKERS - Not Found",
            description: "The requested resource could not be found."
        },
        408: {
            title: "LEETMAKERS - Request Timeout",
            description: "The server timed out waiting for the request."
        },
        500: {
            title: "LEETMAKERS - Internal Server Error",
            description: "The server encountered an unexpected condition."
        },
        503: {
            title: "LEETMAKERS - Service Unavailable",
            description: "The service is temporarily unavailable, please try again later."
        }
    };
    const params = new URLSearchParams(window.location.search);
    const errorCode = params.get("code");
    const errorInfo = errorDetails[errorCode];
    if (errorInfo) {
        document.title = errorInfo.title;
        document.querySelector(".code-error").textContent = errorCode;
        document.querySelector(".error-title").textContent = errorInfo.title.replace("LEETMAKERS ", "");
        document.querySelector("#source").textContent = errorInfo.description;
    } else {
        document.title = "LEETMAKERS - Unknown Error";
        document.querySelector(".error-title").textContent = "Unknown Error";
        document.querySelector("#source").textContent = "An unknown error has occurred.";
    }
}

);
