/**
 * Classe para integração e manipulação de formulários do sysaengine.
 * Obs.: Evite utilizar as funções / métodos que começam com _ tendem a não se comportarem bem usados fora da classe.
 */

class sysaForm{
    constructor(form){
        this.formLoaded = false;
        form = typeof form === 'string' ? document.querySelector(form) : form;

        if(typeof form === 'object' && !(form instanceof HTMLFormElement)){
            console.log('Não é possível utilizar outras tags HTML como formulário.');
            return;
        }

        if(typeof form !== 'object'){
            console.log('Não é possível carregar o formulário. O objeto não é um fomrulário válido ou a query não é válida!');
            return;
        }

        this.form = new FormData(form);
        this.formElement = form;
    }

    loadData(data){
        if(typeof data !== 'object'){
            console.log('Não é possível carregar os dados. O parâmetro não é um objeto válido!');
            return;
        }

        for(let key in data){
            if(!data.hasOwnProperty(key))
                continue;

            if(this._loadIntoHtml(key, data[key])){
                if(this.form.has(key))
                    this.form.delete(key);

                this.form.append(key, data[key]);
            }
        }
    }

    _loadIntoHtml(id, value){
        let element = this.formElement.querySelector('#' + id);
        if(element instanceof HTMLInputElement || element instanceof HTMLSelectElement || element instanceof HTMLTextAreaElement){
            element.value = value;
            return true;
        }

        return false;
    }
}