<?php
require_once 'config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: journal-history.php");
    exit();
}

$entry_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch the entry
$stmt = $conn->prepare("SELECT * FROM diary_entries WHERE entry_id = ? AND user_id = ?");
$stmt->execute([$entry_id, $user_id]);
$entry = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$entry) {
    header("Location: journal-history.php");
    exit();
}

// Get mood details
$moodClass = '';
$moodIcon = '';
$moodLabel = '';

switch ($entry['mood']) {
    case 'joy':
        $moodClass = 'joy';
        $moodIcon = 'fa-smile';
        $moodLabel = 'Joy';
        break;
    case 'sadness':
        $moodClass = 'sadness';
        $moodIcon = 'fa-sad-tear';
        $moodLabel = 'Sadness';
        break;
    case 'anger':
        $moodClass = 'anger';
        $moodIcon = 'fa-angry';
        $moodLabel = 'Anger';
        break;
    case 'fear':
        $moodClass = 'fear';
        $moodIcon = 'fa-surprise';
        $moodLabel = 'Fear';
        break;
    case 'disgust':
        $moodClass = 'disgust';
        $moodIcon = 'fa-grimace';
        $moodLabel = 'Disgust';
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Entry | Dear Diary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;500;600;700&family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #8b5fbf;
            --primary-light: #e9e1f5;
            --primary-dark: #6a4a8f;
            --secondary-color: #d4c4ed;
            --accent-color: #b388ff;
            --text-color: #333333;
            --text-light: #6c757d;
            --joy: #FFD166;
            --sadness: #5B7DB1;
            --anger: #EF476F;
            --fear: #af7ac5;
            --disgust: #7FB069;
            --paper-color: #fffef7;
            --border-radius: 12px;
            --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        body {
            font-family: 'Source Sans Pro', sans-serif;
            background-color: var(--paper-color);
            min-height: 100vh;
            color: var(--text-color);
            line-height: 1.8;
        }
        
        /* Entry Container */
        .entry-view-container {
            max-width: 800px;
            margin: 3rem auto;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2.5rem;
            border: 1px solid var(--primary-light);
        }
        
        .entry-header {
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--primary-light);
        }
        
        .entry-header h1 {
            font-family: 'Caveat', cursive;
            font-size: 2.5rem;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
        }
        
        .entry-date {
            color: var(--text-light);
            font-size: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* Mood Display */
        .mood-display {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .mood-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
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
            background-color: rgba(175, 122, 197, 0.2);
            color: #6e3c7e;
        }
        
        .mood-badge.disgust {
            background-color: rgba(127, 176, 105, 0.2);
            color: #4e6d3d;
        }
        
        /* Entry Content */
        .entry-content {
            font-size: 1.1rem;
            line-height: 0.8;
            white-space: pre-line;
            padding: 1.5rem;
            background-color: white;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary-color);
        }
        
        /* Mood-themed content background */
        .entry-content.joy {
            background-color: rgba(255, 209, 102, 0.1);
            border-left-color: var(--joy);
        }
        
        .entry-content.sadness {
            background-color: rgba(91, 125, 177, 0.1);
            border-left-color: var(--sadness);
        }
        
        .entry-content.anger {
            background-color: rgba(239, 71, 111, 0.1);
            border-left-color: var(--anger);
        }
        
        .entry-content.fear {
            background-color: rgba(175, 122, 197, 0.1);
            border-left-color: var(--fear);
        }
        
        .entry-content.disgust {
            background-color: rgba(127, 176, 105, 0.1);
            border-left-color: var(--disgust);
        }
        
        /* Action Buttons */
        .entry-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid var(--primary-light);
        }
        
        .btn {
            padding: 0.7rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: all 0.3s ease;
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
            .entry-view-container {
                padding: 1.5rem;
                margin: 1.5rem auto;
            }
            
            .entry-header h1 {
                font-size: 2rem;
            }
            
            .entry-actions {
                flex-direction: column;
                gap: 0.8rem;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation (same as other pages) -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book-open"></i> Dear Diary
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="journal-history.php">Journal History</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="entry-view-container">
            <div class="entry-header">
                <h1><?= htmlspecialchars($entry['title']) ?></h1>
                <div class="entry-date">
                    <i class="far fa-calendar-alt"></i>
                    <?= date('F j, Y \a\t g:i a', strtotime($entry['entry_date'])) ?>
                </div>
                
                <?php if ($entry['mood']): ?>
                    <div class="mood-display">
                        <span class="mood-badge <?= $moodClass ?>">
                            <i class="fas <?= $moodIcon ?> me-2"></i>
                            <?= $moodLabel ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="entry-content <?= $entry['mood'] ? $entry['mood'] : '' ?>">
                <?= nl2br(htmlspecialchars($entry['content'])) ?>
            </div>
            
            <div class="entry-actions">
                <a href="edit-entry.php?id=<?= $entry['entry_id'] ?>" class="btn btn-outline-primary">
                    <i class="fas fa-edit me-2"></i>Edit Entry
                </a>
                <a href="journal-history.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to History
                </a>
            </div>
        </div>
    </div>

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
    </script>
</body>
</html>