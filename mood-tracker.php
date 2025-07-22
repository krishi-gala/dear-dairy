<?php
include 'config/config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle month navigation
$currentMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$startDate = $currentMonth . '-01';

// Calculate previous and next months
$prevMonth = date('Y-m', strtotime($currentMonth . ' -1 month'));
$nextMonth = date('Y-m', strtotime($currentMonth . ' +1 month'));

// Get mood entries for the current month
$sql = "SELECT entry_date, mood FROM mood_tracker 
        WHERE user_id = ? AND 
        entry_date >= ? AND 
        entry_date <= LAST_DAY(?)";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id, $startDate, $startDate]);
$moodEntries = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $moodEntries[$row['entry_date']] = $row['mood'];
}

// Handle mood submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mood = $_POST['mood'];
    $notes = $_POST['notes'] ?? '';
    $entry_date = date('Y-m-d'); // today

    $sql = "INSERT INTO mood_tracker (user_id, mood, notes, entry_date) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE mood = VALUES(mood), notes = VALUES(notes)";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(1, $user_id);
    $stmt->bindParam(2, $mood);
    $stmt->bindParam(3, $notes);
    $stmt->bindParam(4, $entry_date);
    
    if ($stmt->execute()) {
        $message = "Mood logged successfully!";
        // Refresh the page to show the new mood
        header("Location: mood-tracker.php?month=$currentMonth");
        exit();
    } else {
        $message = "Error logging mood.";
    }
}

