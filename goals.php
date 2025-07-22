<?php
// goals.php
require_once 'config/config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_goal'])) {
        // Add new goal
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $target_date = $_POST['target_date'] ?? null;
        $progress = $_POST['progress'] ?? 0;
        $is_completed = ($progress >= 100) ? 1 : 0;
        
        try {
            $stmt = $conn->prepare("INSERT INTO goals (user_id, title, description, target_date, progress, is_completed) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([getUserId(), $title, $description, $target_date, $progress, $is_completed]);
        } catch (PDOException $e) {
            $error = "Error adding goal: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_progress'])) {
        // Update goal progress
        $goal_id = $_POST['goal_id'] ?? null;
        $progress = $_POST['progress'] ?? 0;
        
        try {
            $stmt = $conn->prepare("UPDATE goals SET progress = ?, is_completed = ? WHERE goal_id = ? AND user_id = ?");
            $is_completed = ($progress >= 100) ? 1 : 0;
            $stmt->execute([$progress, $is_completed, $goal_id, getUserId()]);
        } catch (PDOException $e) {
            $error = "Error updating goal: " . $e->getMessage();
        }
    }
}

// Get goals
try {
    // Active goals
    $stmt = $conn->prepare("SELECT * FROM goals WHERE user_id = ? AND is_completed = 0 ORDER BY target_date ASC");
    $stmt->execute([getUserId()]);
    $activeGoals = $stmt->fetchAll();
    
    // Completed goals
    $stmt = $conn->prepare("SELECT * FROM goals WHERE user_id = ? AND is_completed = 1 ORDER BY target_date DESC");
    $stmt->execute([getUserId()]);
    $completedGoals = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error loading goals: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goals | Dear Diary</title>
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
        }
        
        body {
            font-family: 'Source Sans Pro', sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            background-color: var(--paper-color);
            min-height: 100vh;
        }
        
        /* Navbar - Matching other pages */
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
        
        /* Goals Container */
        .goals-container {
            max-width: 800px;
            margin: 3rem auto;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2.5rem;
            border: 1px solid var(--primary-light);
        }
        
        .goals-header {
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
        }
        
        .goals-header h1 {
            font-family: 'Caveat', cursive;
            font-size: 2.5rem;
            color: var(--primary-dark);
            margin-bottom: 1rem;
        }
        
        .goal-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--box-shadow);
            border-left: 4px solid var(--primary-color);
            transition: var(--transition);
        }
        
        .goal-card.completed {
            border-left-color: var(--success-color);
            opacity: 0.8;
        }
        
        .goal-title {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
        }
        
        .goal-description {
            color: var(--text-light);
            margin-bottom: 1rem;
        }
        
        .goal-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        .goal-date {
            color: var(--text-light);
        }
        
        .goal-progress-container {
            margin-top: 1rem;
        }
        
        .progress {
            height: 10px;
            border-radius: 5px;
            background-color: var(--light-color);
        }
        
        .progress-bar {
            background-color: var(--primary-color);
            transition: width 0.6s ease;
        }
        
        .goal-actions {
            display: flex;
            gap: 0.8rem;
            margin-top: 1rem;
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
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        
        /* Empty State */
        .empty-state {
            background-color: white;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }
        
        .empty-icon {
            font-size: 2.5rem;
            color: var(--primary-light);
            margin-bottom: 1rem;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .goals-container {
                padding: 1.5rem;
                margin: 1.5rem auto;
            }
            
            .goals-header h1 {
                font-size: 2rem;
            }
            
            .goal-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
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
                            <li><a class="dropdown-item" href="new-entry.php">New Entry</a></li>
                            <li><a class="dropdown-item" href="journal-history.php">History</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mood-tracker.php">Moods</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="goals.php">Goals</a>
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

    <!-- Goals Content -->
  <div class="container">
    <div class="goals-container">
      <div class="goals-header">
        <h1>Personal Goals</h1>
        <p>Track your progress and achievements</p>
      </div>
      
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      
      <!-- Goal Form -->
      <div class="goal-form-container">
        <h3>Create New Goal</h3>
        <form method="POST" id="goal-form">
          <input type="hidden" name="add_goal" value="1">
          <div class="mb-3">
            <label for="goal-title" class="form-label">Goal Title</label>
            <input type="text" class="form-control" id="goal-title" name="title" placeholder="What do you want to achieve?" required>
          </div>
          <div class="mb-3">
            <label for="goal-description" class="form-label">Description</label>
            <textarea class="form-control" id="goal-description" name="description" rows="2" placeholder="Add some details about your goal"></textarea>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="goal-target-date" class="form-label">Target Date</label>
              <input type="date" class="form-control" id="goal-target-date" name="target_date">
            </div>
            <div class="col-md-6">
              <label for="goal-progress" class="form-label">Initial Progress (%)</label>
              <input type="number" class="form-control" id="goal-progress" name="progress" min="0" max="100" value="0">
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Add Goal</button>
        </form>
      </div>
      
      <!-- Goals Lists -->
      <div class="goals-lists">
        <h3 class="mb-3">Active Goals</h3>
        <div id="active-goals-list" class="mb-4">
          <?php if (empty($activeGoals)): ?>
            <div class="empty-state text-center">
              <i class="fas fa-clipboard-list empty-icon"></i>
              <p>You don't have any active goals yet. Create one to get started!</p>
            </div>
          <?php else: ?>
            <?php foreach ($activeGoals as $goal): ?>
              <div class="goal-card">
                <h4 class="goal-title"><?= htmlspecialchars($goal['title']) ?></h4>
                <?php if (!empty($goal['description'])): ?>
                  <p class="goal-description"><?= htmlspecialchars($goal['description']) ?></p>
                <?php endif; ?>
                <div class="goal-meta">
                  <?php if ($goal['target_date']): ?>
                    <span class="goal-date">Target: <?= date('F j, Y', strtotime($goal['target_date'])) ?></span>
                  <?php endif; ?>
                  <span class="goal-progress-text"><?= $goal['progress'] ?>% complete</span>
                </div>
                <div class="goal-progress-container">
                  <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: <?= $goal['progress'] ?>%" 
                         aria-valuenow="<?= $goal['progress'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </div>
                <div class="goal-actions">
                  <button class="btn btn-outline-primary btn-sm update-progress-btn" 
                          data-goal-id="<?= $goal['goal_id'] ?>">Update Progress</button>
                  <form method="POST" style="display: inline;">
                    <input type="hidden" name="update_progress" value="1">
                    <input type="hidden" name="goal_id" value="<?= $goal['goal_id'] ?>">
                    <input type="hidden" name="progress" value="100">
                    <button type="submit" class="btn btn-success btn-sm">Mark Complete</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        
        <h3 class="mb-3">Completed Goals</h3>
        <div id="completed-goals-list">
          <?php if (empty($completedGoals)): ?>
            <div class="empty-state text-center">
              <i class="fas fa-clipboard-check empty-icon"></i>
              <p>No completed goals yet. Keep working toward your active goals!</p>
            </div>
          <?php else: ?>
            <?php foreach ($completedGoals as $goal): ?>
              <div class="goal-card completed">
                <h4 class="goal-title"><?= htmlspecialchars($goal['title']) ?></h4>
                <?php if (!empty($goal['description'])): ?>
                  <p class="goal-description"><?= htmlspecialchars($goal['description']) ?></p>
                <?php endif; ?>
                <div class="goal-meta">
                  <span class="goal-date">Completed: <?= date('F j, Y', strtotime($goal['completed_at'] ?? $goal['target_date'])) ?></span>
                  <span class="goal-progress-text">100% complete</span>
                </div>
                <div class="goal-progress-container">
                  <div class="progress">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%" 
                         aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Update Progress Modal -->
  <div class="modal fade" id="update-progress-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Update Goal Progress</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" id="update-progress-form">
            <input type="hidden" name="update_progress" value="1">
            <input type="hidden" name="goal_id" id="modal-goal-id">
            <div class="mb-3">
              <label for="update-progress" class="form-label">Current Progress (%)</label>
              <input type="number" class="form-control" id="update-progress" name="progress" min="0" max="100" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </form>
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
        
        document.addEventListener('DOMContentLoaded', function() {
            // Update progress buttons
            document.querySelectorAll('.update-progress-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const goalId = this.getAttribute('data-goal-id');
                    document.getElementById('modal-goal-id').value = goalId;
                    const modal = new bootstrap.Modal(document.getElementById('update-progress-modal'));
                    modal.show();
                });
            });
        });
    </script>
</body>
</html>