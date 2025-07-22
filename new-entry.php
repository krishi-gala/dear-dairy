<?php
// new-entry.php
require_once 'config/config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $entry_date = $_POST['entry_date'] ?? date('Y-m-d');
    $mood = $_POST['mood'] ?? null;
    
    // Validate
    if (empty($title)) $errors[] = 'Title is required';
    if (empty($content)) $errors[] = 'Content is required';
    
    if (empty($errors)) {
        try {
            // Start transaction
            $conn->beginTransaction();
            
            // Insert into diary_entries
            $stmt = $conn->prepare("INSERT INTO diary_entries (user_id, title, content, entry_date, mood) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([getUserId(), $title, $content, $entry_date, $mood]);
            
            // If mood is selected, also insert into mood_tracker
            if ($mood) {
                $stmt = $conn->prepare("INSERT INTO mood_tracker (user_id, mood, entry_date) 
                                       VALUES (?, ?, ?)
                                       ON DUPLICATE KEY UPDATE mood = VALUES(mood)");
                $stmt->execute([getUserId(), $mood, $entry_date]);
            }
            
            $conn->commit();
            $success = true;
            // Reset form if needed
            $_POST = [];
        } catch (PDOException $e) {
            $conn->rollBack();
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>New Journal Entry | Dear Diary</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;500;600;700&family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #8b5fbf; /* Sophisticated purple */
      --primary-light: #e9e1f5;
      --primary-dark: #6a4a8f;
      --secondary-color: #d4c4ed;
      --accent-color: #b388ff;
      --accent-secondary: #e8a8c5; /* Brighter pink-mauve */
      --accent-secondary-dark: #d490b3; /* Slightly darker shade */
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
      --paper-color: #fffef7;
      
      /* Mood Colors */
      --joy: #FFD166;
      --sadness: #5B7DB1;
      --anger: #EF476F;
      --fear: #af7ac5;
      --disgust: #7FB069;
      --no-entry: #F0F0F0;
    }
    
    body {
      font-family: 'Source Sans Pro', sans-serif;
      color: var(--text-color);
      line-height: 1.6;
      background-color: var(--paper-color);
      min-height: 100vh;
    }
    
    /* Navbar - Consistent with other pages */
    .navbar {
      background-color: rgba(255, 255, 255, 0.95);
      box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
      padding: 0.8rem 1.5rem;
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
    
    /* Journal Entry Container */
    .entry-container {
      max-width: 800px;
      margin: 3rem auto;
      background-color: white;
      border-radius: 15px;
      box-shadow: var(--box-shadow);
      padding: 2.5rem;
      border: 1px solid var(--primary-light);
    }
    
    .entry-header {
      text-align: center;
      margin-bottom: 2.5rem;
      position: relative;
    }
    
    .entry-header h1 {
      font-family: 'Caveat', cursive;
      font-size: 2.5rem;
      color: var(--primary-dark);
      margin-bottom: 1rem;
    }
    
    .entry-header:after {
      content: '';
      display: block;
      width: 100px;
      height: 3px;
      background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
      margin: 1rem auto;
    }
    
    /* Mood Selector - Enhanced Version */
    .mood-selector {
      margin-bottom: 2rem;
      background-color: rgba(255, 255, 255, 0.7);
      border-radius: 15px;
      padding: 1.5rem;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .mood-selector h3 {
      color: var(--accent-secondary-dark);
      margin-bottom: 1.5rem;
      font-weight: 600;
      text-align: center;
    }
    
    .mood-options {
      display: flex;
      justify-content: center;
      gap: 1.5rem;
      margin-top: 1.5rem;
      flex-wrap: wrap;
    }
    
    .mood-option {
      display: flex;
      flex-direction: column;
      align-items: center;
      cursor: pointer;
      transition: transform 0.2s ease;
    }
    
    .mood-option:hover {
      transform: scale(1.05);
    }
    
    .mood-option.selected .mood-icon {
      transform: scale(1.1);
      box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.1);
    }
    
    .mood-icon {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 0.5rem;
      font-size: 1.8rem;
      transition: all 0.2s ease;
    }
    .mood-icon img {
      max-width: 100%;
      height: auto;
      object-fit: contain;
    }

    .mood-icon.joy {
      background-color: var(--joy);
      color: #000;
    }
    
    .mood-icon.sadness {
      background-color: var(--sadness);
      color: #fff;
    }
    
    .mood-icon.anger {
      background-color: var(--anger);
      color: #fff;
    }
    
    .mood-icon.fear {
      background-color: var(--fear);
      color: #fff;
    }
    
    .mood-icon.disgust {
      background-color: var(--disgust);
      color: #fff;
    }
    
    /* Mood Legend */
    .mood-legend {
      margin-top: 1.5rem;
      text-align: center;
    }
    
    .mood-legend h4 {
      font-size: 1rem;
      color: var(--text-light);
      margin-bottom: 0.5rem;
    }
    
    .mood-legend-items {
      display: flex;
      justify-content: center;
      gap: 1rem;
      flex-wrap: wrap;
    }
    
    .mood-legend-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.9rem;
    }
    
    .mood-color {
      width: 16px;
      height: 16px;
      border-radius: 50%;
    }
    
    .mood-color.joy {
      background-color: var(--joy);
    }
    
    .mood-color.sadness {
      background-color: var(--sadness);
    }
    
    .mood-color.anger {
      background-color: var(--anger);
    }
    
    .mood-color.fear {
      background-color: var(--fear);
    }
    
    .mood-color.disgust {
      background-color: var(--disgust);
    }
    
    /* Sticky Note Text Area with Mood Colors */
    .sticky-note {
      background-color: #fef9c3; /* Default color */
      border: 1px solid #fde047;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      font-family: 'Caveat', cursive;
      font-size: 1.4rem;
      padding: 20px;
      line-height: 1.5;
      min-height: 250px;
      width: 100%;
      border-radius: 10px;
      transition: all 0.5s ease;
    }
    
    /* Mood-specific background colors */
    /* Sticky Note Text Area with Mood Colors */
.sticky-note {
  background-color: #fef9c3; /* Default color */
  border: 1px solid #fde047;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  font-family: 'Caveat', cursive;
  font-size: 1.4rem;
  padding: 20px;
  line-height: 1.5;
  min-height: 250px;
  width: 100%;
  border-radius: 10px;
  transition: all 0.5s ease;
}

/* Mood-specific background colors - More vibrant versions */
.sticky-note.joy {
  background-color: rgba(255, 209, 102, 0.4);
  border-color: var(--joy);
}

.sticky-note.sadness {
  background-color: rgba(91, 125, 177, 0.4);
  border-color: var(--sadness);
  color: #333;
}

.sticky-note.anger {
  background-color: rgba(239, 71, 111, 0.4);
  border-color: var(--anger);
}

.sticky-note.fear {
  background-color:rgb(215, 178, 230);
  border-color: var(--fear);
}

.sticky-note.disgust {
  background-color: rgba(127, 176, 105, 0.4);
  border-color: var(--disgust);
}

.sticky-note:focus {
  box-shadow: 0 0 0 0.25rem rgba(234, 179, 8, 0.25);
  outline: none;
}
    /* Form Elements */
    .form-group {
      margin-bottom: 1.8rem;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 0.6rem;
      font-weight: 600;
      color: var(--primary-dark);
    }
    
    .form-control {
      border: 1px solid #e0e0e0;
      border-radius: 10px;
      padding: 0.8rem 1rem;
      font-size: 1rem;
      transition: var(--transition);
    }
    
    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(236, 137, 142, 0.25);
    }
    
    /* Date Picker */
    .date-picker {
      position: relative;
    }
    
    .date-picker i {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--primary-color);
      pointer-events: none;
    }
    
    /* Buttons */
    .btn {
      padding: 0.7rem 1.5rem;
      border-radius: 10px;
      font-weight: 600;
      transition: var(--transition);
      border-width: 2px;
    }
    
    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }
    
    .btn-primary:hover {
      background-color: var(--primary-dark);
      border-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(236, 137, 142, 0.3);
    }
    
    .btn-block {
      display: block;
      width: 100%;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
      .entry-container {
        padding: 1.5rem;
        margin: 1.5rem auto;
      }
      
      .entry-header h1 {
        font-size: 2rem;
      }
      
      .mood-options {
        gap: 1rem;
      }
      
      .mood-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
      }
    }
    
    @media (max-width: 576px) {
      .mood-options {
        gap: 0.5rem;
      }
      
      .mood-icon {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
      }
      
      .sticky-note {
        font-size: 1.2rem;
      }
    }
  </style>
