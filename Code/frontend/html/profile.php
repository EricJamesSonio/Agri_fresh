<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Profile - Agri Fresh</title>

  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/profile.css">
  <link rel="stylesheet" href="../css/sidebar.css">

  <style>
    body {
      font-family: Arial, sans-serif;
    }
    header h1 {
      margin-bottom: 1rem;
    }
    .profile-container {
      max-width: 600px;
      margin: 2rem auto;
      background: #fff;
      padding: 2rem;
      border-radius: 8px;
      border: 1px solid #e0e0e0;
    }
    label {
      display: block;
      font-weight: 500;
      margin-bottom: 0.3rem;
      margin-top: 1rem;
    }
    input[type="text"], 
    input[type="email"], 
    input[type="password"], 
    select {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1rem;
    }
    .radio-group {
      margin-top: 0.5rem;
    }
    .radio-group label {
      display: inline-block;
      margin-right: 1rem;
      font-weight: normal;
    }
    .dob-row {
      display: flex;
      gap: 1rem;
      margin-top: 0.5rem;
    }
    .dob-row select {
      flex: 1;
    }
    .btn {
      background: #4CAF50;
      color: white;
      border: none;
      padding: 0.6rem 1.2rem;
      border-radius: 4px;
      cursor: pointer;
      font-size: 1rem;
      margin-top: 1.5rem;
    }
    .btn:hover {
      background: #45a049;
    }
  </style>
</head>
<body>
<header>
  <h1>My Profile</h1>
  <button onclick="window.location.href='index.php'" class="btn btn-secondary" style="margin-top:1rem;">
    ← Back
  </button>
</header>

<main class="profile-container">
  <!-- Profile form -->
  <form id="profileForm">
    <input type="hidden" name="role" id="role">
    <input type="hidden" name="id" id="id">

    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="">

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="">

    <label for="phone">Phone Number</label>
    <input type="text" id="phone" name="phone">

    <label>Gender</label>
    <div class="radio-group">
      <label><input type="radio" name="gender" value="male"> Male</label>
      <label><input type="radio" name="gender" value="female"> Female</label>
      <label><input type="radio" name="gender" value="other"> Other</label>
    </div>

    <label>Date of Birth</label>
    <div class="dob-row">
      <select name="day" id="day">
        <option value="">Date</option>
        <!-- 1–31 -->
        <script>
          for (let i=1; i<=31; i++) {
            document.write(`<option value="${i}">${i}</option>`);
          }
        </script>
      </select>
      <select name="month" id="month">
        <option value="">Month</option>
        <option>January</option><option>February</option><option>March</option>
        <option>April</option><option>May</option><option>June</option>
        <option>July</option><option>August</option><option>September</option>
        <option>October</option><option>November</option><option>December</option>
      </select>
      <select name="year" id="year">
        <option value="">Year</option>
        <script>
          const yearNow = new Date().getFullYear();
          for (let y = yearNow; y >= 1900; y--) {
            document.write(`<option value="${y}">${y}</option>`);
          }
        </script>
      </select>
    </div>

    <button type="submit" class="btn">Save</button>
  </form>
</main>

<script src="../js/config.js"></script>
<script src="../js/profile.js"></script>
<?php include(__DIR__ . '/../components/sidebar.php'); ?>
</body>
</html>
