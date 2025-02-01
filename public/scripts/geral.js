$('#id-modal-alerta').on('show.bs.modal', function (e) {
    $('#id-modal-form').modal('hide');
});

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

$(document).on('click', '.ajax-ver-resultado', function (e) {
    let url_action = '';
    let div_destino = '';
    let modal = true;
    sendAjaxRendering(
        {
            action: url_action, 
            id_destino: div_destino, 
            modal: modal
        }
    );
});