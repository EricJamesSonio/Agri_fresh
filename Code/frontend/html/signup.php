<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up – Agri Fresh</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/signup.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <style>
    .error {
      color: red;
      font-size: 0.85rem;
      margin-top: 3px;
      display: block;
    }

    /* Back button fixed at top-right */
    .back-btn {
      position: fixed;
      top: 1rem;
      right: 1rem;
      background: #4CAF50;
      color: white;
      border: none;
      padding: 0.6rem 1.2rem;
      border-radius: 4px;
      cursor: pointer;
      font-size: 1rem;
      z-index: 1000; /* keeps it above sidebar/content */
    }

    .back-btn:hover {
      background: #45a049;
    }

    .signup-wrapper {
      max-width: 600px;
      margin: 4rem auto 2rem auto; /* add margin-top so form doesn't overlap button */
      background: #fff;
      padding: 2rem;
      border-radius: 8px;
      border: 1px solid #e0e0e0;
    }

    .signup-wrapper h1 {
      margin-bottom: 1rem;
    }
  </style>
</head>

<body>
<?php include(__DIR__ . '/../components/sidebar.php'); ?>

  <!-- Back button fixed at very top-right -->
  <button onclick="window.location.href='index.php'" class="back-btn">← Back</button>

  <main class="signup-wrapper">
    <h1>Sign Up</h1>
    <form id="signupForm">
      <label>
        First Name
        <input type="text" name="firstName" required />
        <span class="error" id="firstNameError"></span>
      </label>

      <label>
        Last Name
        <input type="text" name="lastName" required />
        <span class="error" id="lastNameError"></span>
      </label>

      <label>
        Contact No.
        <input type="tel" name="contact" required />
        <span class="error" id="contactError"></span>
      </label>

      <label>
        Email
        <input type="email" name="email" required />
        <span class="error" id="emailError"></span>
      </label>

      <label>
        Password
        <input type="password" name="password" required />
        <span class="error" id="passwordError"></span>
      </label>

      <button type="submit" class="btn">Create Account</button>

      <p class="switch">
        Already have an account? <a href="login.php">Log in</a>
      </p>
    </form>
  </main>

  <footer>
    <p style="text-align:center;padding:1.5rem 0;color:#666;">
      &copy; 2025 AgriFresh Market – Freshness Delivered.
    </p>
  </footer>

  <script src="../js/config.js"></script>
  <script src="../js/signup.js"></script>
</body>
</html>
