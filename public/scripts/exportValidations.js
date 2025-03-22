class Validations {

    aplicacao = '12 - sinal: -'
    resgate = '10 - sinal: +'
    movimento(params = [])
    {
        for (let item of params.entries()) {
            console.log(item[0], item[1])
        }

        return false
    }
}

export default Validations