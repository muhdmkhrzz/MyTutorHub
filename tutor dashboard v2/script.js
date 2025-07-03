document.addEventListener('DOMContentLoaded', function() {
    // --- Data object for class details ---
    const classData = {
        "HCI - Unit 1": {
            topic: "Usability",
            date: "25 June 2025",
            time: "08:00–10:00",
            students: 40,
            material: "chapter 1.pdf",
            material_url: "path/to/chapter 1.pdf" // Placeholder: Replace with the actual file path
        },
        "Data Structure": {
            topic: "Grammar",
            date: "25 June 2025",
            time: "12:00–14:00",
            students: 35,
            material: "grammar v1.pdf",
            material_url: "path/to/grammar v1.pdf" // Placeholder: Replace with the actual file path
        },
        "AI - Theory 1": {
            topic: "Algorithm",
            date: "25 June 2025",
            time: "15:00–17:00",
            students: 25,
            material: "algorithm in AI.pdf",
            material_url: "path/to/algorithm in AI.pdf" // Placeholder: Replace with the actual file path
        }
    };

    // --- Smooth scrolling and active link highlighting ---
    document.querySelectorAll('.sidebar-nav a').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.sidebar-nav a').forEach(link => {
                link.classList.remove('active');
            });
            this.classList.add('active');
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
    
    // --- Class modal functionality ---
    const modal = document.getElementById('class-modal');
    const closeModal = document.querySelector('.close-modal');
    const closeBtn = document.querySelector('.close-btn');
    
    // Open modal when 'View Class' button is clicked
    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', function() {
            const classCard = this.closest('.class-card');
            const classTitle = classCard.querySelector('h3').textContent.trim();
            const data = classData[classTitle];
            
            if (data) {
                document.getElementById('modal-class-title').textContent = classTitle;
                document.getElementById('modal-class-topic').textContent = data.topic;
                document.getElementById('modal-class-date').textContent = data.date;
                document.getElementById('modal-class-time').textContent = data.time;
                document.getElementById('modal-class-students').textContent = data.students;
                
                const materialLink = document.getElementById('modal-material-link');
                materialLink.textContent = data.material + ' [Download]';
                materialLink.href = data.material_url;
                
                if (modal) {
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            } else {
                console.error("Error: No data found for class '" + classTitle + "'.");
            }
        });
    });
    
    // Function to close the modal
    function hideModal() {
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    // Event listeners to close the modal
    if (closeModal) {
        closeModal.addEventListener('click', hideModal);
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', hideModal);
    }
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            hideModal();
        }
    });
    
    // --- Highlight active section while scrolling ---
    const sections = document.querySelectorAll('.content-section');
    const navLinks = document.querySelectorAll('.sidebar-nav a');
    
    window.addEventListener('scroll', function() {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
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
