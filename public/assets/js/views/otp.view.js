function is_empty_fields()
{
    let fields = document.querySelectorAll('.input-group input');
    is_empty = false;
    fields.forEach(element => {
        console.log(element.value);
        if(!element.value)
        {
            is_empty = true;
            
        }
    })
    return is_empty;
}

let button = document.querySelector('form .button');
button.disabled = true;

button.addEventListener('mouseover', () => {
    if (is_empty_fields())
    {
        button.value = 'Fill otp field.';
        button.style.backgroundColor = 'var(--color-error)';
    }
    else
    {
        button.value = 'Send';
        button.style.backgroundColor = 'var(--color-primary)';
        button.disabled = false;            

    }
})

button.addEventListener('mouseleave', () => {
    button.value = 'Send';
    button.style.backgroundColor = 'var(--color-primary)';
})