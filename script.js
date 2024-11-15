// Registration Validation
function validateRegisterEmail() {
    const emailInput = document.getElementById("registerEmail");
    const emailError = document.getElementById("registerEmailError");
    if (emailInput.value.trim() === "") {
        emailError.textContent = "Email cannot be empty.";
        return false;
    } else {
        emailError.textContent = "";
        return true;
    }
}

function validateRegisterUsername() {
    const usernameInput = document.getElementById("registerUsername");
    const usernameError = document.getElementById("registerUsernameError");
    if (usernameInput.value.trim() === "") {
        usernameError.textContent = "Username cannot be empty.";
        return false;
    } else {
        usernameError.textContent = "";
        return true;
    }
}

function validateRegisterPassword() {
    const passwordInput = document.getElementById("registerPassword");
    const passwordError = document.getElementById("registerPasswordError");
    if (passwordInput.value.trim() === "") {
        passwordError.textContent = "Password cannot be empty.";
        return false;
    } else {
        passwordError.textContent = "";
        return true;
    }
}

function validateRegisterRePassword() {
    const passwordInput = document.getElementById("registerPassword").value;
    const rePasswordInput = document.getElementById("registerRePassword");
    const rePasswordError = document.getElementById("registerRePasswordError");
    if (rePasswordInput.value.trim() === "") {
        rePasswordError.textContent = "Please re-enter the password.";
        return false;
    } else if (rePasswordInput.value !== passwordInput) {
        rePasswordError.textContent = "Passwords do not match.";
        return false;
    } else {
        rePasswordError.textContent = "";
        return true;
    }
}

// Prevent form submission if there are errors
document.querySelector("form").addEventListener("submit", function (event) {
    const isEmailValid = validateRegisterEmail();
    const isUsernameValid = validateRegisterUsername();
    const isPasswordValid = validateRegisterPassword();
    const isRePasswordValid = validateRegisterRePassword();

    if (!isEmailValid || !isUsernameValid || !isPasswordValid || !isRePasswordValid) {
        event.preventDefault(); // Stop form submission
        alert("Please fix the errors before submitting.");
    }
});
