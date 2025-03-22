class Formatations {
    constructor (value) {
        this.valor = value;
    }

    convertToUS() 
    {
        try {
            let br = this.valor;
            let converting = br.replace(/\./g, '');
    
            let us = converting.replace(/,/g, '.');
    
            return us;
        } catch (e) {
            console.log('Error ->' + e);
        }
    }
}

$(document).on('keyup', '.numero-br', (e) => {
    let valor = $(e.target).val();

    valor = valor.replace(/\D/g, '');

    if (valor.length === 0) {
        valor = '000';
    } else if (valor.length === 1) {
        valor = '00' + valor;
    } else if (valor.length === 2) {
        valor = '0' + valor;
    }

    const parteInteira = valor.slice(0, -2).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    const parteDecimal = valor.slice(-2);
    valor = `${parteInteira},${parteDecimal}`;

    valor = valor.replace(/^0+(?=\d)/, '');

    $(e.target).val(valor);

    if (valor === 'NaN') {
        $(e.target).val('');
    }
})