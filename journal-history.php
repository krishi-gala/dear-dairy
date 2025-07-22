<?php

require_once 'config/config.php'; // Make sure this sets up $conn as a PDO object

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$moodFilter = isset($_GET['mood']) ? $_GET['mood'] : '';
$dateFilter = isset($_GET['month']) ? $_GET['month'] : '';

$user_id = $_SESSION['user_id'];

// Build the SQL query with filters
$sql = "SELECT * FROM diary_entries WHERE user_id = :user_id";
$params = ['user_id' => $user_id];

if (!empty($searchQuery)) {
    $sql .= " AND (title LIKE :search OR content LIKE :search)";
    $params['search'] = "%$searchQuery%";
}

if (!empty($moodFilter)) {
    $sql .= " AND mood = :mood";
    $params['mood'] = $moodFilter;
}

if (!empty($dateFilter)) {
    $sql .= " AND DATE_FORMAT(entry_date, '%Y-%m') = :month";
    $params['month'] = $dateFilter;
}

$sql .= " ORDER BY entry_date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);

$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal History | Dear Diary</title>
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
            --fear: #A37A74;
            --disgust: #7FB069;
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
        
        /* Journal History Container */
        .journal-container {
            max-width: 900px;
            margin: 3rem auto;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2.5rem;
            border: 1px solid var(--primary-light);
        }
        
        .journal-header {
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
        }
        
        .journal-header h1 {
            font-family: 'Caveat', cursive;
            font-size: 2.5rem;
            color: var(--primary-dark);
            margin-bottom: 1rem;
        }
        
        .journal-header:after {
            content: '';
            display: block;
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            margin: 1rem auto;
        }
        
        /* Filters */
        .journal-filters {
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }
        
        .input-group-text {
            background-color: var(--primary-light);
            border-color: var(--primary-light);
            color: var(--primary-dark);
        }
        
        .form-control, .form-select {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 0.7rem 1rem;
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(236, 137, 142, 0.25);
        }
        
        /* Entry Cards */
        .entry-card {
            background-color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--box-shadow);
            border-left: 4px solid var(--primary-color);
            transition: var(--transition);
        }
        
        .entry-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .entry-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .entry-title {
            font-weight: 600;
            color: var(--primary-dark);
            margin: 0;
        }
        
        .entry-date {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .entry-content {
            color: var(--text-color);
            margin: 1rem 0;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .entry-moods {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .mood-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .mood-badge.joy {
            background-color: rgba(255, 209, 102, 0.2);
            color: #b38a2a;
        }
        
        .mood-badge.sadness {
            background-color: rgba(91, 125, 177, 0.2);
            color: #3a5270;
        }
        
        .mood-badge.anger {
            background-color: rgba(239, 71, 111, 0.2);
            color: #c23a5a;
        }
        
        .mood-badge.fear {
            background-color: rgba(163, 122, 116, 0.2);
            color: #6e514c;
        }
        
        .mood-badge.disgust {
            background-color: rgba(127, 176, 105, 0.2);
            color: #4e6d3d;
        }
        
        .entry-actions {
            display: flex;
            gap: 0.8rem;
        }
        
        /* Buttons */
        .btn {
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
            border-width: 2px;
        }
        
        .btn-sm {
            padding: 0.3rem 0.8rem;
            font-size: 0.85rem;
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        /* Empty State */
        .empty-state {
            background-color: white;
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            box-shadow: var(--box-shadow);
        }
        
        .empty-icon {
            font-size: 3rem;
            color: var(--primary-light);
            margin-bottom: 1.5rem;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .journal-container {
                padding: 1.5rem;
                margin: 1.5rem auto;
            }
            
            .journal-header h1 {
                font-size: 2rem;
            }
            
            .entry-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
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
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="journalDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Journal
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="journalDropdown">
                            <li><a class="dropdown-item" href="new-entry.php">New Entry</a></li>
                            <li><a class="dropdown-item active" href="journal-history.php">History</a></li>
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

    <!-- Journal History Content -->
  <div class="container">
    <div class="journal-container">
      <div class="journal-header">
        <h1>Journal History</h1>
        <p>Reflect on your past thoughts and feelings</p>
      </div>
      
      <!-- Filters -->
      <div class="journal-filters">
        <form method="GET" action="journal-history.php">
          <div class="row g-2">
            <div class="col-md-4">
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>">

              </div>
            </div>
            <div class="col-md-3">
              <select class="form-select" name="mood">
                <option value="">All Moods</option>
                <option value="joy" <?= $moodFilter === 'joy' ? 'selected' : '' ?>>Joy</option>
                <option value="sadness" <?= $moodFilter === 'sadness' ? 'selected' : '' ?>>Sadness</option>
                <option value="anger" <?= $moodFilter === 'anger' ? 'selected' : '' ?>>Anger</option>
                <option value="fear" <?= $moodFilter === 'fear' ? 'selected' : '' ?>>Fear</option>
                <option value="disgust" <?= $moodFilter === 'disgust' ? 'selected' : '' ?>>Disgust</option>
                
              </select>
            </div>
            <div class="col-md-3">
              <input type="month" class="form-control" name="month" value="<?= htmlspecialchars($monthFilter) ?>">
            </div>
            <div class="col-md-2">
              <a href="journal-history.php" class="btn btn-outline-primary w-100">Clear</a>
            </div>
          </div>
        </form>
      </div>
      
      <!-- Entries Container -->
      <div id="entries-container">
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif (empty($entries)): ?>
          <div class="empty-state text-center">
            <i class="fas fa-book-open empty-icon"></i>
            <p>No journal entries yet. Start writing to see your entries here!</p>
          </div>
        <?php else: ?>
          <?php foreach ($entries as $entry): 
            $moodClass = '';
            $moodIcon = '';
            $moodLabel = '';
            
            switch ($entry['mood']) {
                case 'happy':
                    $moodClass = 'joy';
                    $moodIcon = 'fa-smile';
                    $moodLabel = 'Happy';
                    break;
                case 'sad':
                    $moodClass = 'sadness';
                    $moodIcon = 'fa-sad-tear';
                    $moodLabel = 'Sad';
                    break;
                case 'angry':
                    $moodClass = 'anger';
                    $moodIcon = 'fa-angry';
                    $moodLabel = 'Angry';
                    break;
                case 'anxious':
                    $moodClass = 'fear';
                    $moodIcon = 'fa-surprise';
                    $moodLabel = 'Anxious';
                    break;
                case 'excited':
                    $moodClass = 'joy';
                    $moodIcon = 'fa-laugh-squint';
                    $moodLabel = 'Excited';
                    break;
                case 'neutral':
                    $moodClass = 'disgust';
                    $moodIcon = 'fa-meh';
                    $moodLabel = 'Neutral';
                    break;
            }
            ?>
            <div class="entry-card">
              <div class="entry-header">
                <h3 class="entry-title"><?= htmlspecialchars($entry['title']) ?></h3>
                <span class="entry-date"><?= date('F j, Y', strtotime($entry['entry_date'])) ?></span>
              </div>
              <?php if ($entry['mood']): ?>
                <div class="entry-moods">
                  <span class="mood-badge <?= $moodClass ?>">
                    <i class="fas <?= $moodIcon ?> me-1"></i> <?= $moodLabel ?>
                  </span>
                </div>
              <?php endif; ?>
              <p class="entry-content">
                <?= nl2br(htmlspecialchars(substr($entry['content'], 0, 200))) ?>
                <?= strlen($entry['content']) > 200 ? '...' : '' ?>
              </p>
              <div class="entry-actions">
                <a href="view-entry.php?id=<?= $entry['entry_id'] ?>" class="btn btn-outline-primary btn-sm">Read More</a>
                <a href="edit-entry.php?id=<?= $entry['entry_id'] ?>" class="btn btn-outline-primary btn-sm">Edit</a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
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
        
        // Filter functionality
        document.querySelectorAll('.form-control, .form-select').forEach(element => {
            element.addEventListener('change', function() {
                document.querySelector('form').submit();
            });
        });
    </script>
</body>
</html>