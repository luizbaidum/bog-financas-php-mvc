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