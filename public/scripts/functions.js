addEventListener('popstate', () => {
    let url = window.location.href;
    window.location.href = url;
});

async function requireAjaxOperation(user_options) {
    let default_options = {
        action: null,
        method: 'POST',
        data: null,
        redirect: false,
        callback: null
    };

    let defined = Object.assign(default_options, user_options);
    let req = new XMLHttpRequest();

    req.open(defined.method, defined.action, true);
    req.send(defined.data);
    req.onload = function () {
        let resposta = responseTreatment(this);

        if (resposta != undefined) {
            if (defined.callback != null) {
                window[defined.callback](resposta);
            } else {
                let texto = resposta.mensagem;
                let titulo = 'Atenção!';

                if (resposta.result != null && resposta.result != false) {
                    titulo = 'Sucesso!';
                    limparForm(defined.id_form);
                }

                modalAlerta(titulo, texto);
            }
        }        
    }
}

async function requireAjaxRender(user_options) {
    let default_options = {
        action: null,
        method: 'POST',
        data: null,
        redirect: false,
        callback: null,
        modal: false
    };

    let defined = Object.assign(default_options, user_options);
    let req = new XMLHttpRequest();

    req.open(defined.method, defined.action, true);
    req.send(defined.data);
    req.onload = function () {
        console.log(this);
    }
}

function responseTreatment(response) {
    let resposta = Object();

    if (response.status === 200) {
        resposta = response.response;

        try {
            if (typeof(JSON.parse(resposta)) == 'object') {
                resposta = JSON.parse(resposta);
            }
        } catch (error) {
            console.log(error);
            modalAlerta('Atenção!', 'Houve um erro na sua solicitação.');
            return;
        }
    } else {
        resposta.mensagem = 'Forbidden || Not Found';
    }

    return resposta;
}

function limparForm(id_form) {
    $(id_form).trigger('reset');
}

function returnToHome() {
    let destino = window.location.origin;
    window.location = destino + "/home";
}

function returnToIndex() {
    let url = window.location.pathname;
    let destino = url.split("-");
    window.location =  `/${destino[1]}`;
}

function modalAlerta(titulo, texto) {
    $('#id-modal-alerta .modal-title').text(titulo);
    $('#modal-alerta-conteudo').html(texto);

    $('#id-modal-alerta').modal('show');    
}