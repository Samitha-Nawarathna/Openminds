import * as config from  "../core/config.js"

const fileInput = document.getElementById("fileInput");
const preview = document.querySelector(".preview");
const upload_button = document.querySelector(".upload-button");
const upload_content = document.querySelector(".upload-wrapper");
const message_wrapper = document.querySelector(".message-wrapper");
const message_content = document.querySelector(".message-wrapper .message");
const send_button = document.querySelector(".submit");



upload_button.addEventListener("click", ()=>{
    upload_content.style.display = 'block';
})

fileInput.addEventListener("change", () => {
  const file = fileInput.files[0];
  
  let error = validate(file);

  if (!error)
  {
    preview.src = URL.createObjectURL(file);
    preview.style.display = "block";

    return;
  }

  message_content.innerText = error;
  message_wrapper.style.display = 'block';

});

function validate(file)
{
  if (!file) {
    return  "No file selected.";

  }

  if (!file.type.startsWith("image/")) {
    return "Please upload a valid image file.";
  }

  return '';
  
}

// function send_profile_data(file) {

//   const formData = new FormData();
//   formData.append("image", file);

//   fetch(config.ROOT + "ajax/set_profile_data", {
//     method: "POST",
//     body: formData
//   })
//   .then(res => res.json())
//   .then(data => {
//     if (!data.success)
//     {
//       console.log(data);
//       message_content.innerText = data.error;
//       message_wrapper.style.display = 'block';
//       return;
//     }

//     window.location.href = config.ROOT+"dashboard";
//   })
//   .catch(error)
//   {
//     message_content.innerText = "Upload failed (network error)" + error.message;
//     message_wrapper.style.display = 'block';
//     return;

//   };
// }

// send_button.addEventListener('click', ()=>{
//   const file = fileInput.files[0];
//   send_profile_data(file);
// })
