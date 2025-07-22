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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $mood = $_POST['mood'];
    
    if (!empty($title) && !empty($content)) {
        try {
            // Begin transaction for atomic updates
            $conn->beginTransaction();
            
            // 1. First update the diary entry (basic version without timestamps)
            $stmt = $conn->prepare("UPDATE diary_entries 
                                  SET title = ?, content = ?, mood = ? 
                                  WHERE entry_id = ? AND user_id = ?");
            $stmt->execute([$title, $content, $mood, $entry_id, $user_id]);
            
            // 2. Get the entry date from the existing entry data
            // Assuming your diary_entries has an entry_date column
            $entry_date = $entry['entry_date']; 
            
            // If you don't have entry_date, use this fallback:
            // $entry_date = date('Y-m-d'); // Today's date
            
            // 3. Update mood_tracker table
            $stmt = $conn->prepare("INSERT INTO mood_tracker (user_id, mood, entry_date) 
                                  VALUES (?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE mood = VALUES(mood)");
            $stmt->execute([$user_id, $mood, $entry_date]);
            
            // Commit both updates
            $conn->commit();
            
            // Redirect to view the updated entry
            $_SESSION['success_message'] = "Entry updated successfully!";
            header("Location: view-entry.php?id=$entry_id");
            exit();
            
        } catch (PDOException $e) {
            // Rollback if any error occurs
            $conn->rollBack();
            $error = "Error updating entry: " . $e->getMessage();
        }
    } else {
        $error = "Title and content cannot be empty";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Entry | Dear Diary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reuse your existing styles from journal-history.php */
        :root {
            --primary-color: #8b5fbf;
            --primary-light: #e9e1f5;
            --primary-dark: #6a4a8f;
            --secondary-color: #d4c4ed;
            --text-color: #333333;
            --text-light: #6c757d;
            --joy: #FFD166;
            --sadness: #5B7DB1;
            --anger: #EF476F;
            --fear: #af7ac5;
            --disgust: #7FB069;
        }
        
        body {
            font-family: 'Source Sans Pro', sans-serif;
            background-color: #fffef7;
            min-height: 100vh;
        }
        
        .edit-container {
            max-width: 800px;
            margin: 3rem auto;
            background-color: white;
            border-radius: 8px;
            padding: 2.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        /* Mood Selector - Matching new-entry.php style */
        .mood-selector {
            margin-bottom: 2rem;
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .mood-selector h3 {
            color: var(--primary-dark);
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
        
        /* Sticky Note - Matching new-entry.php style */
        .sticky-note {
            background-color: #fef9c3;
            border: 1px solid #fde047;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            font-family: 'Source Sans Pro', sans-serif;
            font-size: 1rem;
            padding: 20px;
            line-height: 1.6;
            min-height: 300px;
            width: 100%;
            border-radius: 10px;
            transition: all 0.5s ease;
        }
        
        .sticky-note.joy {
            background-color: rgba(255, 209, 102, 0.4);
            border-color: var(--joy);
        }
        
        .sticky-note.sadness {
            background-color: rgba(91, 125, 177, 0.4);
            border-color: var(--sadness);
        }
        
        .sticky-note.anger {
            background-color: rgba(239, 71, 111, 0.4);
            border-color: var(--anger);
        }
        
        .sticky-note.fear {
            background-color: rgb(215, 178, 230);
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
        
        .form-control, .form-select {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 0.8rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(139, 95, 191, 0.25);
        }
        
        textarea.form-control {
            min-height: 300px;
        }
        
        /* Buttons */
        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            padding: 0.7rem 1.5rem;
            border-radius: 10px;
        }
        
        .btn-submit:hover {
            background-color: var(--primary-dark);
            color: white;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .edit-container {
                padding: 1.5rem;
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
    </style>
</head>
<body>
    <!-- Navigation (same as journal-history.php) -->
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
        <div class="edit-container">
            <h1 class="mb-4">Edit Journal Entry</h1>
            
            <form method="POST">
                <!-- Mood Selector -->
                <div class="mood-selector">
                    <h3>How were you feeling?</h3>
                    <div class="mood-options">
                        <?php
                        $moods = [
                            'joy' => ['label' => 'Joy'],
                            'sadness' => ['label' => 'Sadness'],
                            'anger' => ['label' => 'Anger'],
                            'fear' => ['label' => 'Fear'],
                            'disgust' => ['label' => 'Disgust']
                        ];
                        
                        foreach ($moods as $key => $moodData): ?>
                            <div class="mood-option <?= $entry['mood'] === $key ? 'selected' : '' ?>" data-mood="<?= $key ?>">
                                <div class="mood-icon <?= $key ?>">
                                    <img src="images/<?= $key ?>.png" alt="<?= $moodData['label'] ?>" width="40" height="40">
                                </div>
                                <span><?= $moodData['label'] ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                    </div>
                    <input type="hidden" name="mood" id="selected-mood" value="<?= $entry['mood'] ?>">
                </div>
                
                <!-- Title -->
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($entry['title']) ?>" required>
                </div>
                
                <!-- Content -->
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea class="sticky-note <?= $entry['mood'] ? $entry['mood'] : '' ?>" id="content" name="content" required><?= htmlspecialchars($entry['content']) ?></textarea>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="journal-history.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mood selection functionality
        const moodOptions = document.querySelectorAll('.mood-option');
        const selectedMoodInput = document.getElementById('selected-mood');
        const stickyNote = document.getElementById('content');
        
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