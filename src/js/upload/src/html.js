/**
 * Classe de controle de HTML da interface de usuário de upload de arquivos.
 * Obs.: Evite utilizar as funções / métodos que começam com _ tendem a não se comportarem bem usados fora da classe.
 */

class uploadHtml{
    constructor() {
        this._input;
        this._multiple=false;
        this._required=false;
        this._allowedImgTag = ['jpg', 'png', 'webp', 'bmp', 'jpeg', 'gif', 'svg'];
        this._loadedGallery = {
            'html': [],
            'grid2': []
        };
    }

    /**
     * Parse arquivos para a Grid 2
     * @return      void
     */
    galeriaGrid2(grid_id){
        if(typeof jsaa === 'undefined' || this._arquivosGaleria.length === 0)
            return;

        jsaa.grid_object[grid_id].set_grid_informations({
            "grid_id": grid_id,
            "datalist": [...this._arquivosGaleria]
        });

        this._loadedGallery.grid2.indexOf(grid_id) === -1 && this._loadedGallery.grid2.push(grid_id);
    }

    /**
     * Gera visual da galeria retornando uma string
     * @param       string size
     * @return      string
     */
    geraGaleria(htmlTarget){
        htmlTarget = htmlTarget instanceof HTMLElement ? htmlTarget : document.getElementById(htmlTarget);
        if(htmlTarget === null || this._arquivosGaleria.length === 0)
            return;

        console.log(this._arquivosGaleria);

        let html = '';
        for(let arquivo of this._arquivosGaleria){
            html += '<li>\
                <div class="btn-group" style="position:absolute; top:0; right:0;">\
                    <a class="btn btn-mini btn-inverse dropdown-toggle" data-toggle="dropdown" href="javascript: void(0);">\
                        <i class="icon-th icon-white"></i>\
                        <span class="caret"></span>\
                    </a>\
                    <ul class="dropdown-menu">\
                        <li><a tabindex="-1" href="'+ arquivo.bucket_url_base + arquivo.name_file +'" target="_blank">Visualizar</a></li>\
                        '+ (this._permiteExcluir ? '<li class="divider"></li>\
                        <li><a tabindex="-1" data-toggle="up2ExcluiArquivo" data-id="'+ arquivo.id_filecenter +'" href="#">Excluir</a></li>' : '') +'\
                    </ul>\
                </div>\
                <a href="'+ arquivo.bucket_url_base + arquivo.name_file +'" target="_blank" class="thumbnail">\
                    '+ (this._allowedImgTag.indexOf(arquivo.filetype) > -1 ? '<img src="'+ arquivo.bucket_url_base + arquivo.name_file +'" alt="'+ arquivo.filetype +'">' : '<div class="others-files">'+ arquivo.filetype +'</div>') +'\
                </a>\
            </li>';
        }

        htmlTarget.innerHTML = html;
        let elements = htmlTarget.querySelectorAll('a[data-toggle="up2ExcluiArquivo"]');
        if(elements.length === 0)
            return;

        let obj = this;
        for(let element of elements){
            element.addEventListener('click', (event) => {
                obj.deleteArquivo(event);
            });
        }

        this._loadedGallery.html.indexOf(htmlTarget) === -1 && this._loadedGallery.html.push(htmlTarget);
    }

    /**
     * Seta um input do html para ser controlado pela classe. Aceita somente input type file.
     * @param       HtmlInputElement input
     * @return      bool
     */
    setInput(input){
        if(!(input instanceof HTMLInputElement) || input.getAttribute('type').toLowerCase() !== 'file' || typeof this._input !== 'undefined')
            return false;

        this._input = input;
        this._multiple = input.multiple;
        this._required = input.required;
        let obj = this;
        this._input.addEventListener('change', () => obj.onSelectFiles());
        return true;
    }

    /**
     * Evento de validação ao selecionar algum arquivo. Posteriormente com possibilidade de callback.
     * @param       
     * @return      void
     */
    onSelectFiles()
    {
        this.verificaArquivosSelecionados();
    }

    /**
     * Retorna o HtmlInputElement setado anteriormente na classe. Pode retornar null caso não tenha nenhum input setado.
     * @param       
     * @return      HTMLInputElement | null
     */
    getInput()
    {
        if(!(this._input instanceof HTMLInputElement) || this._input.getAttribute('type').toLowerCase() !== 'file')
            return null;

        return this._input;
    }

    /**
     * Retorna se o HtmlInputElement setado anteriormente na classe aceita múltiplos arquivos.
     * @param       
     * @return      bool
     */
    isMultiple(){
        return this._multiple;
    }

    /**
     * Retorna se o HtmlInputElement setado anteriormente na classe é obrigatório.
     * @param       
     * @return      bool
     */
    isRequired(){
        return this._required;
    }
};