class PrimeiroAcesso {
    static enviarFormulario(formulario) {
        let data = createPostData(formulario)
        let url_action = formulario.dataset.action

        fetch(url_action, {
            method: 'POST',
            body: data,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        }).then(response => {
            if (response.ok) {
                response.text()
                    .then(resp_text => {
                        let resposta_json = JSON.parse(resp_text)
                        console.log(resposta_json)
                        let msg_para_usuario = resposta_json.mensagem

                        if (resposta_json.result !== false) {
                            msg_para_usuario = msg_para_usuario.concat(' \n Você será redirecionado para a tela de login.')
                            if (confirm(msg_para_usuario)) {
                                 window.location.href = window.location.origin;
                            } else {
                                window.location.href = window.location.origin;
                            }
                        } else {
                            alert(msg_para_usuario)
                        }
                    })
            }
        })
    }
}

const formulario = document.querySelector('.submit-form-crud-ajax-pa')

formulario.addEventListener('submit', function(e) {
    e.preventDefault()
    PrimeiroAcesso.enviarFormulario(formulario)
})

function msgRetornoPrimeiroAcesso(ret) {
    console.log(ret)
}

document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.getElementsByClassName('navbar')[0]
    navbar.style.display = 'none'

    // Verificar correspondência de senhas
    const password = document.getElementById('password')
    const confirmPassword = document.getElementById('confirmPassword')
    const passwordMatch = document.querySelector('.password-match')
    const passwordMismatch = document.querySelector('.password-mismatch')

    function checkPasswordMatch() {
        if (password.value && confirmPassword.value) {
            if (password.value === confirmPassword.value) {
                passwordMatch.style.display = 'inline'
                passwordMismatch.style.display = 'none'
            } else {
                passwordMatch.style.display = 'none'
                passwordMismatch.style.display = 'inline'
            }
        } else {
            passwordMatch.style.display = 'none'
            passwordMismatch.style.display = 'none'
        }
    }

    password.addEventListener('input', checkPasswordMatch)
    confirmPassword.addEventListener('input', checkPasswordMatch)
})