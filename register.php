<?php
// Initialize variables and error array
$errors = [];
$success = false;

// Only process the form if it's submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form input with proper validation
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Only proceed if no validation errors
    if (empty($errors)) {
        try {
            // Connect to the database (use your config file instead of hardcoding)
            require_once __DIR__ . '/config/config.php';
            
            // Hash the password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Prepare and execute the query
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $password_hash]);

            // Set success flag
            $success = true;
            
            // Redirect to login page after successful registration
            header("Location: login.php?registered=1");
            exit();
        } catch (PDOException $e) {
            // If email or username is already taken (due to UNIQUE constraint)
            if ($e->getCode() == 23000) {
                $errors[] = "Username or email already exists";
            } else {
                $errors[] = "Registration error: " . $e->getMessage();
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dear Diary - Register</title>
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
        --light-color: #f8f9fa;
        --dark-color: #343a40;
        --text-color: #333333;
        --text-light: #6c757d;
        --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        --transition: all 0.3s ease;
        --paper-color: #fffef7; 
        }
        
        body {
            background-color: var(--paper-color);
            font-family: 'Open Sans', sans-serif;
            color: var(--text-color);
            line-height: 1.7;
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        
        /* Navbar Styles - Matching login.html */
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
        
        /* Main Content Container - Centered Register */
        .register-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 56px);
            padding: 2rem;
        }
        
        /* Register Card Styles */
        .auth-card {
            background-color: var(--primary-light);
            border-radius: 15px;
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 500px;
            padding: 2.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(5px);
        }
        
        .auth-card h2 {
            color: var(--primary-dark);
            font-weight: 600;
            margin-bottom: 2rem;
            text-align: center;
            font-family: 'Caveat', cursive;
            font-size: 2.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            transition: var(--transition);
            background-color: rgba(255, 255, 255, 0.8);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(236, 137, 142, 0.25);
            background-color: white;
        }
        
        .btn {
            padding: 0.7rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: var(--transition);
            border: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(236, 137, 142, 0.3);
        }
        
        .text-center a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .text-center a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .auth-card {
                padding: 1.5rem;
            }
            
            .navbar-brand {
                font-size: 1.8rem;
            }
        }
        
        @media (max-width: 576px) {
            .auth-card {
                padding: 1.5rem;
            }
            
            .auth-card h2 {
                font-size: 2rem;
            }
            
            .register-container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation matching index.php -->
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
                        <a class="nav-link" href="goals.php">Goals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Centered Register Content -->
    <div class="register-container">
        <div class="auth-card">
            <div class="card-body p-4">
                <h2 class="text-center mb-4">Register</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-0"><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form id="register-form" method="POST">
                    <div class="mb-3">
                        <label for="register-username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="register-username" name="username" required
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="register-email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="register-email" name="email" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="register-password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="register-password" name="password" required>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                    <div class="mb-3">
                        <label for="register-confirm-password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="register-confirm-password" name="confirm_password" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                    <p class="text-center mt-3">
                        Already have an account? <a href="login.php">Login</a>
                    </p>
                </form>
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
        // Password match validation
        document.getElementById('register-form').addEventListener('submit', function(e) {
            const password = document.getElementById('register-password').value;
            const confirmPassword = document.getElementById('register-confirm-password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
            }
        });
    </script>
</body>
</html>