// Calculate calendar variables
if (strtotime($startDate) !== false) {
    $firstDay = date('N', strtotime($startDate)); // 1 (Mon) to 7 (Sun)
    $monthName = date('F Y', strtotime($startDate)); // e.g., "April 2025"
    $daysInMonth = date('t', strtotime($startDate));
} else {
    // fallback values
    $firstDay = 1;
    $monthName = 'Invalid Date';
    $daysInMonth = 30;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mood Tracker | Dear Diary</title>
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
        
        /* Mood Tracker Container */
        .mood-tracker-container {
            max-width: 900px;
            margin: 3rem auto;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2.5rem;
            border: 1px solid var(--primary-light);
        }
        
        .mood-tracker-header {
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
        }
        
        .mood-tracker-header h1 {
            font-family: 'Caveat', cursive;
            font-size: 2.5rem;
            color: var(--primary-dark);
            margin-bottom: 1rem;
        }
        
        .mood-tracker-header:after {
            content: '';
            display: block;
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            margin: 1rem auto;
        }
        
        /* Month Controls */
        .month-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 1rem;
        }
        
        .month-title {
            font-family: 'Caveat', cursive;
            font-size: 1.8rem;
            color: var(--primary-dark);
            margin: 0;
        }
        
        /* Mood Grid */
        .mood-grid-container {
            margin: 2rem 0;
        }
        
        .mood-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }
        
        .mood-day {
            aspect-ratio: 1/1;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            background-color: var(--no-entry);
            position: relative;
            overflow: hidden;
        }
        
        .mood-day:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .mood-day.day-header {
            background-color: transparent;
            box-shadow: none;
            font-weight: 600;
            color: var(--primary-dark);
            cursor: default;
            font-size: 1rem;
        }
        
        .mood-day.day-header:hover {
            transform: none;
            box-shadow: none;
        }
        
        .mood-day.outside-month {
            opacity: 0.3;
            pointer-events: none;
        }
        
        /* Mood Colors */
        .mood-day.joy {
            background-color: var(--joy);
        }
        
        .mood-day.sadness {
            background-color: var(--sadness);
            color: white;
        }
        
        .mood-day.anger {
            background-color: var(--anger);
            color: white;
        }
        
        .mood-day.fear {
            background-color: var(--fear);
            color: white;
        }
        
        .mood-day.disgust {
            background-color: var(--disgust);
            color: white;
        }
        
        /* Mood Icon */
        .mood-icon {
            font-size: 1.2rem;
            margin-bottom: 0.2rem;
            display: none;
            display: block;
            margin: 4px auto 0;
        }
        
        .mood-day.joy .mood-icon,
        .mood-day.sadness .mood-icon,
        .mood-day.anger .mood-icon,
        .mood-day.fear .mood-icon,
        .mood-day.disgust .mood-icon {
            display: block;
        }
        
        /* Mood Legend */
        .mood-legend {
            background-color: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            margin-top: 2rem;
        }
        
        .mood-legend h4 {
            color: var(--primary-dark);
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 600;
        }
        
        .mood-legend-items {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        
        .mood-legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .mood-color {
            width: 24px;
            height: 24px;
            border-radius: 6px;
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
        
        .mood-color.no-entry {
            background-color: var(--no-entry);
            border: 1px solid #ddd;
        }
        
        /* Buttons */
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
            border-width: 2px;
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .mood-tracker-container {
                padding: 1.5rem;
                margin: 1.5rem auto;
            }
            
            .mood-tracker-header h1 {
                font-size: 2rem;
            }
            
            .month-title {
                font-size: 1.5rem;
            }
            
            .mood-day {
                font-size: 0.8rem;
            }
            
            .mood-icon {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .mood-grid {
                gap: 4px;
            }
            
            .mood-day {
                font-size: 0.7rem;
                border-radius: 4px;
            }
            
            .mood-legend-items {
                gap: 1rem;
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
                            <li><a class="dropdown-item" href="journal-history.php">History</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="mood-tracker.php">Moods</a>
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

    <!-- Mood Tracker Content -->
  <div class="container">
    <div class="mood-tracker-container">
      <div class="mood-tracker-header">
        <h1>Mood Tracker</h1>
        <p>Visualize your emotional journey</p>
      </div>
      
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      
      <!-- Month Controls -->
      <div class="month-controls">
        <a href="mood-tracker.php?month=<?= $prevMonth ?>" class="btn btn-outline-primary" id="prev-month-btn">
          <i class="fas fa-chevron-left"></i> Previous
        </a>
        <h3 id="current-month" class="month-title"><?= $monthName ?></h3>
        <a href="mood-tracker.php?month=<?= $nextMonth ?>" class="btn btn-outline-primary" id="next-month-btn">
          Next <i class="fas fa-chevron-right"></i>
        </a>
      </div>
      
      <!-- Mood Grid -->
      <div class="mood-grid-container">
        <div class="mood-grid" id="mood-grid">
          <!-- Day headers -->
          <div class="mood-day day-header">Su</div>
          <div class="mood-day day-header">Mo</div>
          <div class="mood-day day-header">Tu</div>
          <div class="mood-day day-header">We</div>
          <div class="mood-day day-header">Th</div>
          <div class="mood-day day-header">Fr</div>
          <div class="mood-day day-header">Sa</div>
          
          <?php
        // Generate calendar
        $currentDay = 1;
        
        // Adjust for Sunday as first day (change 7 to 0)
        $firstDay = $firstDay % 7;
        
        // Previous month days
        for ($i = 0; $i < $firstDay; $i++) {
            echo '<div class="mood-day outside-month"></div>';
        }
        
        // Current month days
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $formattedDay = str_pad($day, 2, '0', STR_PAD_LEFT);
            $dateString = $currentMonth . '-' . $formattedDay;
            $date = date('Y-m-d', strtotime($dateString));
            
            $mood = $moodEntries[$date] ?? null;
            
            $moodClass = '';
            $moodIcon = '';
            
            if ($mood) {
                switch ($mood) {
                    case 'joy':
                        $moodClass = 'joy';
                        $moodIcon = 'fa-smile';
                        break;
                    case 'sadness':
                        $moodClass = 'sadness';
                        $moodIcon = 'fa-sad-tear';
                        break;
                    case 'anger':
                        $moodClass = 'anger';
                        $moodIcon = 'fa-angry';
                        break;
                    case 'fear':
                        $moodClass = 'fear';
                        $moodIcon = 'fa-surprise';
                        break;
                    case 'disgust':
                        $moodClass = 'disgust';
                        $moodIcon = 'fa-meh';
                        break;
                }
            }
            
            echo '<div class="mood-day ' . $moodClass . '" data-date="' . $date . '">';
            echo $day;
            if ($mood) {
                $imagePath = "images/$mood.png";
                echo "<img src='$imagePath' alt='$mood' class='mood-icon' style='width:50px; height:50px;'>";
            }
            
            echo '</div>';
            
            // Break to new row after Saturday
            if (($firstDay + $day) % 7 == 0 && $day != $daysInMonth) {
                echo '</div><div class="mood-grid">';
            }
        }
        
        // Next month days
        $daysDisplayed = $firstDay + $daysInMonth;
        $remainingCells = (7 - ($daysDisplayed % 7)) % 7;
        
        for ($i = 1; $i <= $remainingCells; $i++) {
            echo '<div class="mood-day outside-month">' . $i . '</div>';
        }
        ?>
        </div>
      </div>
      
      <!-- Mood Legend -->
      <div class="mood-legend">
        <h4>Mood Legend</h4>
        <div class="mood-legend-items">
          <div class="mood-legend-item">
            <div class="mood-color joy"></div>
            <span>Joy</span>
          </div>
          <div class="mood-legend-item">
            <div class="mood-color sadness"></div>
            <span>Sadness</span>
          </div>
          <div class="mood-legend-item">
            <div class="mood-color anger"></div>
            <span>Anger</span>
          </div>
          <div class="mood-legend-item">
            <div class="mood-color fear"></div>
            <span>Fear</span>
          </div>
          <div class="mood-legend-item">
            <div class="mood-color disgust"></div>
            <span>Disgust</span>
          </div>
          <div class="mood-legend-item">
            <div class="mood-color no-entry"></div>
            <span>No Entry</span>
          </div>
        </div>
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
        
        // Mood day click handler
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.mood-day:not(.day-header):not(.outside-month)').forEach(day => {
                day.addEventListener('click', function() {
                    const date = this.getAttribute('data-date');
                    const hasEntry = this.classList.contains('joy') || 
                                    this.classList.contains('sadness') || 
                                    this.classList.contains('anger') || 
                                    this.classList.contains('fear') || 
                                    this.classList.contains('disgust');
                    
                    if (hasEntry) {
                        window.location.href = 'journal-history.php?month=' + date.substr(0, 7) + '&search=' + date;
                    } else {
                        window.location.href = 'new-entry.php?date=' + date;
                    }
                });
            });
        });
    </script>
</body>
</html>