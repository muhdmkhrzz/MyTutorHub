// student.js - Contains all JavaScript for the student dashboard

// Global variables for popups
let currentBookingClassId = null;
let selectedClassRating = 0;
let selectedTutorRating = 0;

// Helper function to get the correct URL for PHP scripts
function getPhpScriptUrl(scriptName) {
    const pathParts = window.location.pathname.split('/');
    const studentDashboardIndex = pathParts.indexOf('student_dashboard');
    let basePath = '';

    if (studentDashboardIndex > -1) {
        basePath = pathParts.slice(0, studentDashboardIndex).join('/');
    } else {
        basePath = pathParts.slice(0, -1).join('/');
    }

    if (basePath && !basePath.startsWith('/')) {
        basePath = '/' + basePath;
    }
    if (basePath.endsWith('/')) {
        basePath = basePath.slice(0, -1);
    }
    return window.location.origin + basePath + '/database/' + scriptName;
}

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
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('main-content').classList.toggle('active');
        });
    }
});


// --- Current Date Display ---
document.addEventListener('DOMContentLoaded', function() {
    const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const currentDateElement = document.getElementById('current-date');
    if (currentDateElement) {
        currentDateElement.textContent = new Date().toLocaleDateString('en-US', dateOptions);
    }
    
    // Call the function to fetch and display dashboard data
    fetchDashboardContent();
});

