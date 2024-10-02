//signin
function validateLoginUsername() {
    const usernameInput = document.getElementById("loginUsername");
    const usernameError = document.getElementById("loginUsernameError");
    const usernameSuccess = document.getElementById("loginUsernameSuccess");

    console.log("Validating login username...");
    // Simulated existing accounts for validation
    const existingAccounts = ["user1", "user2", "admin"]; // Replace with your actual accounts

    if (usernameInput.value.trim() === "") {
        usernameError.textContent = "Username cannot be empty.";
        usernameSuccess.textContent = "";
    } else if (!existingAccounts.includes(usernameInput.value)) {
        usernameError.textContent = "Username does not exist.";
        usernameSuccess.textContent = "";
    } else {
        usernameError.textContent = "";
        usernameSuccess.textContent = "Username is valid!";
    }
}

function validateLoginPassword() {
    const passwordInput = document.getElementById("loginPassword");
    const passwordError = document.getElementById("loginPasswordError");
    const passwordSuccess = document.getElementById("loginPasswordSuccess");

    console.log("Validating login password...");
    // You can add a password check if necessary or leave it as is
    if (passwordInput.value.trim() === "") {
        passwordError.textContent = "Password cannot be empty.";
        passwordSuccess.textContent = "";
    } else {
        passwordError.textContent = "";
        passwordSuccess.textContent = "Password is valid!";
    }
}

// Prevent copying password
document.getElementById("loginPassword").addEventListener("copy", function (event) {
    event.preventDefault(); // Prevent the default copy action
    alert("Copying the password is not allowed."); // Optional: Provide user feedback
});


// Add event listeners for validation
document.getElementById("loginUsername").addEventListener("blur", validateLoginUsername);
document.getElementById("loginPassword").addEventListener("blur", validateLoginPassword);

// Remember Me functionality
document.getElementById("login").addEventListener("click", function () {
    const usernameInput = document.getElementById("loginUsername").value;
    const rememberMe = document.getElementById("rememberme")?.checked;

    if (rememberMe) {
        document.cookie = `username=${usernameInput}; max-age=${7 * 24 * 60 * 60}; path=/`; // 7 days
    } else {
        document.cookie = "username=; max-age=0; path=/";
    }

    alert("Logged in!"); // Replace with actual login logic
});

// Auto-fill the username if it exists in the cookie
function checkCookie() {
    const cookies = document.cookie.split("; ");
    const usernameCookie = cookies.find(cookie => cookie.startsWith("username="));
    if (usernameCookie) {
        const username = usernameCookie.split("=")[1];
        document.getElementById("loginUsername").value = username;
    }
}

// Call checkCookie on page load
window.onload = checkCookie;










// signin
function validateRegisterUsername() {
    const usernameInput = document.getElementById("registerUsername");
    const usernameError = document.getElementById("registerUsernameError");
    const usernameSuccess = document.getElementById("registerUsernameSuccess");

    console.log("Validating username...");
    const usernameRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).+$/;

    if (!usernameRegex.test(usernameInput.value)) {
        usernameError.textContent = "Username must contain at least 1 uppercase letter, 1 lowercase letter, and 1 special character.";
        usernameSuccess.textContent = "";
    } else {
        usernameError.textContent = "";
        usernameSuccess.textContent = "Username is valid!";
    }
}

function validateRegisterEmail() {
    const emailInput = document.getElementById("registerEmail");
    const emailError = document.getElementById("registerEmailError");
    const emailSuccess = document.getElementById("registerEmailSuccess");

    console.log("Validating email...");
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailRegex.test(emailInput.value)) {
        emailError.textContent = "Invalid email format.";
        emailSuccess.textContent = "";
    } else {
        emailError.textContent = "";
        emailSuccess.textContent = "Email is valid!";
    }
}

function validateRegisterPassword() {
    const passwordInput = document.getElementById("registerPassword");
    const passwordError = document.getElementById("registerPasswordError");
    const passwordSuccess = document.getElementById("registerPasswordSuccess");

    console.log("Validating password...");
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[@$!%*?&._]).{8,}$/;

    if (!passwordRegex.test(passwordInput.value)) {
        passwordError.textContent = "Password must contain at least 8 characters, including 1 uppercase letter, 1 lowercase letter, and 1 special character.";
        passwordSuccess.textContent = "";
    } else {
        passwordError.textContent = "";
        passwordSuccess.textContent = "Password is valid!";
    }
}

// Prevent copying password
document.getElementById("loginPassword").addEventListener("copy", function (event) {
    event.preventDefault(); // Prevent the default copy action
    alert("Copying the password is not allowed."); // Optional: Provide user feedback
});


function validateRegisterRePassword() {
    const passwordInput = document.getElementById("registerPassword");
    const rePasswordInput = document.getElementById("registerRePassword");
    const repasswordError = document.getElementById("registerRePasswordError");
    const repasswordSuccess = document.getElementById("registerRePasswordSuccess");

    console.log("Validating re-password...");
    if (rePasswordInput.value !== passwordInput.value) {
        repasswordError.textContent = "Passwords do not match.";
        repasswordSuccess.textContent = "";
    } else {
        repasswordError.textContent = "";
        repasswordSuccess.textContent = "Passwords match!";
    }
}

// Remember Me and cookie handling can remain as is, updating IDs accordingly if used.

document.getElementById("register").addEventListener("click", function () {
    const usernameInput = document.getElementById("registerUsername").value;
    const rememberMe = document.getElementById("rememberme")?.checked;

    if (rememberMe) {
        document.cookie = `username=${usernameInput}; max-age=${7 * 24 * 60 * 60}; path=/`; // 7 days
    } else {
        document.cookie = "username=; max-age=0; path=/";
    }

    alert("Logged in!"); // Replace with actual login logic
});

function checkCookie() {
    const cookies = document.cookie.split("; ");
    const usernameCookie = cookies.find(cookie => cookie.startsWith("username="));
    if (usernameCookie) {
        const username = usernameCookie.split("=")[1];
        document.getElementById("registerUsername").value = username;
    }
}

window.onload = checkCookie;
