<?php

$title = 'Question creator';
$filename = 'question/edit_question';

include_once '../app/views/partials/header.view.php';

?>

<?php




?>

<script>
    let tags = [];
    tags = <?php echo json_encode($data['tags']); ?>;
</script>

<div class="form-wrapper">
        <h1 class="form-title">Edit Question</h1>
        <form action="<?= ROOT?>/question/edit" method="POST" id="create-question-form">
            
            <input type="hidden" name="id" value="<?= htmlspecialchars($data['question']['id']) ?>">
            <div class="input-group">
                <input type="text" id="title" name="title" placeholder="Title" value = "<?= htmlspecialchars($data['question']['title']) ?>" required>
            </div>

            <div class="input-group">
                <textarea id="content" name="content" placeholder="Content" rows="8" required><?= htmlspecialchars($data['question']['content']) ?></textarea>
            </div>

            <div class="input-group">
                <div id="tags-display-container">
                    <!-- <?php foreach ($data['tags'] as $tag): ?>
                        <span class="tag"><?= htmlspecialchars($tag) ?> <span class="remove-tag" onclick="removeTag(this)">x</span></span>
                    <?php endforeach; ?> -->
                </div>
                <input type="text" id="tag-input" placeholder="Tags (type and press Enter)">
                <input type="hidden" name="tags" id="hidden-tags-input">
            </div>

            <button type="submit" class="btn-save">Save</button>

        </form>
    </div>

<script>
document.getElementById('create-question-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let formData = {
        id: document.querySelector('input[name="id"]').value,
        title: document.getElementById('title').value,
        content: document.getElementById('content').value,
        tags: document.getElementById('hidden-tags-input').value
    };
    
    fetch('<?= ROOT ?>/question/api_edit_question', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            window.location.href = '<?= ROOT ?>/question/show?id=' + formData.id;
        } else {
            alert('Error editing question: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>

<?php

include_once '../app/views/partials/footer.view.php';

?>