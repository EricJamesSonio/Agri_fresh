<?php include(__DIR__ . '/../components/sidebar.php'); ?>
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
    input[type="password"] {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1rem;
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
    .hidden {
      display: none;
    }
    #addresses {
      margin-top: 2rem;
    }
    .address-card {
      background: #f9f9f9;
      padding: 0.8rem 1rem;
      border-radius: 5px;
      border: 1px solid #ddd;
      margin-bottom: 0.5rem;
    }
    #address-message.success {
      color: green;
    }
    #address-message.error {
      color: red;
    }
    #add-address-form {
      display: none;
    }
    #add-address-form.show {
      display: block;
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
  <form id="profileForm">
    <input type="hidden" id="role" name="role">
    <input type="hidden" id="id" name="id">

    <label for="first_name">First Name</label>
    <input type="text" id="first_name" name="first_name" value="">

    <label for="last_name">Last Name</label>
    <input type="text" id="last_name" name="last_name" value="">

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="">

    <div id="contactWrapper" class="hidden">
      <label for="contact">Contact Number</label>
      <input type="text" id="contact" name="contact" value="">
    </div>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" placeholder="Enter new password (optional)">

    <button type="submit" class="btn">Save</button>
  </form>

  <!-- Addresses section (only visible for customers) -->
  <section id="addresses" class="hidden">
    <h2>Saved Addresses</h2>
    <div id="address-list"></div>
    <p id="address-message"></p>

    <button id="addAddressBtn" class="btn" type="button">+ Add Address</button>

    <div id="add-address-form">
      <form id="addressForm">
        <label>Street</label>
        <input type="text" name="street" required>
        <label>City</label>
        <input type="text" name="city" required>
        <label>State</label>
        <input type="text" name="state">
        <label>Postal Code</label>
        <input type="text" name="postal_code" required>
        <label>Country</label>
        <input type="text" name="country" required>

        <button type="submit" class="btn">Save Address</button>
        <button type="button" class="btn btn-secondary" onclick="toggleAddressForm()">Cancel</button>
      </form>
    </div>
  </section>
</main>

<script src="../js/config.js"></script>
<script src="../js/profile.js"></script>

</body>
</html>
