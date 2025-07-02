document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling and active link highlighting
    document.querySelectorAll('.sidebar-nav a').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links
            document.querySelectorAll('.sidebar-nav a').forEach(link => {
                link.classList.remove('active');
            });
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Scroll to section
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            if (targetSection) {
                targetSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Class modal functionality
    const modal = document.getElementById('class-modal');
    const closeModal = document.querySelector('.close-modal');
    const closeBtn = document.querySelector('.close-btn');
    
    // Open modal when View Class button is clicked
    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', function() {
            const classCard = this.closest('.class-card');
            const classTitle = classCard.querySelector('h3').textContent;
            const classDate = classCard.querySelector('.class-details p:nth-child(2)').textContent.replace(' ', '');
            const classTime = classCard.querySelector('.class-details p:nth-child(3)').textContent.replace(' ', '');
            const classStudents = classCard.querySelector('.class-details p:nth-child(1)').textContent.replace(' ', '');
            
            document.getElementById('modal-class-title').textContent = classTitle;
            document.getElementById('modal-class-date').textContent = classDate;
            document.getElementById('modal-class-time').textContent = classTime;
            document.getElementById('modal-class-students').textContent = classStudents;
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling behind modal
        });
    });
    
    // Close modal when X is clicked
    closeModal.addEventListener('click', function() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    });
    
    // Close modal when Close button is clicked
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
    
    // Highlight active section while scrolling
    const sections = document.querySelectorAll('.content-section');
    const navLinks = document.querySelectorAll('.sidebar-nav a');
    
    window.addEventListener('scroll', function() {
        let current = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            
            if (pageYOffset >= (sectionTop - 100)) {
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