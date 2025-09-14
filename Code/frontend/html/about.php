<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>About Us – Agri Fresh</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/about.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  
</head>
<body>
  <?php include(__DIR__ . '/../components/sidebar.php'); ?>
  <!-- Re-use the header from index -->
  <header>
    <h1>Agri Fresh Market</h1>
    <nav>
      <a href="index.php">Home</a>
      <a href="index.php#products">Products</a>
      <a href="login.php">Log in</a>
    </nav>
  </header>

  <main class="about-wrapper">
    <h1>About AgriFresh</h1>
    <br>

    <section>
      <h2>Our Story</h2>
      <p>
        AgriFresh started in 2025 with one simple goal: bring the freshest
        organic produce from local farms straight to your doorstep.  We cut
        out long supply chains, traffic, and middle-men so that every leaf,
        root, and fruit arrives at peak flavor and nutrition.
      </p>
      <br><br>
    </section>


    <section>
      <h2>What We Believe</h2>
      <ul>
        <li><strong>Fair prices</strong> for farmers and consumers.</li>
        <li><strong>Zero pesticides</strong> on anything we sell.</li>
        <li><strong>Zero waste</strong>—unsold produce is donated to shelters.</li>
        <li><strong>Community first</strong>—we partner with 30+ small farms
            within a 60-km radius.</li>
      </ul>
      <br><br>
    </section>
   

   <section>
  <h2>Meet the Team</h2>
  <div class="team-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:20px;">
    
    <div class="team-card" style="text-align:center; padding:10px; background:#f9f9f9; border-radius:8px;">
      <img src="../pic/she.jpg" alt="Sheirlyn Reyes" style="width:150px; height:150px; object-fit:cover; border-radius:50%;">
      <h3>Sheirlyn Reyes</h3>
      <p>Founder</p>
    </div>
    
    <div class="team-card" style="text-align:center; padding:10px; background:#f9f9f9; border-radius:8px;">
      <img src="../pic/earl.jpg" alt="Earl John Oliveros" style="width:150px; height:150px; object-fit:cover; border-radius:50%;">
      <h3>Earl John Oliveros</h3>
      <p>Co-founder</p>
    </div>
    
    <div class="team-card" style="text-align:center; padding:10px; background:#f9f9f9; border-radius:8px;">
      <img src="../pic/ley.jpg" alt="John Lery Concepcion" style="width:150px; height:150px; object-fit:cover; border-radius:50%;">
      <h3>John Lery Concepcion</h3>
      <p>Community & Partnerships</p>
    </div>
    
    <div class="team-card" style="text-align:center; padding:10px; background:#f9f9f9; border-radius:8px;">
      <img src="../pic/1.jpg" alt="Rey Mark Nacor" style="width:150px; height:150px; object-fit:cover; border-radius:50%;">
      <h3>Rey Mark Nacor</h3>
      <p>Community & Partnerships</p>
    </div>
    
    <div class="team-card" style="text-align:center; padding:10px; background:#f9f9f9; border-radius:8px;">
      <img src="../pic/macky.jpg" alt="Marc Lowell Apin" style="width:150px; height:150px; object-fit:cover; border-radius:50%;">
      <h3>Marc Lowell Apin</h3>
      <p>Community & Partnerships</p>
    </div>

  </div>
</section>


    <section>
      <h2>Visit Us</h2>
      <p>
        Pandi, Bulacan <br>
        College of Mary Immaculate <br>
        BSCS 3A
      </p>
    </section>
  </main>

  <footer>
    <p style="text-align:center;padding:1.5rem 0;color:#666;">
      &copy; 2025 AgriFresh Market – Freshness Delivered.
    </p>
  </footer>
  

</body>
</html>