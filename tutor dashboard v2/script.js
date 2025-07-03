document.addEventListener('DOMContentLoaded', () => {
    const editIcons = document.querySelectorAll('.edit-icon');
    const deleteIcons = document.querySelectorAll('.delete-icon');
    const editClassModal = document.getElementById('editClassModal');
    const confirmationModal = document.getElementById('confirmationModal');
    const messageModal = document.getElementById('messageModal'); // New message modal
    const messageText = document.getElementById('messageText'); // Text element for message modal
    const messageModalClose = document.getElementById('messageModalClose'); // Close button for message modal
    const messageModalOkButton = document.getElementById('messageModalOkButton'); // OK button for message modal

    const closeModalButtons = document.querySelectorAll('.close-button'); // All close buttons (x)
    const editClassForm = document.getElementById('editClassForm');
    const editClassId = document.getElementById('editClassId');
    const classNameInput = document.getElementById('className');
    const classTopicInput = document.getElementById('classTopic');
    const classMaterialsInput = document.getElementById('classMaterials');
    const yesCancelButton = document.getElementById('yesCancelButton');
    const noKeepButton = document.getElementById('noKeepButton');
    const createClassButton = document.querySelector('.create-class-button'); // Create Class button

    let classToDelete = null; // To store the card element to be deleted

    // Function to show the custom message modal
    function showMessageModal(message) {
        messageText.textContent = message;
        messageModal.style.display = 'flex';
    }

    // Event listener for Create Class button (placeholder)
    createClassButton.addEventListener('click', () => {
        showMessageModal('Create Class functionality coming soon!');
        // In a real application, you would open a form to add a new class here.
    });

    // Event listener for Edit icons
    editIcons.forEach(icon => {
        icon.addEventListener('click', (event) => {
            const classCard = event.target.closest('.class-card');
            const classId = classCard.dataset.classId;
            const title = classCard.querySelector('.class-title').textContent;
            const description = classCard.querySelector('.class-description').textContent;

            // Populate the form
            editClassId.value = classId;
            classNameInput.value = title;
            classTopicInput.value = description;
            // For materials, you'd typically fetch actual materials from a data source.
            // For this example, we'll leave it blank or pre-fill with a placeholder.
            classMaterialsInput.value = "Add materials here (e.g., PDF, Links)"; // Placeholder

            editClassModal.style.display = 'flex'; // Show the modal
        });
    });

    // Event listener for Delete icons
    deleteIcons.forEach(icon => {
        icon.addEventListener('click', (event) => {
            classToDelete = event.target.closest('.class-card');
            confirmationModal.style.display = 'flex'; // Show the confirmation modal
        });
    });

    // Event listener for "Yes, cancel" button
    yesCancelButton.addEventListener('click', () => {
        if (classToDelete) {
            classToDelete.remove(); // Remove the class card from the DOM
            classToDelete = null; // Reset
            confirmationModal.style.display = 'none'; // Hide the modal
            showMessageModal('Class cancelled successfully!'); // Use custom message modal
        }
    });

    // Event listener for "No, keep it" button
    noKeepButton.addEventListener('click', () => {
        classToDelete = null; // Reset
        confirmationModal.style.display = 'none'; // Hide the modal
    });

    // Close modals when clicking on the close button (x)
    closeModalButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Check which modal the close button belongs to
            if (button.closest('#editClassModal')) {
                editClassModal.style.display = 'none';
            } else if (button.closest('#confirmationModal')) {
                confirmationModal.style.display = 'none';
            }
            // The messageModalClose button is handled separately below
        });
    });

    // Close message modal when its 'x' button or 'OK' button is clicked
    messageModalClose.addEventListener('click', () => {
        messageModal.style.display = 'none';
    });

    messageModalOkButton.addEventListener('click', () => {
        messageModal.style.display = 'none';
    });


    // Close modals when clicking outside the modal content
    window.addEventListener('click', (event) => {
        // Check if the click target is the modal itself and not inside its content
        if (event.target === editClassModal) {
            editClassModal.style.display = 'none';
        }
        if (event.target === confirmationModal) {
            confirmationModal.style.display = 'none';
        }
        if (event.target === messageModal) {
            messageModal.style.display = 'none';
        }
    });

    // Handle Edit Class Form Submission
    editClassForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Prevent default form submission

        const updatedClassId = editClassId.value;
        const updatedClassName = classNameInput.value;
        const updatedClassTopic = classTopicInput.value;
        const updatedClassMaterials = classMaterialsInput.value; // Not used in UI update, but captured

        // Find the corresponding class card and update its content
        const classCardToUpdate = document.querySelector(`.class-card[data-class-id="${updatedClassId}"]`);
        if (classCardToUpdate) {
            classCardToUpdate.querySelector('.class-title').textContent = updatedClassName;
            classCardToUpdate.querySelector('.class-description').textContent = updatedClassTopic;
            // In a real application, you would send this data to a backend.
            console.log(`Class ID: ${updatedClassId}`);
            console.log(`Updated Class Name: ${updatedClassName}`);
            console.log(`Updated Topic: ${updatedClassTopic}`);
            console.log(`Updated Materials: ${updatedClassMaterials}`);
        }

        editClassModal.style.display = 'none'; // Hide the modal after submission
        showMessageModal('Class updated successfully!'); // Use custom message modal
    });
});
