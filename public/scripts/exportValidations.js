class Validations {

    aplicacao = ''
    resgate = ''
    msg = [];

    buscar_categorias_investimentos() {
        return fetch('/consultar-categorias-investimentos', {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        }).then(response => {
                if (!response.ok) {
                    throw new Error(`Erro HTTP: ${response.status}`);
                }
                return response.json();
            })
            .catch(error => {
                console.error('Falha ao buscar categorias:', error);
                throw error;
            });
    }

    async movimento(form_data) {
        try {
            let categoria = form_data.get('idCategoria');
            let investimento = form_data.get('idContaInvest');
            let objetivo = form_data.get('idObjetivo');
            let proprietario = form_data.get('idProprietario');
            let categorias_investimentos = await this.buscar_categorias_investimentos();

            this.aplicacao = categorias_investimentos.A;
            this.resgate = categorias_investimentos.RA;

            if (proprietario == '' || proprietario == null) {
                this.msg.push('Por favor, selecionar proprietário(a).');
            }

            if (categoria == '' || categoria == null) {
                this.msg.push('Por favor, selecionar categoria.');
            } else {

                let id_categoria_post = categoria.split(' - ')[0];

                if ((id_categoria_post == this.aplicacao || id_categoria_post == this.resgate) && (investimento == '' || investimento == null)) {
                    this.msg.push('Por favor, selecionar conta investimento.');
                }

                if (id_categoria_post == this.resgate && (objetivo == '' || objetivo == null)) {
                    this.msg.push('Por favor, selecionar objetivo do investimento.');
                }

                if (id_categoria_post != this.resgate && id_categoria_post != this.aplicacao && investimento != '' && investimento != null) {
                    this.msg.push('Por favor, limpar o campo Conta Invest, pois a categoria escolhida não necessita de conta investimento.');
                }
            }
        } catch (error) {
            console.error('Erro ao buscar categorias: ', error);
            this.msg.push('Erro ao carregar categorias de investimento.');
        }

        return this.msg;
    }

    async categoria(form_data) {
        try {
            let tipo = form_data.get('tipo');
            let categorias_investimentos = await this.buscar_categorias_investimentos();
            this.aplicacao = categorias_investimentos.A;
            this.resgate = categorias_investimentos.RA;

            if (tipo == 'A' && this.aplicacao) {
                this.msg.push('Já existe uma categoria destinada a Aplicações. Não é possível cadastrar outra.')
            }

            if (tipo == 'RA' && this.resgate) (
                this.msg.push('Já existe uma categoria destinada a Resgate de Aplicações. Não é possível cadastrar outra.')
            )
        } catch (error) {
            console.error('Erro ao buscar categorias: ', error);
            this.msg.push('Erro ao carregar categorias de investimento.');
        }

        return this.msg
    }
}

export default Validations