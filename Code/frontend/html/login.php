<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Log in – Agri Fresh</title>

  <!-- Font Awesome for eye icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/login.css">
  <link rel="stylesheet" href="../css/sidebar.css">

  <style>
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
      z-index: 1000;
    }

    .back-btn:hover {
      background: #45a049;
    }

    /* Password field wrapper */
    .password-wrapper {
      position: relative;
      width: 100%;
    }

    .password-wrapper input {
      width: 100%;
      padding-right: 2.5rem; /* space for eye icon inside */
      box-sizing: border-box;
    }

    .toggle-password {
      position: absolute;
      top: 40%;
      right: 0.75rem;
      transform: translateY(-50%);
      cursor: pointer;
      color: #888;
      font-size: 1rem;
    }

    .toggle-password:hover {
      color: #787878ff;
    }
  </style>
</head>

<body>

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
        <div class="password-wrapper">
          <input type="password" name="password" id="password" required />
          <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
        </div>
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

  <script>
    // Show/hide password
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    togglePassword.addEventListener('click', () => {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);

      // Switch icons
      if (type === 'password') {
        togglePassword.classList.replace('fa-eye-slash', 'fa-eye');
      } else {
        togglePassword.classList.replace('fa-eye', 'fa-eye-slash');
      }
    });
  </script>

  <script src="../js/login.js"></script>
  <?php include(__DIR__ . '/../components/sidebar.php'); ?>
</body>
</html>
