function submitFormCrud() {
    let id_form = '#idform-crud';
    let action = $(id_form).attr('data-action');
    let dados = new FormData($("form[name='form-crud']")[0]);
    let redirect = $(id_form)[0].dataset.redirect;

    sendAjaxOperations({action, id_form, dados, redirect});
};
/****************************************************/
$('#excluir').click(async function() {

    let action = $(this).attr('data-action');
    let dados = arraySelecteds();
    let redirect = true;

    if (dados.entries().next().done) {
        modalAlerta('Atenção', 'Por favor, selecione ao menos um item para excluir.');
    } else {
        let r_confirmacao = await confirmacao();

        if (r_confirmacao)
            sendAjaxOperations({action, dados, redirect});
    }
});
/****************************************************/
function confirmacao() {
    let r_confirmacao = confirm('Confirma exclusão?');
    return r_confirmacao;
}
/****************************************************/
function arraySelecteds() {

    let array_of_values = new FormData;

    let selected_selects = $('input[name="item_selecionado"]:checked').each(function () {
        array_of_values.append('itens[]', this.value)
    });

    return array_of_values;
}