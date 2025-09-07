var isLoading = false;

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

    startLoading();

    fetch(defined.action, {
        method: defined.method,
        body: defined.data,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(response => {
        if (response.ok) {
            response.text()
            .then((text) => {
                let resposta = responseTreatment(text);

                stopLoading();

                if (resposta != undefined) {
                    if (defined.callback != null) {
                        window[defined.callback](resposta);
                    } else {
                        let texto = resposta.mensagem;
                        let titulo = 'Atenção!';
                        let id_modal_str = '#id-modal-conteudo';

                        if (!defined.modal || defined.modal == 'false') {
                            if (resposta.result || resposta.result == 'true') {
                                titulo = 'Sucesso!';
                                limparForm(defined.id_form);
                                limparMovMensalVinculado();
                                modalAlerta(titulo, texto);
                            } else {
                                modalAlerta(titulo, texto);
                            }

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

    fetch(defined.action, {
        method: defined.method,
        body: defined.data,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(response => {
        if (response.ok) {
            response.text()
            .then(text => {
                let html = text;

                if (defined.modal) {
                    $('#id-modal-conteudo .modal-content').html(html);
                    $('#id-modal-conteudo').modal('show'); 
                } else {
                    if (defined.div_destino == undefined) {

                    } else {
                        $(`#${defined.div_destino}`).html(html);
                    }
                }
            })
        }
    })
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
    window.location = destino + '/home';
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

function deleteTr(botao) {
    let linha = botao.closest('tr');
    let tabela = linha.closest('table');

    tabela.deleteRow(linha.rowIndex);
}

function createPostData(formulario) {
    let post_data = new FormData();

    new FormData(formulario).forEach((value, key) => {
        post_data.append(key, value);
    });

    return post_data;
}

function confirmacao() {
    let r_confirmacao = confirm('Confirma exclusão?');
    return r_confirmacao;
}

function arraySelecteds() {

    let array_of_values = new FormData;

    let selected_selects = $('input[name="selectedData[]"]:checked').each(function () {
        array_of_values.append('itens[]', this.value)
    });

    return array_of_values;
}

function limparMovMensalVinculado() {
    try {
        $('#idNomeMovimento').val('');
        $('#idValor').val('');
        $('#idCategoria').val('');
        $('#idProprietario').val('');

        $('#id-content-return').html('');
    } catch (error) {
        console.log('Error -> ' + error)
    }
}

function startLoading() {
    isLoading = true;

    const loadingOverlay = document.getElementById('loadingOverlay');
    loadingOverlay.style.display = 'flex';

    document.addEventListener('click', preventClicks, true);
}

function stopLoading() {
    isLoading = false;

    const loadingOverlay = document.getElementById('loadingOverlay');
    loadingOverlay.style.display = 'none';

    document.removeEventListener('click', preventClicks, true);
}

function preventClicks(event) {
    if (isLoading) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();

        return false;
    }
}

function atualizarStatus(elemento_select) {
    let url = elemento_select.dataset.url;
    let novo_status = elemento_select.value

    requireAjaxOperation({
        action: url + '&status=' + novo_status
    })
}