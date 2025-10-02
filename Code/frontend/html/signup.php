<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up – Agri Fresh</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
      margin: 0;
      padding: 0;
    }

    .signup-wrapper {
      max-width: 500px;
      margin: 4rem auto;
      background: #fff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      border: 1px solid #ddd;
    }

    .signup-wrapper h1 {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #333;
    }

    form label {
      display: block;
      margin-bottom: 0.8rem;
      color: #555;
      font-weight: 500;
    }

    form input {
      width: 100%;
      padding: 0.6rem 0.8rem;
      margin-top: 0.2rem;
      margin-bottom: 0.5rem;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 1rem;
    }

    form input:focus {
      outline: none;
      border-color: #4CAF50;
      box-shadow: 0 0 3px rgba(76, 175, 80, 0.4);
    }

    .error {
      color: red;
      font-size: 0.85rem;
      margin-top: 2px;
      display: block;
    }

    button.btn {
      width: 100%;
      padding: 0.8rem;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      cursor: pointer;
      margin-top: 1rem;
      transition: 0.2s;
    }

    button.btn:hover {
      background-color: #45a049;
    }

    .switch {
      text-align: center;
      margin-top: 1rem;
      font-size: 0.9rem;
    }

    .switch a {
      color: #4CAF50;
      text-decoration: none;
      font-weight: bold;
    }

    .switch a:hover {
      text-decoration: underline;
    }

    /* OTP input overlay */
    .otp-overlay {
      position: fixed;
      top:0; left:0;
      width:100%; height:100%;
      background: rgba(0,0,0,0.6);
      display:flex;
      justify-content:center;
      align-items:center;
      z-index: 9999;
    }

    .otp-box {
      background: #fff;
      padding: 2rem;
      border-radius: 10px;
      width: 320px;
      text-align: center;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    .otp-box input {
      width: 100%;
      padding: 0.6rem;
      margin: 1rem 0;
      font-size: 1rem;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    .otp-box button {
      width: 100%;
      padding: 0.7rem;
      font-size: 1rem;
      background: #4CAF50;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: 0.2s;
    }

    .otp-box button:hover {
      background: #45a049;
    }
  </style>
</head>
<body>
<?php include(__DIR__ . '/../components/sidebar.php'); ?>

<main class="signup-wrapper">
  <h1>Sign Up</h1>
  <form id="signupForm">
    <label>First Name
      <input type="text" name="firstName" required />
      <span class="error" id="firstNameError"></span>
    </label>

    <label>Last Name
      <input type="text" name="lastName" required />
      <span class="error" id="lastNameError"></span>
    </label>

    <label>Contact No.
      <input type="tel" name="contact" required />
      <span class="error" id="contactError"></span>
    </label>

    <label>Email
      <input type="email" name="email" required />
      <span class="error" id="emailError"></span>
    </label>

    <label>Password
      <input type="password" name="password" required />
      <span class="error" id="passwordError"></span>
    </label>

    <button type="submit" class="btn">Create Account</button>
    <p class="switch">Already have an account? <a href="login.php">Log in</a></p>
  </form>
</main>

<footer>
  <p style="text-align:center;padding:1.5rem 0;color:#666;">
    &copy; 2025 AgriFresh Market – Freshness Delivered.
  </p>
</footer>

<script src="../js/signup.js"></script>
<script>
  // --- OTP overlay example (can be triggered after sending OTP) ---
  async function promptOtp(email) {
    return new Promise(resolve => {
      const overlay = document.createElement('div');
      overlay.className = 'otp-overlay';

      const box = document.createElement('div');
      box.className = 'otp-box';
      box.innerHTML = `
        <h3>Enter OTP</h3>
        <input type="text" placeholder="6-digit OTP" id="otpInput" />
        <button id="otpSubmitBtn">Submit</button>
      `;
      overlay.appendChild(box);
      document.body.appendChild(overlay);

      document.getElementById('otpSubmitBtn').addEventListener('click', () => {
        const val = document.getElementById('otpInput').value.trim();
        if (val) {
          document.body.removeChild(overlay);
          resolve(val);
        } else {
          alert('Please enter OTP.');
        }
      });
    });
  }

  // Example usage in your form JS:
  // const userOtp = await promptOtp(email);
</script>
</body>
</html>
