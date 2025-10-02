const form = document.getElementById("signupForm");

// Input elements
const firstNameInput = form.firstName;
const lastNameInput = form.lastName;
const contactInput = form.contact;
const emailInput = form.email;
const passwordInput = form.password;

// Create error spans dynamically
const createErrorSpan = (input) => {
  let span = document.getElementById(input.name + "Error");
  if (!span) {
    span = document.createElement("span");
    span.id = input.name + "Error";
    span.style.color = "red";
    span.style.fontSize = "0.9rem";
    input.parentNode.appendChild(span);
  }
  return span;
};

const firstNameError = createErrorSpan(firstNameInput);
const lastNameError = createErrorSpan(lastNameInput);
const contactError = createErrorSpan(contactInput);
const emailError = createErrorSpan(emailInput);
const passwordError = createErrorSpan(passwordInput);

// Regex patterns
const nameRegex = /^[a-zA-Z]+$/;

// === Live validation ===
firstNameInput.addEventListener("input", () => {
  firstNameError.textContent = nameRegex.test(firstNameInput.value) ? "" : "First Name should contain only letters.";
});
lastNameInput.addEventListener("input", () => {
  lastNameError.textContent = nameRegex.test(lastNameInput.value) ? "" : "Last Name should contain only letters.";
});
contactInput.addEventListener("input", () => {
  let value = contactInput.value;
  if (value.startsWith("09")) contactInput.maxLength = 11;
  else if (value.startsWith("+63")) contactInput.maxLength = 12;
  else contactInput.maxLength = 12;

  if (value.length > contactInput.maxLength) contactInput.value = value.slice(0, contactInput.maxLength);

  if ((value.startsWith("09") && value.length === 11) || (value.startsWith("+63") && value.length === 12)) {
    contactError.textContent = "";
  } else {
    contactError.textContent = "Contact must start with 09 or +63 and have correct length (11 for 09, 12 for +63).";
  }
});
emailInput.addEventListener("input", () => {
  emailError.textContent = emailInput.value.includes("@") ? "" : "Email must contain '@'.";
});
passwordInput.addEventListener("input", () => {
  passwordError.textContent = passwordInput.value.length >= 6 ? "" : "Password should be at least 6 characters.";
});

