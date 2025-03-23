class Validations {

    aplicacao = '12 - sinal: -'
    resgate = '10 - sinal: +'
    movimento(form_data)
    {
        let msg = Array()
        let categoria = form_data.get('idCategoria')
        let investimento = form_data.get('idContaInvest')
        let objetivo = form_data.get('idObjetivo')

        if (categoria == '' || categoria == null) {
            msg.push('Por favor, selecionar categoria.')
        } else {
            if ((categoria == this.aplicacao || categoria == this.resgate) && (investimento == '' || investimento == null)) {
                msg.push('Por favor, selecionar conta investimento.')
            }

            if (categoria == this.resgate && (objetivo == '' || objetivo == null)) {
                msg.push('Por favor, selecionar objetivo do investimento.')
            }

            if (categoria != this.resgate && categoria != this.aplicacao && investimento != '' && investimento != null) {
                msg.push('Por favor, limpar o campo Conta Invest, pois a categoria escolhida não necessita de conta investimento.')
            }
        }

        return msg
    }
}

export default Validations