// --- Function to fetch and display dashboard content ---
async function fetchDashboardContent() {
    try {
        console.log("Fetching dashboard content from:", getPhpScriptUrl('student.php'));
        const response = await fetch(getPhpScriptUrl('student.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=fetch_dashboard_data'
        });

        // Check if the response is OK (status 200)
        if (!response.ok) {
            const errorText = await response.text();
            console.error('HTTP Error fetching dashboard data:', response.status, response.statusText, errorText);
            showNotification(`Error: ${response.status} ${response.statusText}. Check server logs.`, true);
            return;
        }

        // Attempt to parse as JSON
        const responseText = await response.text();
        console.log("Raw response for fetchDashboardContent:", responseText); // Log raw response

        let result;
        try {
            result = JSON.parse(responseText);
        } catch (jsonError) {
            console.error('SyntaxError: Failed to parse JSON response for fetchDashboardContent:', jsonError);
            console.error('Problematic response text:', responseText);
            showNotification('Error: Received invalid data from server. Check server logs.', true);
            return;
        }


        if (result.status === 'success') {
            const data = result.data;

            // Populate Student Info
            const studNameElement = document.getElementById('stud_name');
            if (data.student_info && studNameElement) {
                studNameElement.textContent = data.student_info.stud_name || 'Student';
                // Store stud_id in a data attribute for later use
                studNameElement.dataset.studId = data.student_info.stud_id;
            }

            // Populate Booked Classes
            const bookedClassesContainer = document.getElementById('booked-classes-container');
            if (bookedClassesContainer) {
                bookedClassesContainer.innerHTML = ''; // Clear existing static content
            }
            const noBookedClassesElement = document.getElementById('no-booked-classes'); // Get the element once

            if (data.booked_classes && data.booked_classes.length > 0) {
                if (noBookedClassesElement) { // Check if the element exists before accessing style
                    noBookedClassesElement.style.display = 'none'; // Hide if data is present
                }
                if (bookedClassesContainer) {
                    data.booked_classes.forEach(cls => {
                        const classCard = `
                            <article class="class-card">
                                <h3 class="class_title">${cls.class_title}</h3>
                                <div class="class_description">
                                    <p><i class="far fa-calendar-alt"></i> <span class="class_date">${new Date(cls.class_date).toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' })}</span></p>
                                    <p><i class="far fa-clock"></i> <span class="class_starttime">${cls.class_starttime.substring(0, 5)} - ${cls.class_endtime.substring(0, 5)}</span></p>
                                    <p><i class="fas fa-chalkboard-teacher"></i> Tutor: <span class="tutor_name">${cls.tutor_name}</span></p>
                                    ${cls.avg_class_rating !== null ? `<p class="class-rating-display">Class Rating: ${parseFloat(cls.avg_class_rating).toFixed(1)} <i class="fas fa-star"></i></p>` : ''}
                                    ${cls.avg_tutor_rating !== null ? `<p class="tutor-rating-display">Tutor Rating: ${parseFloat(cls.avg_tutor_rating).toFixed(1)} <i class="fas fa-star"></i></p>` : ''}
                                    <!-- Changed onclick attribute to use single quotes to correctly nest JSON.stringify'd double quotes -->
                                    <button class="review-btn primary-btn" onclick='openRatingPopup(${JSON.stringify(cls.class_title)}, ${JSON.stringify(cls.tutor_name)}, ${cls.class_id}, ${JSON.stringify(data.student_info.stud_id)})'>Rate Class</button>
                                    <!-- Changed onclick attribute to use single quotes to correctly nest JSON.stringify'd double quotes -->
                                    <button class="review-btn secondary-btn" onclick='openTutorRatingPopup(${JSON.stringify(cls.tutor_name)}, ${JSON.stringify(cls.tutor_id)})'>Rate Tutor</button>
                                </div>
                            </article>
                        `;
                        bookedClassesContainer.insertAdjacentHTML('beforeend', classCard);
                    });
                }
            } else {
                if (noBookedClassesElement) { // Show if no data is present
                    noBookedClassesElement.style.display = 'block';
                }
            }

            // Populate Available Classes
            const availableClassesTableBody = document.getElementById('available-classes-container-table');
            if (availableClassesTableBody) {
                availableClassesTableBody.innerHTML = ''; // Clear existing static content
            }
            const noAvailableClassesRowElement = document.getElementById('no-available-classes-row'); // Get the element once

            if (data.available_classes && data.available_classes.length > 0) {
                if (noAvailableClassesRowElement) { // Hide if data is present
                    noAvailableClassesRowElement.style.display = 'none';
                }
                if (availableClassesTableBody) {
                    data.available_classes.forEach(cls => {
                        // Using JSON.stringify for robust argument passing
                        const classTitle = JSON.stringify(cls.class_title);
                        const tutorName = JSON.stringify(cls.tutor_name);
                        const classDateFormatted = JSON.stringify(new Date(cls.class_date).toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' }));
                        const classTimeFormatted = JSON.stringify(`${cls.class_starttime.substring(0, 5)}-${cls.class_endtime.substring(0, 5)}`);
                        const classFeeFormatted = JSON.stringify(parseFloat(cls.class_fee).toFixed(2));

                        const tableRow = `
                            <tr>
                                <td class="class_title">${cls.class_title}</td>
                                <td class="tutor_name">${cls.tutor_name}</td>
                                <td class="lesson-datetime">
                                    <span class="class_date">${new Date(cls.class_date).toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' })}</span>
                                    <span class="class_time">${cls.class_starttime.substring(0, 5)}-${cls.class_endtime.substring(0, 5)}</span>
                                </td>
                                <td class="class_fee">RM${parseFloat(cls.class_fee).toFixed(2)}</td>
                                <td class="class_capacity">${cls.class_capacity}</td>
                                <td>
                                    <!-- Changed onclick attribute to use single quotes to correctly nest JSON.stringify'd double quotes -->
                                    <button class="book-btn primary-btn" onclick='openBookPopup(${classTitle}, ${tutorName}, ${classDateFormatted}, ${classTimeFormatted}, ${classFeeFormatted}, ${cls.class_id})'>Book Now</button>
                                </td>
                            </tr>
                        `;
                        availableClassesTableBody.insertAdjacentHTML('beforeend', tableRow);
                        console.log("Generated table row HTML:", tableRow); // Debugging log
                    });
                }
            } else {
                if (noAvailableClassesRowElement) { // Show if no data is present
                    noAvailableClassesRowElement.style.display = 'table-row';
                }
            }
            
            // Trigger the search functionality after data is loaded
            const classSearchInput = document.getElementById('class-search');
            if (classSearchInput) {
                classSearchInput.value = ''; // Ensure search input is clear
                classSearchInput.dispatchEvent(new Event('keyup'));
            }

        } else {
            showNotification('Error fetching dashboard data: ' + result.message, true);
        }
    } catch (error) {
        console.error('Error in fetchDashboardContent:', error); // More specific error logging
        showNotification('An error occurred while fetching dashboard data.', true);
    }
}

// --- Buy Lesson Popup (General) ---
document.addEventListener('DOMContentLoaded', function() {
    const buyLessonBtn = document.getElementById('buy-lesson-btn');
    const buyLessonPopup = document.getElementById('buy-lesson-popup');
    const confirmBuyBtn = document.getElementById('confirm-buy-btn'); // Get the new confirm buy button

    if (buyLessonBtn) {
        buyLessonBtn.addEventListener('click', () => {
            if (buyLessonPopup) {
                buyLessonPopup.style.display = 'flex';
            }
        });
    }

    // Add event listener for the new confirm buy button
    if (confirmBuyBtn) {
        confirmBuyBtn.addEventListener('click', async () => {
            // In a real application, you'd likely have a PHP endpoint to handle "buying a lesson"
            // For now, we'll just simulate a confirmation and close the popup.
            showNotification('Lesson purchase confirmed (simulated)!', true);
            if (buyLessonPopup) {
                buyLessonPopup.style.display = 'none';
            }
            // If there was a ToyyibPay integration, you would redirect here after a successful backend call
            // window.location.href = 'https://toyyibpay.com/MyTutorHub-Registration';
        });
    }

    const closeBuyPopupBtn = document.getElementById('close-buy-popup-btn'); // Moved declaration
    if (closeBuyPopupBtn) {
        closeBuyPopupBtn.addEventListener('click', () => {
            if (buyLessonPopup) {
                buyLessonPopup.style.display = 'none';
            }
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
});


// --- Book Now Popup (Specific Class Booking) ---
// Make functions globally accessible for inline onclick attributes
window.openBookPopup = function(title, tutor, date, time, fee, classId) {
    console.log("openBookPopup called with:", { title, tutor, date, time, fee, classId }); // Debugging log
    const bookClassTitle = document.getElementById('book-class-title');
    const bookClassTutor = document.getElementById('book-class-tutor');
    const bookClassDate = document.getElementById('book-class-date');
    const bookClassTime = document.getElementById('book-class-time');
    const bookClassFee = document.getElementById('book-class-fee'); 
    const bookClassId = document.getElementById('book-class-id');
    const bookPopup = document.getElementById('book-popup');

    // Add checks for null elements
    if (bookClassTitle) bookClassTitle.textContent = title;
    if (bookClassTutor) bookClassTutor.textContent = tutor;
    if (bookClassDate) bookClassDate.textContent = date;
    if (bookClassTime) bookClassTime.textContent = time;
    if (bookClassFee) bookClassFee.textContent = 'RM' + fee;
    if (bookClassId) bookClassId.value = classId;
    
    currentBookingClassId = classId; // Store class ID for confirm function
    
    if (bookPopup) { // Check if popup element exists before displaying
        bookPopup.style.display = 'flex';
    } else {
        console.error("Error: 'book-popup' element not found.");
        showNotification("Error: Booking popup not available.", true);
    }
}

window.closeBookPopup = function() {
    const bookPopup = document.getElementById('book-popup');
    if (bookPopup) {
        bookPopup.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const confirmBookBtn = document.getElementById('confirm-book-btn');
    if (confirmBookBtn) {
        confirmBookBtn.addEventListener('click', confirmBookPopup);
    }
});

window.confirmBookPopup = async function() {
    const classIdInput = document.getElementById('book-class-id');
    const classId = classIdInput ? classIdInput.value : null;

    if (!classId) {
        console.error("Error: Class ID not found for booking confirmation.");
        showNotification("Error: Cannot confirm booking, class ID missing.", true);
        return;
    }

    closeBookPopup(); // Close the popup immediately

    showNotification('Booking class...', false); // Show loading message

    try {
        console.log("Confirming booking with:", getPhpScriptUrl('student.php'));
        const response = await fetch(getPhpScriptUrl('student.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=book_class&class_id=${classId}`
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('HTTP Error confirming booking:', response.status, response.statusText, errorText);
            showNotification(`Error: ${response.status} ${response.statusText}. Check server logs.`, true);
            return;
        }

        const responseText = await response.text();
        console.log("Raw response for confirmBookPopup:", responseText); // Log raw response

        let result;
        try {
            result = JSON.parse(responseText);
        } catch (jsonError) {
            console.error('SyntaxError: Failed to parse JSON response for confirmBookPopup:', jsonError);
            console.error('Problematic response text:', responseText);
            showNotification('Error: Received invalid data from server. Check server logs.', true);
            return;
        }


        if (result.status === 'success') {
            showNotification(result.message, true);
            // Redirect to ToyyibPay after successful booking
            window.location.href = 'https://toyyibpay.com/MyTutorHub-Registration';
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

// --- Rating Popup Logic (for Class) ---
// Make functions globally accessible for inline onclick attributes
window.openRatingPopup = function(classTitle, tutorName, classId, studId) {
    document.getElementById('rating-class-title').textContent = classTitle;
    document.getElementById('rating-tutor-name').textContent = tutorName;
    document.getElementById('rating-class-id').value = classId;
    document.getElementById('rating-stud-id').value = studId; // Assuming stud_id is passed from PHP
    document.getElementById('rating-popup').style.display = 'flex';
    resetClassStars(); // Reset stars specifically for class rating
}

window.closeRatingPopup = function() {
    document.getElementById('rating-popup').style.display = 'none';
}

window.resetClassStars = function() {
    selectedClassRating = 0;
    document.querySelectorAll('#star-rating .star').forEach(star => {
        star.classList.remove('selected');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const classStars = document.querySelectorAll('#star-rating .star');

    classStars.forEach(star => {
        star.addEventListener('click', function() {
            selectedClassRating = parseInt(this.getAttribute('data-value'));
            classStars.forEach((s, i) => {
                if (i < selectedClassRating) {
                    s.classList.add('selected');
                } else {
                    s.classList.remove('selected');
                }
            });
        });
    });

    const closeRatingPopupBtn = document.getElementById('close-rating-popup-btn');
    if (closeRatingPopupBtn) {
        closeRatingPopupBtn.onclick = closeRatingPopup;
    }

    const submitClassRatingBtn = document.getElementById('submit-class-rating-btn');
    if (submitClassRatingBtn) {
        submitClassRatingBtn.onclick = async function() {
            if (selectedClassRating === 0) {
                showNotification('Please select a star rating for the class.', true);
                return;
            }

            const ratingClassId = document.getElementById('rating-class-id');
            const ratingStudId = document.getElementById('rating-stud-id');
            const classId = ratingClassId ? ratingClassId.value : null;
            const studId = ratingStudId ? ratingStudId.value : null;
            
            if (!classId || !studId) {
                console.error("Error: Class ID or Student ID missing for class rating.");
                showNotification("Error: Cannot submit class rating, IDs missing.", true);
                return;
            }

            closeRatingPopup();
            showNotification('Submitting class rating...', false);

            try {
                console.log("Submitting class rating to:", getPhpScriptUrl('student.php'));
                const response = await fetch(getPhpScriptUrl('student.php'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=submit_class_rating&class_id=${classId}&stud_id=${studId}&rating=${selectedClassRating}`
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('HTTP Error submitting class rating:', response.status, response.statusText, errorText);
                    showNotification(`Error: ${response.status} ${response.statusText}. Check server logs.`, true);
                    return;
                }

                const responseText = await response.text();
                console.log("Raw response for submitClassRating:", responseText); // Log raw response

                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (jsonError) {
                    console.error('SyntaxError: Failed to parse JSON response for submitClassRating:', jsonError);
                    console.error('Problematic response text:', responseText);
                    showNotification('Error: Received invalid data from server. Check server logs.', true);
                    return;
                }


                if (result.status === 'success') {
                    showNotification(result.message, true);
                    setTimeout(() => {
                        fetchDashboardContent(); // Refresh content instead of full reload
                    }, 1000);
                } else {
                    showNotification(result.message, true);
                }
            } catch (error) {
                console.error('Error submitting class rating:', error);
                showNotification('An error occurred while submitting class rating.', true);
            }
        };
    }
});

// Optional: close class rating popup when clicking outside content
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

// --- Rating Popup Logic (for Tutor) ---
// Make functions globally accessible for inline onclick attributes
window.openTutorRatingPopup = function(tutorName, tutorId) {
    document.getElementById('tutor-rating-tutor-name').textContent = tutorName;
    document.getElementById('tutor-rating-tutor-id').value = tutorId;
    
    const studNameDataset = document.getElementById('stud_name');
    if (studNameDataset) {
        document.getElementById('tutor-rating-stud-id').value = studNameDataset.dataset.studId; // Get actual stud_id
    } else {
        console.warn("Element with ID 'stud_name' not found. Cannot set tutor-rating-stud-id.");
    }

    document.getElementById('tutor-rating-popup').style.display = 'flex';
    resetTutorStars(); // Reset stars specifically for tutor rating
}

window.closeTutorRatingPopup = function() {
    const tutorRatingPopup = document.getElementById('tutor-rating-popup');
    if (tutorRatingPopup) {
        tutorRatingPopup.style.display = 'none';
    }
}

window.resetTutorStars = function() {
    selectedTutorRating = 0;
    document.querySelectorAll('#tutor-star-rating .star').forEach(star => {
        star.classList.remove('selected');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const tutorStars = document.querySelectorAll('#tutor-star-rating .star');

    tutorStars.forEach(star => {
        star.addEventListener('click', function() {
            selectedTutorRating = parseInt(this.getAttribute('data-value'));
            tutorStars.forEach((s, i) => {
                if (i < selectedTutorRating) {
                    s.classList.add('selected');
                } else {
                    s.classList.remove('selected');
                }
            });
        });
    });

    const closeTutorRatingPopupBtn = document.getElementById('close-tutor-rating-popup-btn');
    if (closeTutorRatingPopupBtn) {
        closeTutorRatingPopupBtn.onclick = closeTutorRatingPopup;
    }

    const submitTutorRatingBtn = document.getElementById('submit-tutor-rating-btn');
    if (submitTutorRatingBtn) {
        submitTutorRatingBtn.onclick = async function() {
            if (selectedTutorRating === 0) {
                showNotification('Please select a star rating for the tutor.', true);
                return;
            }

            const tutorRatingTutorId = document.getElementById('tutor-rating-tutor-id');
            const tutorRatingStudId = document.getElementById('tutor-rating-stud-id');
            const tutorId = tutorRatingTutorId ? tutorRatingTutorId.value : null;
            const studId = tutorRatingStudId ? tutorRatingStudId.value : null;

            if (!tutorId || !studId) {
                console.error("Error: Tutor ID or Student ID missing for tutor rating.");
                showNotification("Error: Cannot submit tutor rating, IDs missing.", true);
                return;
            }
            
            closeTutorRatingPopup();
            showNotification('Submitting tutor rating...', false);

            try {
                console.log("Submitting tutor rating to:", getPhpScriptUrl('student.php'));
                const response = await fetch(getPhpScriptUrl('student.php'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=submit_tutor_rating&tutor_id=${tutorId}&stud_id=${studId}&rating=${selectedTutorRating}`
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('HTTP Error submitting tutor rating:', response.status, response.statusText, errorText);
                    showNotification(`Error: ${response.status} ${response.statusText}. Check server logs.`, true);
                    return;
                }

                const responseText = await response.text();
                console.log("Raw response for submitTutorRating:", responseText); // Log raw response

                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (jsonError) {
                    console.error('SyntaxError: Failed to parse JSON response for submitTutorRating:', jsonError);
                    console.error('Problematic response text:', responseText);
                    showNotification('Error: Received invalid data from server. Check server logs.', true);
                    return;
                }


                if (result.status === 'success') {
                    showNotification(result.message, true);
                    setTimeout(() => {
                        fetchDashboardContent(); // Refresh content instead of full reload
                    }, 1000);
                } else {
                    showNotification(result.message, true);
                }
            } catch (error) {
                console.error('Error submitting tutor rating:', error);
                showNotification('An error occurred while submitting tutor rating.', true);
            }
        };
    }
});

// Optional: close tutor rating popup when clicking outside content
document.addEventListener('DOMContentLoaded', function() {
    const tutorRatingPopup = document.getElementById('tutor-rating-popup');
    if (tutorRatingPopup) {
        tutorRatingPopup.addEventListener('click', function(e) {
            if (e.target === tutorRatingPopup) {
                closeTutorRatingPopup();
            }
        });
    }
});


// --- Search Bar Functionality (Basic Example) ---
document.addEventListener('DOMContentLoaded', function() {
    const classSearchInput = document.getElementById('class-search');
    if (classSearchInput) {
        classSearchInput.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#available-classes-container-table tr'); // Target the table rows
            
            // Ensure rows is a NodeList or array before iterating
            if (rows && typeof rows.forEach === 'function') {
                rows.forEach(row => {
                    // Skip the "no available classes" row if it exists
                    if (row.id === 'no-available-classes-row') {
                        return;
                    }
                    const classTitleElement = row.querySelector('.class_title');
                    const tutorNameElement = row.querySelector('.tutor_name');

                    if (classTitleElement && tutorNameElement) {
                        const classTitle = classTitleElement.textContent.toLowerCase();
                        const tutorName = tutorNameElement.textContent.toLowerCase();
                        if (classTitle.includes(searchValue) || tutorName.includes(searchValue)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            } else {
                console.warn("Search: #available-classes-container-table tr not found or not iterable.");
            }
        });
    }
});
