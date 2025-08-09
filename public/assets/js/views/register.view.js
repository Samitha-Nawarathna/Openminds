let fields = document.querySelectorAll('.field input');
// console.log(fields);


function validate_email(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    let is_valid = emailRegex.test(email.trim());

    if (!is_valid)
    {
        return "Invalid email."
    }
    return ''
}

function validate_username(username) {
    return ''
}

function validate_password(password) {
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
    let is_valid = passwordRegex.test(password.trim());

    if (!is_valid)
        {
            return "Password must be 8+ chars, with upper, lower, number, special char."
        }
        return ''

}

function validate_confirm_password(confirm_password, original_password) {
    let is_valid = (confirm_password.trim() === original_password.trim());
    
    console.log(confirm_password, original_password, is_valid);

    if (!is_valid)
        {
            return "Does not match with password."
        }
        return ''
}

function update_style(element, validator)
{
    let name = element.getAttribute('name');
    element.addEventListener('input',() =>{

        if (name === "confirm_password") {
            let confirm_password = element.value;
            let password = '';
            let error = '';

            document.querySelectorAll('.field input').forEach(element => {
                if (element.getAttribute('name') === 'password') {
                    if(element.value)
                    {
                        password = element.value;
                    }
                }
            });

            error = validator(confirm_password, password);
            console.log(error);
        }else
        {
            let value = element.value;
            error = validator(value);
        }

        let group = element.closest('.input-group');
        let warning = group.closest('.field').querySelector('.error');
        // console.log(group, warning);

        if (error !== '') {
            group.style.borderColor = 'var(--color-error)';
            element.style.color = 'var(--color-error)';

            warning.style.display = 'block';
            warning.textContent = error;
        }else
        {
            group.style.borderColor = 'var(--color-primary)';
            element.style.color = 'var(--color-primary)';

            warning.style.display = 'none';
            warning.textContent = error;            
        }


    });

    return error;
}

let validators = {
    'email': validate_email,
    'username': validate_username,
    'password': validate_password,
    'confirm_password': validate_confirm_password
}

let error = '';

fields.forEach(element => {
    let name = element.getAttribute('name');

    error = update_style(element, validators[name]);


})

function is_empty_fields()
{
    let fields = document.querySelectorAll('.field input');
    let is_empty = false;
    fields.forEach(element => {
        // console.log(element.value);
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
        button.value = 'Fill all fields.';
        button.style.backgroundColor = 'var(--color-error)';
        // button.style.color = 'var(--color-error)';
    }
    else
    {
        if (error)
            {
                button.value = 'Problem with inputs.';
                button.style.backgroundColor = 'var(--color-error)';
                // button.style.color = 'var(--color-error)';
            }
            else
            {
                button.value = 'Register';
                button.style.backgroundColor = 'var(--color-primary)';
                button.disabled = false;
            }

    }
})

button.addEventListener('mouseleave', () => {
    button.value = 'Register';
    button.style.backgroundColor = 'var(--color-primary)';
    button.style.color = 'var(--color-surface)';
})