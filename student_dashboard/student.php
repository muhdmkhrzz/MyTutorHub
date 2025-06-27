<?php
include 'connect.php';

// Insert form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name'])) {
  $name = mysqli_real_escape_string($conn, $_POST['name']);
  $sql = "INSERT INTO students (name, class_name, tutor, schedule, material_link, payment_status)
          VALUES ('$name', 'HCI - Unit I', 'Lily', '2025-06-25 08:00:00', '#', 'paid')";
  mysqli_query($conn, $sql);
}

// Fetch the latest student
$result = $conn->query("SELECT * FROM students ORDER BY id DESC LIMIT 1");
$student = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard</title>
  <link rel="stylesheet" href="student.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="parent">

    <!-- Left Sidebar -->
    <div class="logo"><img src="mytutorhub_logo.png" alt="MyTutorHub Logo"></div>
    <div class="navbar">
      <ul>
        <li><a href="#">Dashboard</a></li>
        <li><a href="#">My Bookings</a></li>
        <li><a href="#">Recorded Lessons</a></li>
      </ul>
    </div>
    <div class="logout"><a href="#">Logout</a></div>

    <!-- Search -->
    <div class="search">
      <input type="text" placeholder="Search...">
      <span><?php echo date("d F Y, l"); ?></span>
    </div>

    <!-- Welcome Box -->
    <div class="div10 card my-4 p-4 shadow-sm">
      <h2 class="card-title mb-3">Welcome back, <?php echo htmlspecialchars($student['name'] ?? 'Guest'); ?>!</h2>
      <p class="card-text">New Responsive Web Application Development classes are available.</p>
      <button class="btn btn-primary">Buy Lesson</button>
    </div>

    <!-- Alert -->
    <div class="alert alert-info mt-4" role="alert">
      <strong>Notice:</strong> Check out the new Responsive Web Application Development classes!
    </div>

    <!-- Class Info -->
    <div class="class">
      <div class="class-card">
        <h3><?php echo $student['class_name'] ?? 'N/A'; ?></h3>
        <p>40 students</p>
        <p><?php echo date("d F Y, H:i", strtotime($student['schedule'] ?? '')); ?> - 10:00</p>
      </div>
    </div>

    <!-- Lessons Table -->
    <div class="lesson">
      <table>
        <thead>
          <tr>
            <th>Class</th><th>Tutor</th><th>Starting</th><th>Material</th><th>Payment</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Google Meet</td>
            <td><?php echo $student['tutor'] ?? '-'; ?></td>
            <td><?php echo date("d M Y, H:i", strtotime($student['schedule'] ?? '')); ?></td>
            <td><a href="<?php echo $student['material_link'] ?? '#'; ?>">Download</a></td>
            <td><?php echo ($student['payment_status'] == 'paid') ? '✅ Done' : '❌ Not Paid'; ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Profile -->
    <div class="profile">
      <img src="your-profile.jpg" alt="Profile Photo">
      <h4><?php echo htmlspecialchars($student['name'] ?? 'Student'); ?></h4>
      <p>Student</p>
      <button>Profile</button>
    </div>

    <!-- Calendar -->
    <div class="calendar">
      <h4>Calendar</h4>
      <table id="calendar-table" class="table table-bordered">
        <thead>
          <tr><th colspan="7" id="calendar-month-year"></th></tr>
          <tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>
        </thead>
        <tbody id="calendar-body"></tbody>
      </table>
    </div>

    <!-- Reminders -->
    <div class="reminder">
      <h4>Reminders</h4>
      <ul>
        <li>HCI - Unit I: 25 Jun 2025</li>
        <li>Data Structured: 25 Jun 2025</li>
        <li>AI - Theory I: 26 Jun 2025</li>
      </ul>
    </div>

    <!-- Simple form to insert student name -->
    <div style="margin: 2rem;">
      <form action="" method="POST" class="form-inline">
        <input type="text" name="name" placeholder="Enter your name" class="form-control mr-2" required>
        <button type="submit" class="btn btn-success">Submit</button>
      </form>
    </div>
  </div>

  <script>
    function generateCalendar() {
      const today = new Date();
      const month = today.getMonth();
      const year = today.getFullYear();
      const firstDay = new Date(year, month, 1).getDay();
      const daysInMonth = new Date(year, month + 1, 0).getDate();

      const monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
      ];

      document.getElementById('calendar-month-year').textContent = `${monthNames[month]} ${year}`;

      const calendarBody = document.getElementById('calendar-body');
      calendarBody.innerHTML = "";

      let date = 1;
      for (let i = 0; i < 6; i++) {
        let row = document.createElement('tr');
        for (let j = 0; j < 7; j++) {
          let cell = document.createElement('td');
          if (i === 0 && j < firstDay) {
            cell.textContent = "";
          } else if (date > daysInMonth) {
            cell.textContent = "";
          } else {
            cell.textContent = date;
            if (
              date === today.getDate() &&
              month === today.getMonth() &&
              year === today.getFullYear()
            ) {
              cell.style.background = "#0d6efd";
              cell.style.color = "#fff";
              cell.style.borderRadius = "50%";
            }
            date++;
          }
          row.appendChild(cell);
        }
        calendarBody.appendChild(row);
        if (date > daysInMonth) break;
      }
    }
    generateCalendar();
  </script>
</body>
</html>
