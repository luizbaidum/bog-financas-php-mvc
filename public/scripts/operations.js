$('.action-delete').click(async function() {

    let action = $(this).attr('data-url');
    let data = arraySelecteds();
    let redirect = true;

    if (data.entries().next().done) {
        modalAlerta('Atenção', 'Por favor, selecione ao menos um item para excluir.');
    } else {
        let r_confirmacao = await confirmacao();

        if (r_confirmacao)
            requireAjaxOperation({action, data, redirect})
    }
});

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