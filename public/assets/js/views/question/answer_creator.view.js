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
        
        // --- MOCK AJAX CALL ---
        // You would replace this block with your actual AJAX implementation 
        // (using fetch or XMLHttpRequest) to send data to the server.
        
        console.log("[MOCK AJAX] Sending data to server...");

        form.submit();

        // --- END MOCK AJAX CALL ---
    });
});