</head>
<body>
  <!-- Navigation - Consistent with other pages -->
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
            <a class="nav-link" href="index.php">Home</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="journalDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Journal
            </a>
            <ul class="dropdown-menu" aria-labelledby="journalDropdown">
              <li><a class="dropdown-item active" href="new-entry.php">New Entry</a></li>
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

  <!-- Journal Entry Form -->
  <div class="container">
    <div class="entry-container">
      <div class="entry-header">
        <h1>Create New Journal Entry</h1>
        <p>Express your thoughts and feelings</p>
      </div>
      
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul>
            <?php foreach ($errors as $error): ?>
              <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div class="alert alert-success">
          Entry saved successfully!
        </div>
      <?php endif; ?>
      
      <form method="POST" action="new-entry.php">
        <!-- Mood Selector -->
        <div class="mood-selector">
          <h3>How are you feeling today?</h3>
          <div class="mood-options">
            <?php
            $moods = [
                'joy' => ['icon' => 'fa-smile', 'label' => 'Joy'],
                'sadness' => ['icon' => 'fa-sad-tear', 'label' => 'Sadness'],
                'anger' => ['icon' => 'fa-angry', 'label' => 'Anger'],
                'fear' => ['icon' => 'fa-surprise', 'label' => 'Fear'],
                'disgust' => ['icon' => 'fa-grimace', 'label' => 'Disgust']
            ];
            
            foreach ($moods as $key => $moodData): ?>
              <div class="mood-option" data-mood="<?= $key ?>">
              <div class="mood-icon <?= $key ?>">
               <img src="images/<?= $key ?>.png" alt="<?= $moodData['label'] ?>" style="width:50px; height:50px;">
              </div>

                <span><?= $moodData['label'] ?></span>
              </div>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="mood" id="selected-mood" value="<?= $_POST['mood'] ?? '' ?>">
        </div>
        
        <!-- Title -->
        <div class="form-group">
          <label for="entry-title">Title</label>
          <input type="text" class="form-control" id="entry-title" name="title" 
                 value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" placeholder="Give your entry a title" required>
        </div>
        
        <!-- Content -->
        <div class="form-group">
          <label for="entry-content">Write your thoughts...</label>
          <textarea class="sticky-note <?= !empty($_POST['mood']) ? $_POST['mood'] : '' ?>" id="entry-content" name="content" 
                    placeholder="Dear Diary..." required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
        </div>
        
        <!-- Date -->
        <div class="form-group date-picker">
          <label for="entry-date">Date</label>
          <input type="date" class="form-control" id="entry-date" name="entry_date" 
                 value="<?= htmlspecialchars($_POST['entry_date'] ?? date('Y-m-d')) ?>">
          <i class="fas fa-calendar-alt"></i>
        </div>
        
        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary btn-block mt-4">
          <i class="fas fa-save me-2"></i>Save Entry
        </button>
      </form>
    </div>
  </div>

  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
      if (window.scrollY > 50) {
        document.querySelector('.navbar').classList.add('scrolled');
      } else {
        document.querySelector('.navbar').classList.remove('scrolled');
      }
    });
    
    // Mood selection functionality
    const moodOptions = document.querySelectorAll('.mood-option');
    const selectedMoodInput = document.getElementById('selected-mood');
    const stickyNote = document.getElementById('entry-content');
    
    moodOptions.forEach(option => {
      option.addEventListener('click', function() {
        const mood = this.getAttribute('data-mood');
        selectedMoodInput.value = mood;
        
        // Update UI
        moodOptions.forEach(opt => opt.classList.remove('selected'));
        this.classList.add('selected');
        
        // Update sticky note color
        stickyNote.className = 'sticky-note ' + mood;
      });
    });
    
    // Initialize with any previously selected mood
    <?php if (!empty($_POST['mood'])): ?>
      document.querySelector(`.mood-option[data-mood="<?= $_POST['mood'] ?>"]`).classList.add('selected');
      document.getElementById('entry-content').classList.add('<?= $_POST['mood'] ?>');
    <?php endif; ?>
  </script>
</body>
</html>