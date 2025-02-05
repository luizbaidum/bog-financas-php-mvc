$('#id-modal-alerta').on('show.bs.modal', function (e) {
    $('#id-modal-form').modal('hide');
});

$(document).on('click', '.render-ajax', function (e) {
    let url_action = e.currentTarget.dataset.url;
    let div_destino = e.currentTarget.dataset.div;
    let modal = e.currentTarget.dataset.modal ?? false;

    requireAjaxRender(
        {
            action: url_action, 
            id_destino: div_destino, 
            modal: modal
        }
    );
});

$(document).on('click', '.ajax-ver-resultado', function (e) {
    let action = e.currentTarget.dataset.action;
    let modal = true;

    requireAjaxRender(
        {
            action: action, 
            modal: modal,
            method: 'GET'
        }
    );
});