// --- Terms & Conditions Modal ---
function showTermsAndConditions() {
  return new Promise((resolve) => {
    const overlay = document.createElement("div");
    overlay.className = "terms-overlay";
    overlay.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.6);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    `;

    const box = document.createElement("div");
    box.className = "terms-box";
    box.style.cssText = `
      background-color: #fff;
      padding: 30px;
      border-radius: 12px;
      width: 90%;
      max-width: 600px;
      max-height: 80vh;
      display: flex;
      flex-direction: column;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    `;

    const title = document.createElement("h2");
    title.textContent = "Terms and Conditions";
    title.style.cssText = `
      margin: 0 0 15px 0;
      color: #333;
      font-size: 1.5rem;
    `;

    const content = document.createElement("div");
    content.className = "terms-content";
    content.style.cssText = `
      flex: 1;
      overflow-y: auto;
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
      background-color: #f9f9f9;
      margin-bottom: 20px;
      line-height: 1.6;
      color: #555;
    `;

    content.innerHTML = `
      <h3>1. Acceptance of Terms</h3>
      <p>By creating an account, you agree to be bound by these Terms and Conditions.</p>
      
      <h3>2. User Account</h3>
      <p>You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.</p>
      
      <h3>3. Privacy Policy</h3>
      <p>We collect and process your personal information in accordance with our Privacy Policy. By signing up, you consent to such processing.</p>
      
      <h3>4. User Conduct</h3>
      <p>You agree not to use the service for any unlawful purpose or in any way that could damage, disable, or impair the service.</p>
      
      <h3>5. Termination</h3>
      <p>We reserve the right to terminate or suspend your account at any time for violation of these terms.</p>
      
      <h3>6. Changes to Terms</h3>
      <p>We may modify these terms at any time. Continued use of the service constitutes acceptance of modified terms.</p>
      
      <h3>7. Contact</h3>
      <p>If you have any questions about these Terms, please contact our support team.</p>
    `;

    const buttonContainer = document.createElement("div");
    buttonContainer.style.cssText = `
      display: flex;
      gap: 15px;
      justify-content: center;
    `;

    const declineBtn = document.createElement("button");
    declineBtn.textContent = "Decline";
    declineBtn.style.cssText = `
      padding: 12px 30px;
      font-size: 1rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      background-color: #dc3545;
      color: white;
      transition: background-color 0.3s;
    `;
    declineBtn.onmouseover = () => declineBtn.style.backgroundColor = "#c82333";
    declineBtn.onmouseout = () => declineBtn.style.backgroundColor = "#dc3545";

    const acceptBtn = document.createElement("button");
    acceptBtn.textContent = "Accept";
    acceptBtn.style.cssText = `
      padding: 12px 30px;
      font-size: 1rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      background-color: #28a745;
      color: white;
      transition: background-color 0.3s;
    `;
    acceptBtn.onmouseover = () => acceptBtn.style.backgroundColor = "#218838";
    acceptBtn.onmouseout = () => acceptBtn.style.backgroundColor = "#28a745";

    declineBtn.addEventListener("click", () => {
      document.body.removeChild(overlay);
      resolve(false);
    });

    acceptBtn.addEventListener("click", () => {
      document.body.removeChild(overlay);
      resolve(true);
    });

    buttonContainer.appendChild(declineBtn);
    buttonContainer.appendChild(acceptBtn);

    box.appendChild(title);
    box.appendChild(content);
    box.appendChild(buttonContainer);
    overlay.appendChild(box);
    document.body.appendChild(overlay);
  });
}

// --- Loading Overlay ---
function showLoadingOverlay(message = "Loading...") {
  const overlay = document.createElement("div");
  overlay.className = "loading-overlay";
  overlay.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    flex-direction: column;
  `;

  const spinner = document.createElement("div");
  spinner.style.cssText = `
    border: 8px solid #f3f3f3;
    border-top: 8px solid #007bff;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    animation: spin 1s linear infinite;
  `;

  const style = document.createElement("style");
  style.textContent = `
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  `;
  document.head.appendChild(style);

  const text = document.createElement("p");
  text.textContent = message;
  text.style.cssText = `
    color: white;
    font-size: 1.2rem;
    margin-top: 20px;
    font-weight: 500;
  `;

  overlay.appendChild(spinner);
  overlay.appendChild(text);
  document.body.appendChild(overlay);

  return overlay;
}

function hideLoadingOverlay(overlay) {
  if (overlay && overlay.parentNode) {
    document.body.removeChild(overlay);
  }
}

// --- OTP Overlay ---
function promptOtpOverlay(email) {
  return new Promise((resolve) => {
    const overlay = document.createElement("div");
    overlay.className = "otp-overlay";
    overlay.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.6);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    `;

    const box = document.createElement("div");
    box.className = "otp-box";
    box.style.cssText = `
      background-color: #fff;
      padding: 30px;
      border-radius: 12px;
      width: 90%;
      max-width: 400px;
      text-align: center;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    `;

    box.innerHTML = `
      <h3 style="margin-top: 0; color: #333;">Enter OTP</h3>
      <p style="color: #666; margin: 15px 0;">We sent a 6-digit OTP to <strong>${email}</strong></p>
      <input type="text" id="otpInput" maxlength="6" placeholder="Enter OTP" 
        style="padding: 12px; width: 80%; font-size: 1.1rem; margin: 15px 0; border: 2px solid #ddd; border-radius: 6px; text-align: center;" />
      <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: center;">
        <button id="otpCancelBtn" style="padding: 12px 24px; font-size: 1rem; border: none; border-radius: 6px; cursor: pointer; background-color: #6c757d; color: white;">Cancel</button>
        <button id="otpSubmitBtn" style="padding: 12px 24px; font-size: 1rem; border: none; border-radius: 6px; cursor: pointer; background-color: #007bff; color: white;">Verify</button>
      </div>
    `;

    overlay.appendChild(box);
    document.body.appendChild(overlay);

    document.getElementById("otpSubmitBtn").addEventListener("click", () => {
      const val = document.getElementById("otpInput").value.trim();
      if (val.length === 6 && /^\d+$/.test(val)) {
        document.body.removeChild(overlay);
        resolve(val);
      } else {
        alert("Please enter a valid 6-digit OTP.");
      }
    });

    document.getElementById("otpCancelBtn").addEventListener("click", () => {
      document.body.removeChild(overlay);
      resolve(null);
    });
  });
}

// === Submit handler ===
let isSubmitting = false;

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  // Prevent multiple submissions
  if (isSubmitting) {
    return;
  }

  const firstName = firstNameInput.value.trim();
  const lastName = lastNameInput.value.trim();
  const contact = contactInput.value.trim();
  const email = emailInput.value.trim();
  const password = passwordInput.value;

  // Final validation
  if (!nameRegex.test(firstName) || !nameRegex.test(lastName) ||
      !((contact.startsWith("09") && contact.length === 11) || 
        (contact.startsWith("+63") && contact.length === 12)) ||
      !email.includes("@") || password.length < 6) {
    alert("Please fix the errors before submitting.");
    return;
  }

  isSubmitting = true;
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalBtnText = submitBtn ? submitBtn.textContent : "";
  if (submitBtn) submitBtn.textContent = "Processing...";

  try {
    // --- Show Terms & Conditions ---
    const termsAccepted = await showTermsAndConditions();
    
    if (!termsAccepted) {
      alert("You must accept the Terms and Conditions to create an account. Account not created.");
      return;
    }

    // --- Show loading overlay ---
    const loadingOverlay = showLoadingOverlay("Sending OTP...");

    // --- Request OTP ---
    const otpRes = await fetch(apiUrl("otp"), {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email })
    });
    const otpData = await otpRes.json();

    // --- Hide loading overlay ---
    hideLoadingOverlay(loadingOverlay);

    if (!otpData.success) {
      alert("Failed to send OTP: " + otpData.message);
      return;
    }

    // --- Show OTP modal overlay ---
    const userOtp = await promptOtpOverlay(email);
    if (!userOtp) {
      alert("You must enter the OTP. Account not created.");
      return;
    }

    // --- Create account ---
    const res = await fetch(apiUrl("signup"), {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ firstName, lastName, contact, email, password, otp: userOtp })
    });

    const data = await res.json();
    if (data.success) {
      alert("Account created successfully!");
      window.location.href = "login.php";
    } else {
      alert("Error: " + data.message);
    }

  } catch (err) {
    console.error(err);
    alert("Error: " + err.message);
  } finally {
    isSubmitting = false;
    if (submitBtn) submitBtn.textContent = originalBtnText;
  }
});