const form = document.getElementById("signupForm");

// Input elements
const firstNameInput = form.firstName;
const lastNameInput = form.lastName;
const contactInput = form.contact;
const emailInput = form.email;
const passwordInput = form.password;

// Create error spans dynamically if not present
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

  // Dynamic max length based on prefix
  if (value.startsWith("09")) contactInput.maxLength = 11;
  else if (value.startsWith("+63")) contactInput.maxLength = 12;
  else contactInput.maxLength = 12; // default

  // Truncate extra characters
  if (value.length > contactInput.maxLength) contactInput.value = value.slice(0, contactInput.maxLength);

  // Live error message
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

// === Submit handler ===
form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const firstName = firstNameInput.value.trim();
  const lastName = lastNameInput.value.trim();
  const contact = contactInput.value.trim();
  const email = emailInput.value.trim();
  const password = passwordInput.value;

  // Final validation before sending
  if (!nameRegex.test(firstName) || !nameRegex.test(lastName) ||
      !((contact.startsWith("09") && contact.length === 11) || 
        (contact.startsWith("+63") && contact.length === 12)) ||
      !email.includes("@") || password.length < 6) {
    alert("Please fix the errors before submitting.");
    return;
  }

  try {
    const res = await fetch(apiUrl("signup"), {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ firstName, lastName, contact, email, password })
    });

    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

    const data = await res.json();

    if (data.success) {
      alert("Account created successfully!");
      window.location.href = "login.php";
    } else {
      alert("Error: " + data.message);
    }
  } catch (err) {
    console.error("Connection error:", err);
    alert(`Error connecting to server: ${err.message}`);
  }
});
