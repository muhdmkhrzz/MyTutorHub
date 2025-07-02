document.addEventListener('DOMContentLoaded', function() {
    // --- Smooth scrolling and active link highlighting ---
    // Select all navigation links in the sidebar
    document.querySelectorAll('.sidebar-nav a').forEach(anchor => {
        // Add a click event listener to each link
        anchor.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default anchor click behavior (e.g., jumping to section)
            
            // Remove 'active' class from all navigation links
            document.querySelectorAll('.sidebar-nav a').forEach(link => {
                link.classList.remove('active');
            });
            
            // Add 'active' class to the clicked link
            this.classList.add('active');
            
            // Get the target section's ID from the href attribute (e.g., '#dashboard-section')
            const targetId = this.getAttribute('href');
            // Select the target section element
            const targetSection = document.querySelector(targetId);
            
            // If the target section exists, scroll to it smoothly
            if (targetSection) {
                targetSection.scrollIntoView({
                    behavior: 'smooth', // Smooth scrolling animation
                    block: 'start'      // Align the top of the element with the top of the viewport
                });
            }
        });
    });
    
    // --- Class modal functionality ---
    // Get references to the modal elements
    const modal = document.getElementById('class-modal');
    const closeModal = document.querySelector('.close-modal'); // The 'X' button
    const closeBtn = document.querySelector('.close-btn');     // The 'Close' button inside the modal
    
    // Open modal when 'View Class' button is clicked
    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Find the parent class card of the clicked button
            const classCard = this.closest('.class-card');
            
            // Extract class details from the card
            // .textContent is used to get the visible text, and .replace() to remove Font Awesome icons
            const classTitle = classCard.querySelector('h3').textContent;
            const classDate = classCard.querySelector('.class-details p:nth-child(2)').textContent.replace('🗓️ ', '').replace('📅 ', ''); // Added more robust icon removal
            const classTime = classCard.querySelector('.class-details p:nth-child(3)').textContent.replace('🕒 ', '').replace('⏰ ', ''); // Added more robust icon removal
            const classStudents = classCard.querySelector('.class-details p:nth-child(1)').textContent.replace('🎓 ', '').replace('🧑‍🎓 ', ''); // Added more robust icon removal
            
            // Populate the modal with the extracted class details
            document.getElementById('modal-class-title').textContent = classTitle;
            document.getElementById('modal-class-date').textContent = classDate;
            document.getElementById('modal-class-time').textContent = classTime;
            document.getElementById('modal-class-students').textContent = classStudents;
            
            // Display the modal and prevent background scrolling
            if (modal) { // Check if modal exists before trying to access its style
                modal.style.display = 'flex'; // Use flex to center the modal content
                document.body.style.overflow = 'hidden'; // Prevent scrolling behind modal
            } else {
                console.error("Error: Modal element not found. Please ensure #class-modal exists in newtutor.html");
            }
        });
    });
    
    // Function to close the modal
    function hideModal() {
        if (modal) { // Check if modal exists before trying to access its style
            modal.style.display = 'none';
            document.body.style.overflow = 'auto'; // Re-enable scrolling
        }
    }

    // Close modal when 'X' (closeModal) is clicked
    if (closeModal) { // Check if closeModal exists before adding event listener
        closeModal.addEventListener('click', hideModal);
    } else {
        console.error("Error: Close modal button (.close-modal) not found. Please ensure it exists in newtutor.html");
    }
    
    // Close modal when 'Close' button (closeBtn) is clicked
    if (closeBtn) { // Check if closeBtn exists before adding event listener
        closeBtn.addEventListener('click', hideModal);
    } else {
        console.error("Error: Close button (.close-btn) inside modal not found. Please ensure it exists in newtutor.html");
    }
    
    // Close modal when clicking outside the modal content
    window.addEventListener('click', function(event) {
        if (event.target === modal) { // If the click target is the modal overlay itself
            hideModal();
        }
    });
    
    // --- Highlight active section while scrolling ---
    // Select all content sections and sidebar navigation links
    const sections = document.querySelectorAll('.content-section');
    const navLinks = document.querySelectorAll('.sidebar-nav a');
    
    // Add a scroll event listener to the window
    window.addEventListener('scroll', function() {
        let current = ''; // Variable to store the ID of the currently active section
        
        // Loop through each content section
        sections.forEach(section => {
            // Get the top position and height of the section
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            
            // Determine if the current scroll position is within the section's range
            // Adjusting by -100px to activate the link a bit before the section fully enters view
            if (pageYOffset >= (sectionTop - 100)) {
                current = section.getAttribute('id'); // Set current to the section's ID
            }
        });
        
        // Loop through each navigation link
        navLinks.forEach(link => {
            link.classList.remove('active'); // Remove 'active' class from all links
            // If the link's href matches the current section's ID, add 'active' class
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });
});
