const textarea = document.querySelector("form textarea");
const message_content = document.querySelector(".message-wrapper .message");
const message_wrapper = document.querySelector(".message-wrapper");

// CV Variables
const cvInput = document.getElementById('cvInput');
const cvWrapper = document.getElementById('cv-wrapper');
const cvFilename = document.getElementById('cv-filename');
const btnDeleteCv = document.getElementById('btn-delete-cv');

// Docs Variables
const docsInput = document.getElementById('docsInput');
const docsFileList = document.getElementById('docs-file-list');
const docsButtonText = document.getElementById('docs-button-text');

// Auto resize textarea
textarea.addEventListener("input", () => {
  textarea.style.height = "auto";
  textarea.style.height = textarea.scrollHeight + "px";
});

// --- CV Logic ---
cvInput.addEventListener('change', function () {
  const file = this.files[0];
  if (validateCV(file)) {
    cvFilename.innerText = file.name;
    cvWrapper.style.display = 'flex';
    message_wrapper.style.display = 'none';
  }
});

btnDeleteCv.addEventListener('click', function () {
  cvInput.value = "";
  cvWrapper.style.display = 'none';
});

// --- Supporting Docs Logic ---
// Keep track of accumulated files across multiple selections
let accumulatedFiles = [];

docsInput.addEventListener('change', function () {
  const newFiles = Array.from(this.files);

  if (newFiles.length > 0) {
    if (validateDocs(newFiles)) {
      // Add new files to the accumulation
      accumulatedFiles = [...accumulatedFiles, ...newFiles];

      // Update the input's files property to include all accumulated files
      updateDocsInput();

      // Render individual file items
      renderFileList();

      // Change button text after first file
      docsButtonText.textContent = "Add Another File";

      message_wrapper.style.display = 'none';
    }
  }
});

function renderFileList() {
  docsFileList.innerHTML = '';

  accumulatedFiles.forEach((file, index) => {
    const fileItem = document.createElement('div');
    fileItem.className = 'upload-wrapper';
    fileItem.style.display = 'flex';
    fileItem.style.marginBottom = '0';

    const fileNameDiv = document.createElement('div');
    fileNameDiv.className = 'input-group filename';
    fileNameDiv.textContent = file.name;

    const deleteBtn = document.createElement('button');
    deleteBtn.type = 'button';
    deleteBtn.className = 'button btn-none btn-delete';
    deleteBtn.textContent = 'delete';
    deleteBtn.onclick = () => removeFile(index);

    fileItem.appendChild(fileNameDiv);
    fileItem.appendChild(deleteBtn);
    docsFileList.appendChild(fileItem);
  });
}

function removeFile(index) {
  accumulatedFiles.splice(index, 1);
  updateDocsInput();
  renderFileList();

  // Reset button text if no files left
  if (accumulatedFiles.length === 0) {
    docsButtonText.textContent = "Upload Documents";
  }
}

function updateDocsInput() {
  const dt = new DataTransfer();
  accumulatedFiles.forEach(file => dt.items.add(file));
  docsInput.files = dt.files;
}

// --- Validation Helpers ---

function showError(msg) {
  message_content.innerText = msg;
  message_wrapper.style.display = 'block';
}

function validateCV(file) {
  if (!file) return false;

  if (file.type !== "application/pdf") {
    showError("CV must be a PDF file!");
    cvInput.value = "";
    return false;
  }

  if (file.size > 5 * 1024 * 1024) {
    showError("CV is too large! Max 5MB.");
    cvInput.value = "";
    return false;
  }
  return true;
}

function validateDocs(files) {
  const validTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];

  for (let file of files) {
    if (!validTypes.includes(file.type)) {
      showError(`File "${file.name}" is invalid type. Only PDF and Images allowed.`);
      return false;
    }
    if (file.size > 5 * 1024 * 1024) {
      showError(`File "${file.name}" is too large! Max 5MB.`);
      return false;
    }
  }
  return true;
}
