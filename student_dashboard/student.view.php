<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="student.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <img src="mytutorhub_logo.png" class="logo" alt="Logo">
        <a href="student.php">Dashboard</a>
        <a href="#">My Bookings</a>
        <a href="#">Recorded Lessons</a>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="main-content">
        <div class="search-bar">
            <input type="text" placeholder="Search...">
            <span><?= date("d F Y, l") ?></span>
        </div>
        
        <div class="welcome-panel">
            <div class="text">
                <h2>Welcome back, <?= htmlspecialchars($student['stud_name'] ?? 'Student') ?>!</h2>
                <p>New classes are available. Learn more about HTML and CSS.</p>
                <button>Buy Lesson</button>
            </div>
            <img src="../img/profile.jpg" alt="Profile">
        </div>

        <?php if (!empty($classes)): ?>
            <h2>Classes</h2>
            <div class="classes">
                <?php foreach ($classes as $class): ?>
                    <div class="class-card">
                        <h3><?= htmlspecialchars($class['class_title']) ?></h3>
                        <p class="info"><?= date('d M Y', strtotime($class['class_date'])) ?></p>
                        <p class="info"><?= $class['class_starttime'] ?> - <?= $class['class_endtime'] ?></p>
                        <p class="info">Tutor: <?= htmlspecialchars($class['tutor_name']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-data-message">
                <p>You have no classes available.</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($lessons)): ?>
            <h2>Lessons</h2>
            <table class="lessons-table">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Tutor</th>
                        <th>Starting</th>
                        <th>Material</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lessons as $lesson): ?>
                        <tr>
                            <td><?= htmlspecialchars($lesson['class_title']) ?></td>
                            <td><?= htmlspecialchars($lesson['tutor_name']) ?></td>
                            <td>
                                <?= date('d M Y', strtotime($lesson['booking_date'])) ?>
                                <?= $lesson['class_starttime'] ?> - <?= $lesson['class_endtime'] ?>
                            </td>
                            <td><a href="#">Download</a></td>
                            <td class="status-done">Done</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data-message">
                <p>You have no lessons booked yet.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>