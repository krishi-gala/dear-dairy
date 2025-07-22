<?php
// index.php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dear Diary - Your Personal Journal</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #8b5fbf; /* Sophisticated purple */
      --primary-light: #e9e1f5;
      --primary-dark: #6a4a8f;
      --secondary-color: #d4c4ed;
      --accent-color: #b388ff;
      --accent-secondary:rgb(231, 131, 176); /* Brighter pink-mauve */
      --accent-secondary-dark:rgb(204, 117, 162); /* Slightly darker shade */
      --light-color: #f8f9fa;
      --dark-color: #343a40;
      --text-color: #333333;
      --text-light: #6c757d;
      --success-color: #28a745;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
      --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      --transition: all 0.3s ease;
      --border-radius: 8px;
      --paper-color: #fffef7; /* Warm paper-like background */
      --ink-color: #333333; /* Darker text for better readability */
    }
    
    body {
      font-family: 'Source Sans Pro', sans-serif;
      color: var(--text-color);
      line-height: 1.6;
      background-color: var(--paper-color);
    }
    
    h1, h2, h3, h4, h5, h6 {
      font-family: 'Playfair Display', serif;
      font-weight: 600;
      color: var(--dark-color);
    }
    
    /* Navbar - More refined */
    .navbar {
      background-color: rgba(255, 255, 255, 0.95);
      box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
      padding: 0.8rem 1rem;
      transition: var(--transition);
      backdrop-filter: blur(5px);
    }
    
    .navbar.scrolled {
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }
    
    .navbar-brand {
      font-family: 'Caveat', cursive;
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary-color);
      transition: var(--transition);
    }
    
    .navbar-brand:hover {
      color: var(--primary-dark);
    }
    
    .navbar-brand i {
      margin-right: 10px;
      color: var(--primary-color);
    }
    
    .nav-link {
      font-weight: 500;
      padding: 0.5rem 1.2rem;
      color: var(--text-color);
      transition: var(--transition);
      position: relative;
    }
    
    .nav-link:before {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      background-color: var(--primary-color);
      transition: var(--transition);
      visibility: hidden;
    }
    
    .nav-link:hover:before,
    .nav-link.active:before {
      visibility: visible;
      width: 70%;
    }
    
    .nav-link:hover {
      color: var(--primary-color);
    }
    
    .nav-link.active {
      color: var(--primary-color);
      font-weight: 600;
    }
    
    .dropdown-menu {
      border: none;
      box-shadow: var(--box-shadow);
      border-radius: 10px;
      padding: 0.5rem 0;
      background-color: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(5px);
    }
    
    .dropdown-item {
      padding: 0.5rem 1.5rem;
      transition: var(--transition);
    }
    
    .dropdown-item:hover {
      background-color: var(--primary-light);
      color: var(--primary-dark);
    }
    
    /* Hero Section - More elegant */
    .hero-section {
      background: linear-gradient(135deg, var(--primary-light), white);
      padding: 3rem 0;
      position: relative;
      border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .hero-title {
      font-size: 2.8rem;
      margin-bottom: 1rem;
      line-height: 1.2;
      color: var(--primary-color);
    }
    
    .hero-subtitle {
      font-size: 1.2rem;
      margin-bottom: 1.5rem;
      color: var(--text-light);
      max-width: 600px;
    }
    
    .hero-image img {
      border-radius: var(--border-radius);
      box-shadow: 0 15px 30px rgba(0,0,0,0.1);
      max-height: 400px;
      object-fit: cover;
      border: 1px solid var(--accent-secondary);
    }
    
    /* Thought of the Day - More eye-catching */
    .thought-of-day-container {
      background: linear-gradient(135deg, var(--primary-light), white);
      border-radius: var(--border-radius);
      padding: 2rem;
      box-shadow: var(--box-shadow);
      margin: 2rem auto;
      border-left: 4px solid var(--accent-secondary);
      position: relative;
      overflow: hidden;
    }
    
    .thought-of-day-container:before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100" fill="none" opacity="0.05"><path d="M30,50 Q50,30 70,50 T90,50" stroke="%23e8a8c5" stroke-width="2"/></svg>');
      opacity: 0.1;
    }
    
    .thought-header {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1rem;
    }
    
    .thought-header i {
      color: var(--accent-secondary);
      font-size: 1.5rem;
      margin: 0 0.5rem;
    }
    
    .thought-content {
      font-style: italic;
      font-size: 1.3rem;
      color: var(--text-color);
      position: relative;
      padding: 1rem;
      text-align: center;
      font-family: 'Playfair Display', serif;
    }
    
    .thought-content:before {
      content: '\201C';
      font-family: 'Playfair Display', serif;
      font-size: 5rem;
      color: var(--primary-light);
      position: absolute;
      top: -20px;
      left: -10px;
      line-height: 1;
      z-index: 0;
    }
    
    .thought-author {
      color: var(--accent-secondary);
      font-weight: 500;
    }
    
    /* Feature Boxes - More professional */
    .feature-box {
      background-color: white;
      border-radius: var(--border-radius);
      padding: 1.5rem;
      height: 100%;
      box-shadow: var(--box-shadow);
      transition: var(--transition);
      border-bottom: 3px solid var(--accent-secondary);
      margin-bottom: 1rem;
    }
    
    .feature-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .feature-icon {
      font-size: 2rem;
      margin-bottom: 1rem;
      color: var(--accent-secondary);
    }
    
    /* Buttons - More sophisticated */
    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
      padding: 0.6rem 1.5rem;
      border-radius: var(--border-radius);
      font-weight: 500;
    }

    .btn-primary:hover {
      background-color: var(--accent-secondary-dark);
      border-color: var(--primary-color);
      padding: 0.6rem 1.5rem;
      border-radius: var(--border-radius);
      font-weight: 500;
    }
    
    .btn-outline-primary {
      color: var(--primary-color);
      border-color: var(--primary-color);
      padding: 0.6rem 1.5rem;
      border-radius: var(--border-radius);
      font-weight: 500;
    }
    
    .btn-outline-primary:hover {
      background-color: var(--accent-secondary-dark);
    }
    
    .btn-accent {
      background-color: var(--accent-secondary);
      border-color: var(--accent-secondary);
      color: white;
      padding: 0.6rem 1.5rem;
      border-radius: var(--border-radius);
      font-weight: 500;
    }
    
    .btn-accent:hover {
      background-color: var(--accent-secondary-dark);
      border-color: var(--accent-secondary-dark);
    }
    
    /* Recent Entries Card */
    .recent-entries-card {
      border: none;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      overflow: hidden;
      margin-bottom: 3rem;
      background-color: white;
      border-top: 3px solid var(--accent-secondary);
    }
    
    .recent-entries-card .card-header {
      background-color: white;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      padding: 1rem;
      font-family: 'Playfair Display', serif;
    }
    
    .recent-entries-card .card-header h3 {
      margin: 0;
      color: var(--primary-color);
    }
    
    .no-entries {
      color: var(--text-light);
      font-style: italic;
      position: relative;
      display: inline-block;
    }
    
    .no-entries a {
      color: var(--text-color);
      text-decoration: none;
      font-weight: 500;
      transition: var(--transition);
      position: relative;
    }
    
    .no-entries a:after {
      content: '';
      position: absolute;
      width: 100%;
      height: 2px;
      bottom: -2px;
      left: 0;
      background-color: var(--accent-secondary);
      transform: scaleX(0);
      transform-origin: right;
      transition: transform 0.3s ease;
    }
    
    .no-entries a:hover:after {
      transform: scaleX(1);
      transform-origin: left;
    }
    
    .no-entries a:hover {
      color: var(--accent-secondary-dark);
    }
    
    /* Quick Nav Section - More professional */
    .quick-nav-section {
      background-color: white;
      padding: 2rem 0;
      margin: 2rem 0;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      border: 1px solid var(--accent-secondary);
    }
    
    .quick-nav-link {
      background-color: var(--accent-secondary);
      color: white;
      padding: 0.8rem 1.2rem;
      border-radius: var(--border-radius);
      text-decoration: none;
      font-weight: 500;
      transition: var(--transition);
      display: block;
      margin-bottom: 0.8rem;
      border-left: 4px solid var(--primary-color);
      width: 500px;
    }
    
    .quick-nav-link:hover {
      background-color: var(--accent-secondary-dark);
      color: white;
      transform: translateX(5px);
    }
    
    /* Responsive Adjustments */
    @media (max-width: 992px) {
      .hero-title {
        font-size: 2.2rem;
      }
      
      .hero-subtitle {
        font-size: 1.1rem;
      }
    }
    
    @media (max-width: 768px) {
      .hero-section {
        padding: 2rem 0;
      }
      
      .hero-title {
        font-size: 2rem;
      }
      
      .thought-content {
        font-size: 1.1rem;
      }
    }
  </style>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <i class="fas fa-book-open"></i> Dear Diary
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link active" href="index.php">Home</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="journalDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Journal
            </a>
            <ul class="dropdown-menu" aria-labelledby="journalDropdown">
              <li><a class="dropdown-item" href="new-entry.php">New Entry</a></li>
              <li><a class="dropdown-item" href="journal-history.php">History</a></li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="mood-tracker.php">Moods</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="goals.php">Goals</a>
          </li>
          <?php if (isLoggedIn()): ?>
            <li class="nav-item">
              <a class="nav-link" href="logout.php">Logout</a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="login.php">Login</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="register.php">Register</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6">
          <h1 class="hero-title">Reflect, Grow, and Discover</h1>
          <p class="hero-subtitle">A sophisticated journaling platform for your personal journey and self-reflection.</p>
          <?php if (isLoggedIn()): ?>
            <a href="new-entry.php" class="btn btn-primary me-2">
              <i class="fas fa-pencil-alt me-2"></i>Start Journaling
            </a>
          <?php else: ?>
            <a href="register.php" class="btn btn-primary me-2">
              <i class="fas fa-user-plus me-2"></i>Get Started
            </a>
          <?php endif; ?>
          <a href="#features" class="btn btn-outline-primary">
            Learn More
          </a>
        </div>
        <div class="col-lg-6 mt-4 mt-lg-0">
          <div class="hero-image text-center">
            <img src="book.jpeg" alt="Elegant journal book" class="img-fluid">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Main Content Container -->
  <div class="container">
    <!-- Thought of the Day -->
    <div class="thought-of-day-container">
      <div class="thought-header">
        <i class="fas fa-quote-left"></i>
        <h4 class="m-0" style="color: var(--accent-secondary);">Thought of the Day</h4>
        <i class="fas fa-quote-right"></i>
      </div>
      <div class="thought-content">
        <p id="daily-thought" class="mb-1">"The journey of a thousand miles begins with a single step."</p>
        <div class="thought-author">― <span id="thought-author">Lao Tzu</span></div>
      </div>
    </div>
    
    <!-- Features -->
    <section id="features" class="my-4 py-3">
      <div class="row">
        <div class="col-md-4">
          <div class="feature-box">
            <i class="fas fa-book-open feature-icon"></i>
            <h3>Daily Reflections</h3>
            <p>Capture your thoughts and experiences in a clean, distraction-free writing environment designed for focus.</p>
            <a href="new-entry.php" class="btn btn-accent mt-2">Begin Writing</a>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-box">
            <i class="fas fa-chart-pie feature-icon"></i>
            <h3>Mood Analytics</h3>
            <p>Track and visualize your emotional patterns with our insightful mood tracking and analytics.</p>
            <a href="mood-tracker.php" class="btn btn-accent mt-2">Track Now</a>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-box">
            <i class="fas fa-bullseye feature-icon"></i>
            <h3>Goal Tracking</h3>
            <p>Set meaningful objectives and monitor your progress with our goal achievement system.</p>
            <a href="goals.php" class="btn btn-accent mt-2">Get Started</a>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Quick Navigation -->
    <section class="quick-nav-section">
      <div class="container">
        <div class="row">
          <div class="col-lg-6 mb-4 mb-lg-0">
            <h3 class="mb-3" style="color: var(--primary-color);">Quick Access</h3>
            <p class="text-muted mb-3">Navigate quickly to the features you use most often.</p>
            <div class="quick-nav-links">
              <a href="new-entry.php" class="quick-nav-link">
                <i class="fas fa-plus me-2"></i> New Journal Entry
              </a>
              <a href="mood-tracker.php" class="quick-nav-link">
                <i class="fas fa-chart-line me-2"></i> Mood Tracker
              </a>
              <a href="goals.php" class="quick-nav-link">
                <i class="fas fa-bullseye me-2"></i> Goals Dashboard
              </a>
              <a href="journal-history.php" class="quick-nav-link">
                <i class="fas fa-history me-2"></i> Journal Archive
              </a>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="quick-nav-gif h-100 d-flex align-items-center">
              <img src="source.gif" alt="Journaling illustration" class="img-fluid rounded">
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Recent Entries -->
    <div class="card recent-entries-card mt-4">
      <div class="card-header">
        <h3><i class="fas fa-clock me-2"></i>Recent Entries</h3>
      </div>
      <div class="card-body">
        <div id="recent-entries" class="text-center py-3">
        <?php
