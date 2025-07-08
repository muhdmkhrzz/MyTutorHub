document.addEventListener('DOMContentLoaded', function() {
    // --- Global variables for modals and data ---
    const classModal = document.getElementById('class-modal');
    const createClassModal = document.getElementById('create-class-modal');
    const addResourceModal = document.getElementById('add-resource-modal');
    const editResourceModal = document.getElementById('edit-resource-modal');
    const editClassModal = document.getElementById('edit-class-modal');
    const confirmDeleteModal = document.getElementById('confirm-delete-modal');

    const notification = document.getElementById('notification');

    const createClassForm = document.getElementById('create-class-form');
    const addResourceForm = document.getElementById('add-resource-form');
    const editResourceForm = document.getElementById('edit-resource-form');
    const editClassForm = document.getElementById('edit-class-form');

    const newCourseSelect = document.getElementById('new_course_id');
    const resourceClassSelect = document.getElementById('resource_class_id');
    const editResourceClassSelect = document.getElementById('edit_resource_class_id');
    const editClassCourseSelect = document.getElementById('edit_course_id');

    // Store my_classes data globally for dropdowns and resources
    let myClassesData = [];
    // Local array to store resources (frontend only for now, backend needed for actual uploads)
    let resourcesList = [];
    let nextResourceId = 1; // Simple ID counter for mock resources
    let currentClassIdToDelete = null; // To store the ID of the class to be deleted

    // --- Utility Functions for Modals ---
    function hideModal(modalElement) {
        if (modalElement) {
            modalElement.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    function showModal(modalElement) {
        if (modalElement) {
            modalElement.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function showNotification(message, type = 'info') {
        notification.textContent = message;
        notification.className = `notification ${type}`;
        notification.style.display = 'block';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }

    // --- Smooth scrolling and active link highlighting ---
    document.querySelectorAll('.sidebar-nav a').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');

            // Only prevent default and scroll if it's an internal anchor link
            if (targetId.startsWith('#')) {
                e.preventDefault(); // Prevent default link behavior for internal anchors
                
                document.querySelectorAll('.sidebar-nav a').forEach(link => {
                    link.classList.remove('active');
                });
                this.classList.add('active');
                
                const targetSection = document.querySelector(targetId);
                if (targetSection) {
                    targetSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
            // For other links (like logout.php), allow default navigation
        });
    });

    // --- Class modal functionality (View/Manage Class) ---
    document.querySelectorAll('.close-modal, .close-btn').forEach(button => {
        button.addEventListener('click', () => hideModal(classModal));
    });
    window.addEventListener('click', function(event) {
        if (event.target === classModal) {
            hideModal(classModal);
        }
    });

    function showClassModal(classDetails) {
        document.getElementById('modal-class-title').textContent = classDetails.class_title;
        document.getElementById('modal-class-date').textContent = classDetails.class_date;
        document.getElementById('modal-class-time').textContent = `${classDetails.class_starttime.substring(0, 5)} - ${classDetails.class_endtime.substring(0, 5)}`;
        document.getElementById('modal-class-students').textContent = classDetails.enrolled_students_count !== undefined ? `${classDetails.enrolled_students_count} / ${classDetails.class_capacity} Students` : `${classDetails.class_capacity} Students`;
        document.getElementById('modal-class-description').textContent = classDetails.class_description || 'No description provided.';
        document.getElementById('modal-class-fee').textContent = classDetails.class_fee;
        document.getElementById('modal-class-course').textContent = classDetails.course_name || 'N/A';

        // Display Registration Deadline in View/Manage Class Modal
        const modalDeadlineContainer = document.getElementById('modal-class-deadline-container');
        const modalDeadlineSpan = document.getElementById('modal-class-deadline');
        // Check for existence and non-zero date value
        if (classDetails.class_deadline && classDetails.class_deadline !== '0000-00-00') {
            modalDeadlineSpan.textContent = classDetails.class_deadline;
            modalDeadlineContainer.style.display = 'flex'; // Show the container
        } else {
            modalDeadlineContainer.style.display = 'none'; // Hide the container
        }

        // Display Class File Link
        const classFileContainer = document.getElementById('modal-class-file-container');
        const classFileLink = document.getElementById('modal-class-file-link');
        if (classDetails.class_file) {
            classFileLink.href = classDetails.class_file;
            // Create an icon element
            const icon = document.createElement('i');
            icon.className = 'fas fa-file-alt file-icon'; // Using a file icon, add 'file-icon' class for styling
            
            // Determine the display text for the link
            let linkText = classDetails.class_file;
            const fileName = linkText.substring(linkText.lastIndexOf('/') + 1); // Get actual file name
            if (fileName.length > 30) { // Truncate if file name is too long
                linkText = fileName.substring(0, 27) + '...';
            } else {
                linkText = fileName;
            }

            // Clear previous content and append icon and text
            classFileLink.innerHTML = ''; // Clear existing content
            classFileLink.appendChild(icon);
            classFileLink.appendChild(document.createTextNode(` ${linkText}`)); // Add text node with a space
            
            classFileContainer.style.display = 'flex'; // Show the container
        } else {
            classFileContainer.style.display = 'none'; // Hide if no file
        }

        // Attach event listeners to Edit/Delete buttons in this modal
        document.getElementById('edit-class-btn').onclick = () => handleEditClassClick(classDetails.class_id);
        document.getElementById('delete-class-btn').onclick = () => handleDeleteClassClick(classDetails.class_id);

        showModal(classModal);
    }

    // --- Create Class Modal functionality ---
    document.getElementById('create-class-tile').addEventListener('click', () => {
        showModal(createClassModal);
        createClassForm.reset();
        fetchCoursesForDropdown(newCourseSelect); // Pass the specific select element
    });
    document.querySelector('.close-modal-create').addEventListener('click', () => hideModal(createClassModal));
    window.addEventListener('click', function(event) {
        if (event.target === createClassModal) {
            hideModal(createClassModal);
        }
    });

    // --- Fetch and populate courses dropdown (for Create/Edit Class) ---
    async function fetchCoursesForDropdown(selectElement, selectedCourseId = null) {
        try {
            const response = await fetch('../database/get_courses.php');
            const data = await response.json();
            if (data.success && data.courses.length > 0) {
                selectElement.innerHTML = '<option value="">Select a Course</option>';
                data.courses.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course.course_id;
                    option.textContent = course.course_name;
                    if (selectedCourseId !== null && parseInt(course.course_id) === parseInt(selectedCourseId)) {
                        option.selected = true;
                    }
                    selectElement.appendChild(option);
                });
            } else {
                selectElement.innerHTML = '<option value="">No courses available</option>';
                showNotification(`Error loading courses: ${data.message || 'No courses found'}`, 'info');
            }
        } catch (error) {
            console.error('Error fetching courses:', error);
            selectElement.innerHTML = '<option value="">Error loading courses</option>';
            showNotification('Failed to load courses for dropdown. Please try again.', 'error');
        }
    }


    // --- Fetch and display dashboard data ---
    async function fetchDashboardData() {
        try {
            const response = await fetch('../database/get_tutor_dashboard_data.php');
            const data = await response.json();
            if (data.success) {
                myClassesData = data.my_classes; // Update global classes data
                renderTodaySchedule(data.today_classes);
                renderMyClasses(data.my_classes);
                renderResources(); // Render resources from local array
                fetchReportsData(); // NEW: Fetch and render reports data
            } else {
                showNotification(`Error loading dashboard data: ${data.message}`, 'error');
            }
        } catch (error) {
            console.error('Error fetching dashboard data:', error);
            showNotification('Failed to load dashboard data. Please try again.', 'error');
        }
    }

    function renderTodaySchedule(classes) {
        const container = document.getElementById('today-schedule-container');
        container.innerHTML = '';
        if (classes.length === 0) {
            container.innerHTML = '<p>No classes scheduled for today.</p>';
            return;
        }
        classes.forEach(cls => {
            const card = document.createElement('div');
            card.className = 'class-card';
            card.innerHTML = `
                <h3>${cls.class_title}</h3>
                <div class="class-details">
                    <p><i class="fas fa-user-graduate"></i> ${cls.enrolled_students} / ${cls.class_capacity} Students</p>
                    <p><i class="far fa-calendar-alt"></i> ${new Date(cls.class_date).toLocaleDateString()}</p>
                    <p><i class="far fa-clock"></i> ${cls.class_starttime.substring(0, 5)} - ${cls.class_endtime.substring(0, 5)}</p>
                </div>
                <button class="btn view-btn" data-class-id="${cls.class_id}">View Class</button>
            `;
            container.appendChild(card);
        });
        attachViewManageButtonListeners();
    }

    function renderMyClasses(classes) {
        const container = document.getElementById('my-classes-container');
        const createTile = document.getElementById('create-class-tile');
        container.innerHTML = '';
        container.appendChild(createTile);

        if (classes.length === 0) {
            const noClassesMsg = document.createElement('p');
            noClassesMsg.textContent = 'You have not created any classes yet.';
            container.insertBefore(noClassesMsg, createTile);
            return;
        }

        classes.forEach(cls => {
            const tile = document.createElement('div');
            tile.className = 'class-tile';
            tile.innerHTML = `
                <h3>${cls.class_title}</h3>
                <p>${cls.class_description || 'No description'}</p>
                <button class="btn manage-btn" data-class-id="${cls.class_id}">Manage Class</button>
            `;
            container.insertBefore(tile, createTile);
        });
        attachViewManageButtonListeners();
    }

    function attachViewManageButtonListeners() {
        document.querySelectorAll('.view-btn, .manage-btn').forEach(button => {
            button.removeEventListener('click', handleViewManageClick);
            button.addEventListener('click', handleViewManageClick);
        });
    }

    async function handleViewManageClick() {
        const classId = this.dataset.classId;
        if (!classId) {
            showNotification('Class ID not found.', 'error');
            return;
        }
        try {
            const response = await fetch(`../database/get_class_details.php?class_id=${classId}`);
            const data = await response.json();
            if (data.success) {
                showClassModal(data.class_details);
            } else {
                showNotification(`Error fetching class details: ${data.message}`, 'error');
            }
        } catch (error) {
            console.error('Error fetching class details:', error);
            showNotification('Failed to load class details. Please try again.', 'error');
        }
    }

    // --- Handle Create Class Form Submission ---
    createClassForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        // Ensure class_file is included if present
        const classFile = document.getElementById('new_class_file').value;
        if (classFile) {
            formData.append('class_file', classFile);
        }

        try {
            const response = await fetch('../database/create_class.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                showNotification(data.message, 'success');
                hideModal(createClassModal);
                fetchDashboardData(); // Refresh dashboard data
            } else {
                showNotification(`Error: ${data.message}`, 'error');
            }
        } catch (error) {
            console.error('Error creating class:', error);
            showNotification('Failed to create class. Please try again.', 'error');
        }
    });

    // --- Resources Section Logic (Frontend Only for now, backend needed for actual uploads) ---
    const resourcesListContainer = document.getElementById('resources-list-container');

    function renderResources() {
        resourcesListContainer.innerHTML = ''; // Clear existing content

        if (resourcesList.length === 0) {
            resourcesListContainer.innerHTML = '<p>No resources added yet. Click "Add New Resource" to get started.</p>';
            return;
        }

        resourcesList.forEach(resource => {
            const resourceItem = document.createElement('div');
            resourceItem.className = 'resource-item-card';
            resourceItem.innerHTML = `
                <h3>${resource.title}</h3>
                <p><strong>Class:</strong> ${resource.classTitle}</p>
                <p>
                    <strong>File:</strong>
                    <a href="${resource.filePath || '#'}" target="_blank" rel="noopener noreferrer">
                        <i class="fas fa-file-alt"></i> ${resource.fileName || 'View File'}
                    </a>
                </p>
                <div class="resource-actions">
                    <button class="btn icon-btn edit-resource-btn" data-resource-id="${resource.id}">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button class="btn icon-btn delete-resource-btn" data-resource-id="${resource.id}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            `;
            resourcesListContainer.appendChild(resourceItem);
        });

        attachResourceActionListeners(); // Attach listeners after rendering
    }

    function attachResourceActionListeners() {
        document.querySelectorAll('.edit-resource-btn').forEach(button => {
            button.removeEventListener('click', handleEditResourceClick);
            button.addEventListener('click', handleEditResourceClick);
        });
        document.querySelectorAll('.delete-resource-btn').forEach(button => {
            button.removeEventListener('click', handleDeleteResourceClick);
            button.addEventListener('click', handleDeleteResourceClick);
        });
    }

    function handleEditResourceClick() {
        const resourceId = parseInt(this.dataset.resourceId);
        const resourceToEdit = resourcesList.find(r => r.id === resourceId);

        if (resourceToEdit) {
            showModal(editResourceModal);
            document.getElementById('edit_resource_id').value = resourceToEdit.id;
            document.getElementById('edit_resource_title').value = resourceToEdit.title;
            // Display current file name
            document.getElementById('current-resource-file-name').textContent = resourceToEdit.fileName || 'None';
            
            populateResourceClassDropdown(editResourceClassSelect, resourceToEdit.classId);
        } else {
            showNotification('Resource not found for editing.', 'error');
        }
    }

    function handleDeleteResourceClick() {
        const resourceId = parseInt(this.dataset.resourceId);
        resourcesList = resourcesList.filter(resource => resource.id !== resourceId);
        showNotification('Resource deleted successfully (frontend only)!', 'success');
        renderResources();
    }

    // --- Add Resource Modal Logic ---
    document.getElementById('add-resource-button').addEventListener('click', () => {
        showModal(addResourceModal);
        addResourceForm.reset();
        populateResourceClassDropdown(resourceClassSelect);
    });
    document.querySelector('.close-modal-add-resource').addEventListener('click', () => hideModal(addResourceModal));
    window.addEventListener('click', function(event) {
        if (event.target === addResourceModal) {
            hideModal(addResourceModal);
        }
    });

    // Function to populate the class dropdown in the Add/Edit Resource modals
    function populateResourceClassDropdown(selectElement, selectedClassId = null) {
        selectElement.innerHTML = '<option value="">Select a Class</option>';
        if (myClassesData.length > 0) {
            myClassesData.forEach(cls => {
                const option = document.createElement('option');
                option.value = cls.class_id;
                option.textContent = cls.class_title;
                if (selectedClassId !== null && parseInt(cls.class_id) === parseInt(selectedClassId)) {
                    option.selected = true;
                }
                selectElement.appendChild(option);
            });
        } else {
            selectElement.innerHTML += '<option value="">No classes available</option>';
        }
    }

    // Handle Add Resource Form Submission (Frontend Only - Backend needed for actual upload)
    addResourceForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const classId = parseInt(resourceClassSelect.value);
        const resourceTitle = document.getElementById('resource_title').value;
        const resourceFile = document.getElementById('resource_file').files[0]; // Get the selected file

        if (!classId || !resourceTitle || !resourceFile) {
            showNotification('Please fill all resource fields and select a file.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('class_id', classId);
        formData.append('resource_title', resourceTitle);
        formData.append('resource_file', resourceFile); // Append the file

        try {
            // --- IMPORTANT: This is where you would send the file to a PHP backend ---
            // Example:
            // const response = await fetch('../database/upload_resource.php', {
            //     method: 'POST',
            //     body: formData
            // });
            // const data = await response.json();
            // if (data.success) {
            //     const uploadedFilePath = data.filePath; // Path returned from PHP
            //     const uploadedFileName = data.fileName; // Name returned from PHP
            //     // Add to local resourcesList with actual path/name
            //     const newResource = {
            //         id: nextResourceId++,
            //         classId: classId,
            //         classTitle: myClassesData.find(cls => cls.class_id === classId)?.class_title || `Class ID ${classId}`,
            //         title: resourceTitle,
            //         filePath: uploadedFilePath,
            //         fileName: uploadedFileName
            //     };
            //     resourcesList.push(newResource);
            //     showNotification('Resource added successfully!', 'success');
            // } else {
            //     showNotification(`Error uploading resource: ${data.message}`, 'error');
            // }

            // For now, simulating success and storing file details locally (frontend-only)
            const newResource = {
                id: nextResourceId++,
                classId: classId,
                classTitle: myClassesData.find(cls => cls.class_id === classId)?.class_title || `Class ID ${classId}`,
                title: resourceTitle,
                filePath: URL.createObjectURL(resourceFile), // Create a temporary URL for display
                fileName: resourceFile.name
            };
            resourcesList.push(newResource);
            showNotification('Resource added successfully (frontend only)!', 'success');

            hideModal(addResourceModal);
            addResourceForm.reset();
            renderResources();
        } catch (error) {
            console.error('Error adding resource:', error);
            showNotification('Failed to add resource. Please try again.', 'error');
        }
    });

    // --- Handle Edit Resource Form Submission (Frontend Only - Backend needed for actual upload) ---
    document.querySelector('.close-modal-edit-resource').addEventListener('click', () => hideModal(editResourceModal));
    window.addEventListener('click', function(event) {
        if (event.target === editResourceModal) {
            hideModal(editResourceModal);
        }
    });

    editResourceForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const resourceId = parseInt(document.getElementById('edit_resource_id').value);
        const newClassId = parseInt(editResourceClassSelect.value);
        const newResourceTitle = document.getElementById('edit_resource_title').value;
        const newResourceFile = document.getElementById('edit_resource_file').files[0]; // Get new selected file

        if (!newClassId || !newResourceTitle) {
            showNotification('Please fill all fields for editing resource.', 'error');
            return;
        }

        const resourceIndex = resourcesList.findIndex(r => r.id === resourceId);
        if (resourceIndex > -1) {
            const selectedClass = myClassesData.find(cls => cls.class_id === newClassId);
            const newClassTitle = selectedClass ? selectedClass.class_title : `Class ID ${newClassId}`;

            let updatedResource = { ...resourcesList[resourceIndex] }; // Copy existing resource
            updatedResource.classId = newClassId;
            updatedResource.classTitle = newClassTitle;
            updatedResource.title = newResourceTitle;

            if (newResourceFile) {
                // --- IMPORTANT: This is where you would send the new file to a PHP backend ---
                // Example:
                // const formData = new FormData();
                // formData.append('resource_id', resourceId); // If updating existing file
                // formData.append('resource_file', newResourceFile);
                // const response = await fetch('../database/update_resource_file.php', {
                //     method: 'POST',
                //     body: formData
                // });
                // const data = await response.json();
                // if (data.success) {
                //     updatedResource.filePath = data.filePath;
                //     updatedResource.fileName = data.fileName;
                //     showNotification('Resource file updated successfully!', 'success');
                // } else {
                //     showNotification(`Error updating resource file: ${data.message}`, 'error');
                //     return; // Stop if file upload fails
                // }

                // Simulating success for frontend-only
                updatedResource.filePath = URL.createObjectURL(newResourceFile);
                updatedResource.fileName = newResourceFile.name;
                showNotification('Resource file updated successfully (frontend only)!', 'success');
            }

            resourcesList[resourceIndex] = updatedResource;
            showNotification('Resource updated successfully (frontend only)!', 'success');
            hideModal(editResourceModal);
            renderResources();
        } else {
            showNotification('Resource not found for update.', 'error');
        }
    });

    // --- NEW: Edit Class Modal Logic ---
    document.querySelector('.close-modal-edit-class').addEventListener('click', () => hideModal(editClassModal));
    window.addEventListener('click', function(event) {
        if (event.target === editClassModal) {
            hideModal(editClassModal);
        }
    });

    async function handleEditClassClick(classId) {
        try {
            const response = await fetch(`../database/get_class_details.php?class_id=${classId}`);
            const data = await response.json();

            if (data.success) {
                const classDetails = data.class_details;
                document.getElementById('edit_class_id').value = classDetails.class_id;
                document.getElementById('edit_class_title').value = classDetails.class_title;
                document.getElementById('edit_class_description').value = classDetails.class_description || '';
                document.getElementById('edit_class_date').value = classDetails.class_date;
                document.getElementById('edit_class_starttime').value = classDetails.class_starttime.substring(0, 5); // Ensure HH:MM format
                document.getElementById('edit_class_endtime').value = classDetails.class_endtime.substring(0, 5);   // Ensure HH:MM format
                document.getElementById('edit_class_capacity').value = classDetails.class_capacity;
                
                // Debugging: Log the raw deadline value
                console.log('Raw classDetails.class_deadline:', classDetails.class_deadline);

                // Correctly set deadline, handling potential '0000-00-00' from MySQL
                document.getElementById('edit_class_deadline').value = 
                    (classDetails.class_deadline && classDetails.class_deadline !== '0000-00-00') 
                    ? classDetails.class_deadline 
                    : ''; 
                
                document.getElementById('edit_class_fee').value = classDetails.class_fee;
                document.getElementById('edit_class_file').value = classDetails.class_file || '';

                // Populate and select the correct course in the edit modal
                await fetchCoursesForDropdown(editClassCourseSelect, classDetails.course_id);

                hideModal(classModal); // Hide the view class modal
                showModal(editClassModal); // Show the edit class modal
            } else {
                showNotification(`Error fetching class details for edit: ${data.message}`, 'error');
            }
        } catch (error) {
            console.error('Error fetching class details for edit:', error);
            showNotification('Failed to load class details for editing. Please try again.', 'error');
        }
    }

    editClassForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const response = await fetch('../database/update_class.php', { // This file will be created
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                showNotification(data.message, 'success');
                hideModal(editClassModal);
                fetchDashboardData(); // Refresh dashboard data
            } else {
                showNotification(`Error updating class: ${data.message}`, 'error');
            }
        } catch (error) {
            console.error('Error updating class:', error);
            showNotification('Failed to update class. Please try again.', 'error');
        }
    });

    // --- NEW: Delete Class Modal Logic ---
    document.querySelector('.close-modal-confirm-delete').addEventListener('click', () => hideModal(confirmDeleteModal));
    window.addEventListener('click', function(event) {
        if (event.target === confirmDeleteModal) {
            hideModal(confirmDeleteModal);
        }
    });

    function handleDeleteClassClick(classId) {
        currentClassIdToDelete = classId; // Store the ID
        hideModal(classModal); // Hide the view class modal
        showModal(confirmDeleteModal); // Show the confirmation modal
    }

    document.getElementById('confirm-delete-btn').addEventListener('click', async () => {
        if (currentClassIdToDelete) {
            try {
                const response = await fetch('../database/delete_class.php', { // This file will be created
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `class_id=${currentClassIdToDelete}`
                });
                const data = await response.json();

                if (data.success) {
                    showNotification(data.message, 'success');
                    hideModal(confirmDeleteModal);
                    fetchDashboardData(); // Refresh dashboard
                } else {
                    showNotification(`Error deleting class: ${data.message}`, 'error');
                }
            } catch (error) {
                console.error('Error deleting class:', error);
                showNotification('Failed to delete class. Please try again.', 'error');
            } finally {
                currentClassIdToDelete = null; // Reset
            }
        }
    });

    document.getElementById('cancel-delete-btn').addEventListener('click', () => {
        hideModal(confirmDeleteModal);
        currentClassIdToDelete = null; // Reset
    });

    // Initial load of dashboard data when the page is ready
    fetchDashboardData();

    // --- NEW: Fetch and render Reports data ---
    async function fetchReportsData() {
        try {
            const response = await fetch('../database/get_reports_data.php');
            const data = await response.json();
            if (data.success) {
                renderReports(data.total_revenue, data.total_student_bookings);
            } else {
                showNotification(`Error loading reports data: ${data.message}`, 'error');
            }
        } catch (error) {
            console.error('Error fetching reports data:', error);
            showNotification('Failed to load reports data. Please try again.', 'error');
        }
    }

    function renderReports(totalRevenue, totalStudentBookings) {
        document.getElementById('total-revenue').textContent = totalRevenue;
        document.getElementById('total-student-bookings').textContent = totalStudentBookings;
    }

    // --- Highlight active section while scrolling (existing logic) ---
    // Updated to exclude 'student-overview-section'
    const sections = document.querySelectorAll('.content-section');
    const navLinks = document.querySelectorAll('.sidebar-nav a');

    window.addEventListener('scroll', function() {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            // Adjust offset to trigger active state a bit earlier
            if (pageYOffset >= (sectionTop - 150)) {
                current = section.getAttribute('id');
            }
        });
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });
});
