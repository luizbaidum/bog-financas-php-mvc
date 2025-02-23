addEventListener('popstate', () => {
    let url = window.location.href;
    window.location.href = url;
});

function requireAjaxOperation(user_options) {
    let default_options = {
        action: null,
        method: 'POST',
        data: null,
        redirect: false,
        callback: null,
        modal: false
    };

    let defined = Object.assign(default_options, user_options);

    fetch(defined.action, {
        method: defined.method,
        body: defined.data,
    })
    .then(response => {
        if (response.ok) {
            response.text()
            .then((text) => {
                let resposta = responseTreatment(text);

                if (resposta != undefined) {
                    if (defined.callback != null) {
                        window[defined.callback](resposta);
                    } else {
                        let texto = resposta.mensagem;
                        let titulo = 'Atenção!';

                        if (defined.modal == false && resposta.result != null && resposta.result != false) {
                            titulo = 'Sucesso!';
                            limparForm(defined.id_form);

                            modalAlerta(titulo, texto);
                        }

                        let id_modal_str = '#id-modal-conteudo';

                        if (defined.modal == 'false') {
                            if ($(id_modal_str).hasClass('show')) {
                                $(id_modal_str).modal('hide');                       
                            }
                        } else {
                            $(id_modal_str + ' .modal-content .card').html(texto);
                        }
                    }
                }
            })
        }
    })
}

function requireAjaxRender(user_options) {
    let default_options = {
        action: null,
        method: 'POST',
        data: null,
        callback: null,
        modal: false,
        div_destino: undefined
    };

    let defined = Object.assign(default_options, user_options);
    let req = new XMLHttpRequest();

    req.open(defined.method, defined.action, true);
    req.send(defined.data);
    req.onload = function () {
        let html = this.response;

        if (defined.modal) {
            $('#id-modal-conteudo .modal-content').html(html);
            $('#id-modal-conteudo').modal('show'); 
        } else {
            if (defined.div_destino == undefined) {

            } else {
                $(`#${defined.div_destino}`).html(html);
            }
        }
    }
}

function responseTreatment(response_text) {
    let resposta = Object();

    resposta = response_text;

    try {
        if (typeof(JSON.parse(resposta)) == 'object') {
            resposta = JSON.parse(resposta);
        }
    } catch (error) {
        console.log(error);
        modalAlerta('Atenção!', 'Houve um erro na sua solicitação.');
        return;
    }

    return resposta;
}

function limparForm(id_form) {
    $(`#${id_form}`).trigger('reset');
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

function insertOptions(select_element, options, comparator) {
    options.forEach(function(item, value) {

        if (item.idContaInvest == comparator) {
            let value = item.idObj;
            let text = item.nomeObj;
            let opt = document.createElement('option');

            opt.value = value;
            opt.innerHTML = text;
            select_element.appendChild(opt);
        }
    })
}

function removeOptions(select_element) {
    let i, L = select_element.options.length - 1;

    for (i = L; i >= 0; i--) {
        if (select_element[i].value != '') {
            select_element.remove(i);
        }
    }
}
