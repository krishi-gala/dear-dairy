<?php
session_start();
include 'config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed_moods = ['joy', 'sadness', 'anger', 'fear', 'disgust'];
    $mood = $_POST['mood'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $entry_date = date('Y-m-d');

    if (!in_array($mood, $allowed_moods)) {
        $message = "Invalid mood selected.";
    } else {
        $sql = "INSERT INTO mood_tracker (user_id, mood, notes, entry_date)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE mood = VALUES(mood), notes = VALUES(notes)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $user_id, $mood, $notes, $entry_date);

        if ($stmt->execute()) {
            $message = "Mood logged successfully!";
        } else {
            $message = "Error logging mood.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mood Tracker | Dear Diary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;500;600;700&family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Mood Tracker</h1>
    <?php if (!empty($message)) : ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" class="p-4 border rounded bg-light">
        <div class="mb-3">
            <label for="mood" class="form-label">Today's Mood</label>
            <select class="form-select" name="mood" required>
                <option value="">Select Mood</option>
                <option value="joy">Joy</option>
                <option value="sadness">Sadness</option>
                <option value="anger">Anger</option>
                <option value="fear">Fear</option>
                <option value="disgust">Disgust</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Notes (optional)</label>
            <textarea class="form-control" name="notes" rows="4"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Log Mood</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
