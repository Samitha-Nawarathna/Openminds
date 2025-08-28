const textarea = document.querySelector("form textarea");
const message_content = document.querySelector(".message-wrapper .message");
const message_wrapper = document.querySelector(".message-wrapper");
const upload_box = document.querySelector('.upload-box');
const upload_wrapper = document.querySelector('.upload-wrapper');
const delete_file_btn = document.querySelector(".upload-wrapper .btn-delete");

// auto resize textarea
textarea.addEventListener("input", () => {
  textarea.style.height = "auto";
  textarea.style.height = textarea.scrollHeight + "px";
});

// file upload handling
upload_box.addEventListener('change', function () {

  const file = document.querySelector('.upload-box').files[0];

  if (validate(file))
  {
    const fileName = file.name;

    document.querySelector('.upload-wrapper .filename').innerText = fileName;
    upload_wrapper.style.display = 'flex';
    // document.querySelector('.file-size').innerText = `File Size: ${fileSize} MB`;
    // document.querySelector('.file-type').innerText = `File Type: ${fileType}`;

    message_wrapper.style.display = 'none';
  }
});

//file delete handling
delete_file_btn.addEventListener('click', function () {
  document.querySelector('.upload-box').files = new DataTransfer().files;
  upload_wrapper.style.display = 'none';
  
});