<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Log in – Agri Fresh</title>

  <!-- same stylesheet used everywhere -->
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/login.css">
  <link rel="stylesheet" href="../css/sidebar.css">

  <style>
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
      z-index: 1000; /* keeps it above other content */
    }

    .back-btn:hover {
      background: #45a049;
    }
  </style>
</head>

<body>

  <!-- Back button fixed at very top-right -->
  <button onclick="window.location.href='index.php'" class="back-btn">← Back</button>


  <main class="login-wrapper">
    <h1>Log in</h1>

    <form id="loginForm">
      <label>
        Email
        <input type="email" name="email" required />
      </label>

      <label>
        Password
        <input type="password" name="password" required />
      </label>

      <button type="submit">Sign in</button>

      <p class="switch">
        No account? <a href="signup.php">Sign up</a>
      </p>
    </form>
  </main>

  <footer>
    <p style="text-align:center;padding:1.5rem 0;color:#666;">
      &copy; 2025 AgriFresh Market – Freshness Delivered.
    </p>
  </footer>

  <!-- Load config FIRST, then login logic -->
  <script src="../js/config.js"></script>
  <script src="../js/login.js"></script>
  
  <?php include(__DIR__ . '/../components/sidebar.php'); ?>
</body>
</html>
