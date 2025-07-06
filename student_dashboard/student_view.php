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
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <nav class="sidebar" id="sidebar" aria-label="Main navigation">
        <!-- Using a placeholder image as mytutorhub_logo.png is not provided -->
        <img src="https://placehold.co/180x60/c9c1f2/7c4fe0?text=MyTutorHub" class="logo" alt="MyTutorHub Logo" loading="lazy">
        <a href="#main-content" class="active"><i class="fas fa-home"></i> Dashboard</a>
        <a href="#classes-section"><i class="fas fa-calendar-check"></i> My Bookings</a>
        <a href="#available-classes-section"><i class="fas fa-book-open"></i> Available Classes</a> <!-- Changed text -->
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>

    <div class="main-content" id="main-content">
        <div class="search-bar">
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="class-search" placeholder="Search for classes..." aria-label="Search classes">
            </div>
            <span id="current-date" aria-live="polite"></span>
        </div>

        <div class="welcome-panel">
            <div class="text">
                <!-- PHP will inject student name here -->
                <h2>Welcome back, <span id="stud_name"><?php echo htmlspecialchars($student['stud_name'] ?? 'Student'); ?></span>!</h2>
                <p>New classes are available. Learn more about HTML and CSS.</p>
                <!-- This button's functionality is not directly tied to booking a specific class here,
                     it's more of a general call to action. The actual booking happens from the table below. -->
                <button id="buy-lesson-btn" class="primary-btn">Buy Lesson</button>
            </div>
        </div>

        <!-- Buy Lesson Popup (general purpose, not directly used for class booking now) -->
        <div id="buy-lesson-popup" class="popup-overlay" style="display: none;">
            <div class="popup-content">
            <h3>Buy Lesson</h3>
            <p>Are you sure you want to buy this lesson?</p>
            <div class="lesson-details">
                <p><strong>Lesson:</strong> <span class="class_title">HTML & CSS Basics</span></p>
                <p><strong>Price:</strong> <span class="class_fee">RM30</span></p>
                <p><strong>Tutor:</strong> <span class="class_tutor">Jane Smith</span></p>
                <p><strong>Date:</strong> <span class="class_date">01 Jan 2023</span></p>
            </div>
            <div class="popup-actions">
                <button id="confirm-buy-btn" class="primary-btn">Confirm</button>
                <button id="close-buy-popup-btn" class="secondary-btn">Cancel</button>
            </div>
            </div>
        </div>

        <!-- My Bookings Section (formerly Classes Section) -->
        <section class="classes-section" id="classes-section">
            <div class="section-header">
                <h2>My Bookings</h2> <!-- Changed text -->
                <a href="view-all-bookings.php" class="view-all-btn" id="view-all-classes" aria-expanded="false">
                    View All Bookings
                </a>
            </div>
            
            <div class="classes-grid" id="booked-classes-container">
                <?php if (!empty($booked_classes)): ?>
                    <?php foreach ($booked_classes as $class): ?>
                        <article class="class-card">
                            <h3 class="class_title"><?php echo htmlspecialchars($class['class_title']); ?></h3>
                            <div class="class_description">
                                <p><i class="far fa-calendar-alt"></i> <span class="class_date"><?php echo htmlspecialchars(date('d M Y', strtotime($class['booking_date']))); ?></span></p>
                                <p><i class="far fa-clock"></i> <span class="class_starttime"><?php echo htmlspecialchars(date('H:i', strtotime($class['class_starttime']))) . ' - ' . htmlspecialchars(date('H:i', strtotime($class['class_endtime']))); ?></span></p>
                                <p><i class="fas fa-chalkboard-teacher"></i> Tutor: <span class="tutor_name"><?php echo htmlspecialchars($class['tutor_name']); ?></span></p>
                                <button class="review-btn" onclick="openRatingPopup(
                                    '<?php echo htmlspecialchars($class['class_title']); ?>',
                                    '<?php echo htmlspecialchars($class['tutor_name']); ?>',
                                    <?php echo htmlspecialchars($class['class_id']); ?>,
                                    <?php echo htmlspecialchars($stud_id); ?>
                                )">Rate Class</button>
                                <?php if (isset($class['tutor_rating']) && $class['tutor_rating'] !== null): ?>
                                    <p class="tutor-rating-display">Your Tutor Rating: <?php echo htmlspecialchars($class['tutor_rating']); ?> <i class="fas fa-star"></i></p>
                                <?php endif; ?>
                                <?php if (isset($class['class_rating']) && $class['class_rating'] !== null): ?>
                                    <p class="class-rating-display">Your Class Rating: <?php echo htmlspecialchars($class['class_rating']); ?> <i class="fas fa-star"></i></p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No booked classes found.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Available Classes Section (formerly Lessons Section) -->
        <section class="lessons-section" id="available-classes-section"> <!-- Changed ID -->
            <div class="section-header">
                <h2>Available Classes</h2> <!-- Changed text -->
                <a href="view-all-available-classes.php" class="view-all-btn" id="view-all-lessons" aria-expanded="false">
                    View All Classes
                </a>
            </div>
            
            <div class="table-container">
                <table class="lessons-table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Tutor</th>
                            <th>Tutor Rating</th> <!-- New Column -->
                            <th>Date & Time</th>
                            <th>Fee</th>
                            <th>Capacity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="available-classes-container"> <!-- Changed ID -->
                        <?php if (!empty($available_classes)): ?>
                            <?php foreach ($available_classes as $class): ?>
                                <tr>
                                    <td class="lesson-title"><?php echo htmlspecialchars($class['class_title']); ?></td>
                                    <td class="lesson-tutor"><?php echo htmlspecialchars($class['tutor_name']); ?></td>
                                    <td class="tutor-avg-rating">
                                        <?php if ($class['tutor_avg_rating'] !== null): ?>
                                            <?php echo number_format($class['tutor_avg_rating'], 1); ?> <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td class="lesson-datetime">
                                        <span class="lesson-date"><?php echo htmlspecialchars(date('d M Y', strtotime($class['class_date']))); ?></span>
                                        <span class="lesson-time"><?php echo htmlspecialchars(date('H:i', strtotime($class['class_starttime']))) . ' - ' . htmlspecialchars(date('H:i', strtotime($class['class_endtime']))); ?></span>
                                    </td>
                                    <td class="class_fee">RM<?php echo htmlspecialchars(number_format($class['class_fee'], 2)); ?></td>
                                    <td class="class_capacity"><?php echo htmlspecialchars($class['class_capacity']); ?></td>
                                    <td>
                                        <button class="book-btn" onclick="openBookPopup(
                                            '<?php echo htmlspecialchars($class['class_title']); ?>',
                                            '<?php echo htmlspecialchars($class['tutor_name']); ?>',
                                            '<?php echo htmlspecialchars(date('d M Y', strtotime($class['class_date']))); ?>',
                                            '<?php echo htmlspecialchars(date('H:i', strtotime($class['class_starttime']))) . '-' . htmlspecialchars(date('H:i', strtotime($class['class_endtime']))); ?>',
                                            '<?php echo htmlspecialchars(number_format($class['class_fee'], 2)); ?>',
                                            <?php echo htmlspecialchars($class['class_id']); ?>
                                        )">Book Now</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7">No available classes found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Book Now Popup -->
    <div class="popup-overlay" id="book-popup" style="display: none;">
        <div class="popup-content">
            <h3>Book Class</h3>
            <p>Are you sure you want to book this class?</p>
            <div class="lesson-details">
                <p><strong>Class:</strong> <span id="book-class-title"></span></p>
                <p><strong>Tutor:</strong> <span id="book-class-tutor"></span></p>
                <p><strong>Date:</strong> <span id="book-class-date"></span></p>
                <p><strong>Time:</strong> <span id="book-class-time"></span></p>
                <p><strong>Fee:</strong> <span id="book-class-fee"></span></p>
                <input type="hidden" id="book-class-id"> <!-- Hidden input for class_id -->
            </div>
            <div class="popup-actions">
                <button class="primary-btn" onclick="confirmBookPopup()">Confirm</button>
                <button class="secondary-btn" onclick="closeBookPopup()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Rating Popup -->
    <div class="popup-overlay" id="rating-popup" style="display: none;">
        <div class="popup-content">
            <h3>Rate This Class</h3>
            <p><strong>Class:</strong> <span id="rating-class-title"></span></p>
            <p><strong>Tutor:</strong> <span id="rating-tutor-name"></span></p>
            <div class="star-rating" id="star-rating">
                <span class="star" data-value="1">&#9733;</span>
                <span class="star" data-value="2">&#9733;</span>
                <span class="star" data-value="3">&#9733;</span>
                <span class="star" data-value="4">&#9733;</span>
                <span class="star" data-value="5">&#9733;</span>
            </div>
            <input type="hidden" id="rating-class-id">
            <input type="hidden" id="rating-stud-id">
            <div class="popup-actions">
                <button class="primary-btn" id="submit-rating-btn">Submit</button>
                <button class="secondary-btn" id="close-rating-popup-btn">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Notification Element -->
    <div id="notification" class="notification" aria-live="polite"></div>

    <script>
        // Global variables for popups
        let currentBookingClassId = null;
        let selectedRating = 0;

        // --- General Popup Functions ---
        function showNotification(message, autoHide = true) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.classList.add('show');
            if (autoHide) {
                setTimeout(() => {
                    notification.classList.remove('show');
                }, 3000);
            }
        }

        // --- Mobile Menu Toggle ---
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('main-content').classList.toggle('active');
        });

        // --- Current Date Display ---
        document.addEventListener('DOMContentLoaded', function() {
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', dateOptions);
        });

        // --- Buy Lesson Popup (General) ---
        const buyLessonBtn = document.getElementById('buy-lesson-btn');
        const buyLessonPopup = document.getElementById('buy-lesson-popup');
        const closeBuyPopupBtn = document.getElementById('close-buy-popup-btn');
        const confirmBuyBtn = document.getElementById('confirm-buy-btn');

        if (buyLessonBtn) {
            buyLessonBtn.addEventListener('click', () => {
                buyLessonPopup.style.display = 'flex';
            });
        }

        if (closeBuyPopupBtn) {
            closeBuyPopupBtn.addEventListener('click', () => {
                buyLessonPopup.style.display = 'none';
            });
        }

        if (confirmBuyBtn) {
            confirmBuyBtn.addEventListener('click', () => {
                buyLessonPopup.style.display = 'none';
                showNotification('Lesson purchased successfully!', true);
            });
        }

        // Optional: close buy lesson popup when clicking outside content
        if (buyLessonPopup) {
            buyLessonPopup.addEventListener('click', (e) => {
                if (e.target === buyLessonPopup) {
                    buyLessonPopup.style.display = 'none';
                }
            });
        }


        // --- Book Now Popup (Specific Class Booking) ---
        function openBookPopup(title, tutor, date, time, fee, classId) {
            document.getElementById('book-class-title').textContent = title;
            document.getElementById('book-class-tutor').textContent = tutor;
            document.getElementById('book-class-date').textContent = date;
            document.getElementById('book-class-time').textContent = time;
            document.getElementById('book-class-fee').textContent = 'RM' + fee;
            document.getElementById('book-class-id').value = classId; // Set hidden input value
            currentBookingClassId = classId; // Store class ID for confirm function
            document.getElementById('book-popup').style.display = 'flex';
        }

        function closeBookPopup() {
            document.getElementById('book-popup').style.display = 'none';
        }

        async function confirmBookPopup() {
            const classId = document.getElementById('book-class-id').value;
            closeBookPopup(); // Close the popup immediately

            showNotification('Booking class...', false); // Show loading message

            try {
                const response = await fetch('student.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=book_class&class_id=${classId}`
                });
                const result = await response.json();

                if (result.status === 'success') {
                    showNotification(result.message, true);
                    // Reload the page to reflect updated capacities and booked classes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification(result.message, true);
                }
            } catch (error) {
                console.error('Error booking class:', error);
                showNotification('An error occurred during booking.', true);
            }
        }

        // Optional: close book popup when clicking outside content
        document.addEventListener('DOMContentLoaded', function() {
            const bookPopup = document.getElementById('book-popup');
            if (bookPopup) {
                bookPopup.addEventListener('click', function(e) {
                    if (e.target === bookPopup) {
                        closeBookPopup();
                    }
                });
            }
        });

        // --- Rating Popup Logic ---
        function openRatingPopup(classTitle, tutorName, classId, studId) {
            document.getElementById('rating-class-title').textContent = classTitle;
            document.getElementById('rating-tutor-name').textContent = tutorName;
            document.getElementById('rating-class-id').value = classId;
            document.getElementById('rating-stud-id').value = studId;
            document.getElementById('rating-popup').style.display = 'flex';
            resetStars();
        }

        function closeRatingPopup() {
            document.getElementById('rating-popup').style.display = 'none';
        }

        function resetStars() {
            selectedRating = 0;
            document.querySelectorAll('#star-rating .star').forEach(star => {
                star.classList.remove('selected');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('#star-rating .star');

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    selectedRating = parseInt(this.getAttribute('data-value'));
                    stars.forEach((s, i) => {
                        if (i < selectedRating) {
                            s.classList.add('selected');
                        } else {
                            s.classList.remove('selected');
                        }
                    });
                });
            });

            document.getElementById('close-rating-popup-btn').onclick = closeRatingPopup;

            document.getElementById('submit-rating-btn').onclick = async function() {
                if (selectedRating === 0) {
                    showNotification('Please select a star rating.', true);
                    return;
                }

                const classId = document.getElementById('rating-class-id').value;
                const studId = document.getElementById('rating-stud-id').value;
                
                closeRatingPopup();
                showNotification('Submitting rating...', false);

                try {
                    const response = await fetch('student.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=submit_rating&class_id=${classId}&stud_id=${studId}&rating=${selectedRating}`
                    });
                    const result = await response.json();

                    if (result.status === 'success') {
                        showNotification(result.message, true);
                        setTimeout(() => {
                            window.location.reload(); // Reload to show updated ratings
                        }, 1000);
                    } else {
                        showNotification(result.message, true);
                    }
                } catch (error) {
                    console.error('Error submitting rating:', error);
                    showNotification('An error occurred while submitting rating.', true);
                }
            };
        });

        // Optional: close rating popup when clicking outside content
        document.addEventListener('DOMContentLoaded', function() {
            const ratingPopup = document.getElementById('rating-popup');
            if (ratingPopup) {
                ratingPopup.addEventListener('click', function(e) {
                    if (e.target === ratingPopup) {
                        closeRatingPopup();
                    }
                });
            }
        });

        // --- Search Bar Functionality (Basic Example) ---
        document.getElementById('class-search').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#available-classes-container tr');
            rows.forEach(row => {
                const classTitle = row.querySelector('.lesson-title').textContent.toLowerCase();
                const tutorName = row.querySelector('.lesson-tutor').textContent.toLowerCase();
                if (classTitle.includes(searchValue) || tutorName.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

    </script>
</body>
</html>
