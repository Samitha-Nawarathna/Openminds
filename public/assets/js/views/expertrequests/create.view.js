const textarea = document.querySelector("form textarea");
const message_content = document.querySelector(".message-wrapper .message");
const message_wrapper = document.querySelector(".message-wrapper");


textarea.addEventListener("input", () => {
  textarea.style.height = "auto";
  textarea.style.height = textarea.scrollHeight + "px";
});

document.querySelector('.upload-box').addEventListener('change', function (e) {
  const file = document.querySelector('.upload-box').files[0];

  if (!file) {
    message_content.innerText = "Please select a file!";
    message_wrapper.style.display = 'block';

    // alert();
    e.preventDefault();
    return;
  }

  if (file.type !== "application/pdf") {
    message_content.innerText = "Only PDF files are allowed!";
    message_wrapper.style.display = 'block';    

    e.preventDefault();
    return;
  }

  if (file.size > 5 * 1024 * 1024) { // 5MB limit
    message_content.innerText = "File is too large! Max 5MB.";
    message_wrapper.style.display = 'block';  

    // alert("");
    e.preventDefault();
  }
});