if (isLoggedIn()) {
    try {
        $stmt = $conn->prepare("SELECT * FROM diary_entries WHERE user_id = ? ORDER BY entry_date DESC LIMIT 3");
        $stmt->execute([getUserId()]);
        $entries = $stmt->fetchAll();
        
        if (count($entries) > 0) {
            echo '<div class="list-group">';
            foreach ($entries as $entry) {
                echo '<a href="view-entry.php?id='.$entry['entry_id'].'" class="list-group-item list-group-item-action">';
                echo '<h5>'.$entry['title'].'</h5>';
                echo '<small class="text-muted">'.date('F j, Y', strtotime($entry['entry_date'])).'</small>';
                echo '</a>';
            }
            echo '</div>';
        } else {
            echo '<p class="no-entries">No recent entries yet. <a href="new-entry.php">Create your first entry →</a></p>';
        }
    } catch (PDOException $e) {
        echo '<p class="text-danger">Error loading entries</p>';
    }
} else {
    echo '<p class="no-entries">Please <a href="login.php">login</a> to view your entries</p>';
}
?>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-light py-3 mt-4 border-top">
    <div class="container text-center text-muted">
      <p>&copy; 2025 Dear Diary. All rights reserved.</p>
    </div>
  </footer>

  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Simple thought of the day generator
    const thoughts = [
      {text: "You are enough just as you are.", author: "Megan Markle"},
      {text: "The only way to do great work is to love what you do.", author: "Steve Jobs"},
      {text: "Happiness is not something ready made. It comes from your own actions.", author: "Dalai Lama"},
      {text: "Believe you can and you're halfway there.", author: "Theodore Roosevelt"},
      {text: "The present moment is the only moment available to us.", author: "Thich Nhat Hanh"}
    ];
    
    // Set random thought of the day
    const randomThought = thoughts[Math.floor(Math.random() * thoughts.length)];
    document.getElementById('daily-thought').textContent = `"${randomThought.text}"`;
    document.getElementById('thought-author').textContent = randomThought.author;
    
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
      if (window.scrollY > 50) {
        document.querySelector('.navbar').classList.add('scrolled');
      } else {
        document.querySelector('.navbar').classList.remove('scrolled');
      }
    });
  </script>
</body>
</html>