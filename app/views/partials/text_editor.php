<link href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

<?php
// Define default values if variables are not set (for safety)
$editor_id = $editor_id ?? 'editor';
$initial_content = $initial_content ?? '{"ops":[{"insert":"Hello World!"}]}'; // Default Delta content
$is_read_only = $is_read_only ?? false;
$placeholder_text = $placeholder_text ?? 'Start writing your content here...';
$hidden_input_name = $hidden_input_name ?? 'quill_content_delta';

// Convert PHP boolean to JavaScript boolean
$js_read_only = $is_read_only ? 'true' : 'false';

// The following HTML/JS is the Quill editor setup
?>
<style>
  /* 🎨 Styling for the editor */
  #<?php echo $editor_id; ?> {
    height: 350px;
    background-color: <?php echo $is_read_only ? '#f7f7f7' : '#fff'; ?>;
  }
</style>

<form id="contentForm_<?php echo $editor_id; ?>" action="#" method="POST">
  <div id="<?php echo $editor_id; ?>"></div>
  <input type="hidden" name="<?php echo $hidden_input_name; ?>" id="hidden_input_<?php echo $editor_id; ?>">
  <br>
</form>

<script>
  // Configuration Variables passed from PHP
  const EDITOR_ID = "<?php echo $editor_id; ?>";
  const INITIAL_CONTENT = <?php echo $initial_content; ?>;
  const IS_READ_ONLY = <?php echo $js_read_only; ?>;
  const PLACEHOLDER_TEXT = "<?php echo $placeholder_text; ?>";
  const AUTOSAVE_INTERVAL_MS = 2000;
  const LOCAL_STORAGE_KEY = 'quill_autosave_' + EDITOR_ID;

  // Toolbar options
  const toolbarOptions = [
    ['bold', 'italic', 'underline'],
    ['blockquote', 'code-block'],
    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
    ['link', 'image', 'video', 'formula', 'code-block']
  ];

  // 🚀 Initialize Quill
  const quill = new Quill('#' + EDITOR_ID, {
    theme: 'snow',
    readOnly: IS_READ_ONLY, // Set read-only mode
    placeholder: PLACEHOLDER_TEXT, // Set placeholder
    modules: {
      toolbar: toolbarOptions,
      syntax: {
        highlight: text => hljs.highlightAuto(text).value
      },
      formula: true
    }
  });

  // Load initial or saved content
  function loadContent() {
    const savedContent = localStorage.getItem(LOCAL_STORAGE_KEY);
    if (savedContent) {
      try {
        const delta = JSON.parse(savedContent);
        quill.setContents(delta);
        console.log(`[${EDITOR_ID}] Loaded content from Local Storage.`);
        return;
      } catch (e) {
        console.error(`[${EDITOR_ID}] Error parsing saved content. Using initial content.`);
      }
    }
    // Load initial content passed from PHP if no saved content
    quill.setContents(INITIAL_CONTENT);
  }
  loadContent();


  // Autosave Functionality
  function saveContent() {
    const delta = quill.getContents();
    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(delta));
    console.log(`[${EDITOR_ID}] ✅ Autosaved content to Local Storage.`);
  }

  // Set up Autosave debounce listener only if not read-only
  if (!IS_READ_ONLY) {
    let autosaveTimer = null;
    quill.on('text-change', () => {
      clearTimeout(autosaveTimer);
      autosaveTimer = setTimeout(saveContent, AUTOSAVE_INTERVAL_MS);
    });
  }

// Initialize the global registry if it doesn't exist
window.quillSubmitPrep = window.quillSubmitPrep || {}; 

// Function to populate THIS editor's hidden input
function populateHiddenField_<?php echo $editor_id; ?>() {
  const hiddenInput = document.getElementById('hidden_input_' + EDITOR_ID);
  
  // Check if the editor is defined and not in read-only mode (optional performance check)
  if (typeof quill !== 'undefined' && !quill.options.readOnly) {
    const delta = quill.getContents();
    hiddenInput.value = JSON.stringify(delta);
    hiddenInput.setAttribute('value', delta);
    console.log(`[${EDITOR_ID}] Data preparation complete for submission.${hiddenInput.value}`);
  } else if (typeof quill !== 'undefined' && quill.options.readOnly) {
    console.log(`[${EDITOR_ID}] Skipping data prep: Read-Only mode.`);
  }
}

// Register this function globally using a unique key
window.quillSubmitPrep.<?php echo $editor_id; ?> = populateHiddenField_<?php echo $editor_id; ?>;
</script>