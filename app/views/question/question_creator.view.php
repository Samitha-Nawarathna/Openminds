<?php

$title = 'Question creator';
$filename = 'question/question_creator';

include_once '../app/views/partials/header.view.php';

?>

<div class="form-wrapper">
        <h1 class="form-title">Ask Question</h1>
        <form action="<?= ROOT?>/question/create" method="POST" id="create-question-form">
            
            <div class="input-group">
                <input type="text" id="title" name="title" placeholder="Title" required>
            </div>

            <div class="input-group">
                <textarea id="content" name="content" placeholder="Content" rows="8" required></textarea>
            </div>

            <div class="input-group">
                <div id="tags-display-container"></div>
                <input type="text" id="tag-input" placeholder="Tags (type and press Enter)">
                <input type="hidden" name="tags" id="hidden-tags-input">
            </div>

            <button type="submit" class="btn-save">Save</button>

        </form>
    </div>

<script>
 
document.getElementById('create-question-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let tagsInput = document.getElementById('hidden-tags-input').value;
    let formData = {
        title: document.getElementById('title').value,
        content: document.getElementById('content').value,
        tags: tagsInput
    };
    
    fetch('<?= ROOT ?>/question/api_create_question', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            console.log('<?= ROOT ?>/question/show?id=' + data.question_id);
            window.location.href = '<?= ROOT ?>/question/show?id=' + data.question_id;
        } else {
            alert('Error creating question: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>

<?php

include_once '../app/views/partials/footer.view.php';

?>