document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('answer-form');

    // --- Mock form submission to show the data ---
    form.addEventListener('submit', (event) => {
        // Prevent the browser's default submission
        event.preventDefault();
        
        const formData = new FormData(form);
        const questionId = formData.get('question_id');
        const answerContent = formData.get('content');
        
        console.log("--- Answer Submission Data Ready for Backend ---");
        console.log(`Question ID: ${questionId}`);
        console.log(`Answer Content (Snippet): ${answerContent.substring(0, 50)}...`);
        
        
        
        setTimeout(() => {
            form.submit();
            alert("Answer submitted successfully! (Mock submission)");
            // Here you would typically redirect the user to the Question Viewer page
            // window.location.href = `/question/${questionId}`;
        }, 500);
        // --- END MOCK AJAX CALL ---
    });
});