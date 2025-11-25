function is_empty_fields()
{
    let fields = document.querySelectorAll('.input-group input');
    let is_empty = false;
    fields.forEach(element => {
        console.log(element.value);
        if(!element.value)
        {
            is_empty = true;
            
        }
    })
    return is_empty;
}

function is_new_pword_match()
{
    let new_pword = document.querySelector('input[name="new_password"]').value;
    let confirm_pword = document.querySelector('input[name="confirm_new_password"]').value;

    console.log(new_pword, confirm_pword);

    return new_pword === confirm_pword;
}

let button = document.querySelector('form .button');
button.disabled = true;

button.addEventListener('mouseover', () => {
    if (is_empty_fields())
    {
        button.value = 'Fill all fields.';
        button.style.backgroundColor = 'var(--color-error)';
    }
    else if (!is_new_pword_match())
    {
        button.value = 'Confirm password does not match.';
        button.style.backgroundColor = 'var(--color-error)';
    }
    else
    {
        button.value = 'Update Password';
        button.style.backgroundColor = 'var(--color-primary)';
        button.disabled = false;            

    }
})

button.addEventListener('mouseleave', () => {
    button.value = 'Update Password';
    button.style.backgroundColor = 'var(--color-primary)';
})