$('.moeda').on('keyup', (e) => {

    let valor = $(e.target).val();

    valor = valor.replace(/[\D]+/g, '');
    valor = valor.replace(/([0-9]{2})$/g, ",$1");

    if (valor.length > 6) valor = valor.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");

    $(e.target).val(valor);

    if(valor == 'NaN') $(e.target).val('');
})
/****************************************************/
$('.input-data').keyup((e) => {

    var v=e.target.value.replace(/\D/g,"");

    v=v.replace(/(\d{2})(\d)/,"$1/$2");
    v=v.replace(/(\d{2})(\d)/,"$1/$2");
    e.target.value = v;
})
/****************************************************/
$(document).ready(function() {
    aplicarR$();
    $('.select2').select2();
})

function aplicarR$()
{
    $(document).find('.vlr-compra-peca').each((index, item) => {

        let vlr1 = $(item).text();
        let vlr2 = vlr1.replace('.',',').replace('R$ ', '');
        $(item).text('R$ ' + vlr2);
    });
}