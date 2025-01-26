addEventListener('popstate', () => {
    let url = window.location.href;
    window.location.href = url;
});

function sendAjaxRendering(occasional_options) {
    let default_options = $.extend(
        {
            action: null,
            metodo: 'POST',
            id_destino: null,
            dados: null,
            modal: false
        },
        occasional_options
    );

    $.ajax({
            type: default_options.metodo,
            url: default_options.action,
            data: default_options.dados,
            processData: false,
            contentType: false
        }).always(function(response) {

            if (default_options.modal) {

                let array_resposta = JSON.parse(response);

                let titulo = array_resposta.titulo;
                let texto_objeto = array_resposta.result[0];
                let texto = '';

                if (typeof(texto_objeto) == 'object') {
                    for (const [key, value] of Object.entries(texto_objeto))
                        texto += (`<b>${key}</b>: ${value} <br>`);
                }

                modalAlerta(titulo, texto);
            } else {
                /**
                 * Ação de pesquisa não altera o url no navegador.
                 */
                if (default_options.action.indexOf('pesquisar') == -1) {
                    let stateObj = {url: default_options.action};
                    history.pushState(stateObj, '', default_options.action);
                }

                if (typeof(response) == 'string') {
                    try {
                        document.getElementById(default_options.id_destino).innerHTML = JSON.parse(response);
                    } catch (error) {
                        console.log('Not a JSON returned.');
                        document.getElementById(default_options.id_destino).innerHTML = response;
                    }
                }
            }
        });
}
/****************************************************/
function sendAjaxOperations(occasional_options) {
    let default_options = $.extend(
        {
            action: null,
            metodo: "POST",
            dados: null,
            process_data: false,
            content_type: false,
            redirect: false,
            id_form: null,
            callback: null
        },
        occasional_options
    );

    if (default_options.redirect !== false && (default_options.redirect == null || default_options.redirect == undefined || default_options.redirect == true))
        default_options.redirect = getIndexPageUrl();

    $.ajax({
            type: default_options.metodo,
            url: default_options.action,
            data: default_options.dados,
            processData: default_options.process_data,
            contentType: default_options.content_type
        }).always(function(response) {
            let array_resposta = JSON.parse(response);

            if (array_resposta.result) {
                limparForm(default_options.id_form);

                if (default_options.callback == null || default_options.callback == '') {
                    if (default_options.redirect != false) {
                        setTimeout(() => {
                            window.scroll({
                                top: 0,
                                left: 0,
                                behavior: 'smooth',
                            });
                        }, 1000);
                    }
                } else {
                    window[default_options.callback](resposta);
                }
            } else {
                alert(array_resposta.mensagem);
            }
        });
}
/****************************************************/
function limparForm(id_form) {
    $(id_form).trigger("reset");
}
/****************************************************/
function returnToHome() {
    let destino = window.location.origin;
    window.location = destino + "/home";
}
/****************************************************/
function returnToIndex() {
    let url = window.location.pathname;
    let destino = url.split("-");
    window.location =  `/${destino[1]}`;
}
/****************************************************/
function modalAlerta(titulo, texto)
{
    $('#id-modal-alerta').modal('show');

    $('#id-modal-alerta .modal-title').text(titulo);
    $('#modal-alerta-conteudo').html(texto);
}

$("#id-modal-alerta").on('show.bs.modal', function (e) {
    $("#id-modal-form").modal("hide");
});
/****************************************************/
function getIndexPageUrl() {
    let url_atual = window.location.href;
    let url_destino = "";

    if (url_atual.split("-")[1] == undefined) {
        url_destino = url_atual;
    } else {
        if ((url_atual.split("-")[0]).indexOf("cadastrar") != -1)
            url_destino = (url_atual.split("-")[0]).replace("cadastrar", url_atual.split("-")[1]);

        if ((url_atual.split("-")[0]).indexOf("editar") != -1)
            url_destino = (url_atual.split("-")[0]).replace("editar", url_atual.split("-")[1]);
    }

    return url_destino;
}
/****************************************************/
$(document).on("submit", "#form-pesquisa", function (e) {
    e.preventDefault();

    const form_data = new FormData(this);
    let url_destino = this.dataset.url.split("-")[0];
    let div_destino = this.dataset.div;

    form_data.append("dao", this.dataset.url.split("-")[1]);

    sendAjaxRendering({action: url_destino, dados: form_data, id_destino: div_destino});
})
/****************************************************/
$(document).on('click', '.render-ajax', function (e) {
    let url_action = e.currentTarget.dataset.url;
    let div_destino = e.currentTarget.dataset.div;
    let modal = e.currentTarget.dataset.modal ?? false;
    sendAjaxRendering(
        {
            action: url_action, 
            id_destino: div_destino, 
            modal: modal
        }
    );
});