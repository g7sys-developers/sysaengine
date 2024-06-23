/**
 * Classe de interface de usuário de upload de arquivos.
 * Obs.: Evite utilizar as funções / métodos que começam com _ tendem a não se comportarem bem usados fora da classe.
 */

class upload extends uploadHtml{
    constructor() {
        super();
        let initialUrl = location.href.replace(/\?.*/g, '');
        initialUrl = initialUrl.substr(0, initialUrl.lastIndexOf('/')) + '/';
        
        this._initialUrl = initialUrl;
        this._urlBase = 'ajax/sysaengine/';
        this._verifyFile = 'pesquisa_dados_galeria.php';
        this._randomKey = '95BD84A89896826954ADF71A851F4';
        this._header = new Headers();
        this._header.append('Secuitykey', this._randomKey);

        this._extensoesProibidas = ['php', 'js', 'bat', 'sh', 'exe', 'com', 'reg', 'cmd', 'bin', 'csh', 'ksh', 'out', 'run'];
        this._maxSize = 20971520;
        this._extensaoPermitidas = [];
        this._erros = [];
        this._id_filecenter_gallery;
        this._filesInGallery = [];
        this._uploadFile = 'upload.php';
        this._uploadFileTemp = 'uploadTemp.php';
        this._loadFile = 'carrega_galeria.php';
        this._deleteFile = 'deleta_arquivo_gcloud.php';
        this._arquivosGaleria = [];
        this._uploadTemporario = false;
        this._permiteExcluir = false;
        this._callbacks = {
            'antesDeletar': [],
            'antesInserir': [],
            'depoisSelecionar': []
        };
    }

    /**
     * Pega o id da galeria atual
     * @return int | null
     */
    pegaIdGaleria = () => this._id_filecenter_gallery;

    /**
     * Permite a apresentação de permissão de exclusão de arquivos da galeria
     * @return void
     */
    permitirExcluirArquivos = () => this._permiteExcluir = true;

    /**
     * Seta o envio de arquivo(s) como arquivo temporário e que não será salvo no banco de dados, com isso o retorno passa a ser o nome do arquivo
     * @param       
     * @returns     void
     */
    uploadTemporario = () => this._uploadTemporario = true;

    /**
     * Verifica se a extensão passada através do parâmetro é uma extensão proíbida.
     * @param       string extensao
     * @return      bool
     */
    verificaExtensoesProibida = (extensao) => this._extensoesProibidas.indexOf(extensao) > -1;

    /**
     * Verifica se a extensão passada através do parâmetro é uma extensão permitida pelo programador.
     * @param       string extensao
     * @return      bool
     */
    verificaExtensoesEscolhidas = (extensao) => this._extensaoPermitidas.indexOf(extensao) > -1;

    /**
     * Seta callbacks para ações antes de deletar arquivos
     * @param       function func
     * @return      this
     */
    adicionaCallbackDelete = (func) => {
        if(typeof func !== 'function')
            return this;

        this._callbacks['antesDeletar'].push(func);
    }

    /**
     * Seta callbacks para ações antes de inserir arquivos
     * @param       function func
     * @return      this
     */
    adicionaCallbackInserir = (func) => {
        if(typeof func !== 'function')
            return this;

        this._callbacks['antesInserir'].push(func);
    }

    /**
     * Seta callbacks para ações depois de selecionar arquivos
     * @param       function func
     * @return      this
     */
    adicionaCallbackDepoisSelecionar = (func) => {
        if(typeof func !== 'function')
            return this;

        this._callbacks['depoisSelecionar'].push(func);
    }

    /**
     * Seta arquivos da galeria
     * @param       json
     * @return      void
     */
    setaArquivosGaleria = (obj) => {
        this._arquivosGaleria = [...obj];
        if(this._loadedGallery.html.length > 0){
            for(let html2 of this._loadedGallery.html){
                this.geraGaleria(html2);
            }
        }

        if(this._loadedGallery.grid2.length > 0){
            for(let g2 of this._loadedGallery.grid2){
                this.galeriaGrid2(g2);
            }
        }
    };

    /**
     * Deleta o arqiuvo da galeria pelo id
     * @param       int id_filecenter
     * @return      Promise
     */
    deleteArquivoPorId = async(id_filecenter) => {
        let dados = this._arquivosGaleria.find((element) => id_filecenter==element.id_filecenter);
        if(typeof dados === 'undefined'){
            console.log('O arquivo não foi carregado nessa galeria. Para deletá-lo o arquivo precisa estar carregado!');
            return;
        }

        if(this._callbacks['antesDeletar'].length > 0){
            for(let cb of this._callbacks['antesDeletar']){
                if(!cb(dados)){
                    console.log('O arquivo não pode ser deletado pois foi negado pela função abaixo.');
                    console.log(cb);
                    return;
                }
            }
        }

        this._header.delete('Content-Type') || null;
        this._header.append('Content-type', 'application/json');

        let f = await fetch(this._initialUrl + this._urlBase + this._deleteFile, {
            method: 'POST',
            headers: this._header,
            body: JSON.stringify(dados)
        });

        let j = await f.json();
        this.setaArquivosGaleria(j);
        return new Promise((resolve, reject) => {
            resolve(j);
        });
    };

    /**
     * Deleta o arquivo da galeria
     * @param       target
     * @return      Promise
     */
    deleteArquivo = async ({ target }) => {
        if(!confirm("Deseja deletar este arquivo?"))
            return;

        let id_filecenter = target.getAttribute('data-id');
        return this.deleteArquivoPorId(id_filecenter);
    }

    /**
     * Através do nome do arquivo retorna-se a sua extensão
     * @param       string filename
     * @return      string
     */
    pegaExtensaoArquivo = (filename) => filename.match(/(?<=\.)[a-zA-Z0-9]{2,}$/gi);

    /**
     * Retorna uma lista com todos os arquivos selecionados. Readonly
     * @param       
     * @return      array
     */
    pegaArquivos = () => this._input.files;

    /**
     * Retorna o valor do último erro ocorrido.
     * @param       
     * @return      string
     */
    pegaErros = () => this._erros;

    /**
     * Retorna a quantidade de arquivos selecionados no input type file
     * @param       
     * @return      int
     */
    contaArquivos = () => this._input.files.length;

    /**
     * Retorna o maxsize configurado na classe em bytes
     * @param       
     * @return      int
     */
    pegaMaxSize = () => this._maxSize;

    /**
     * Retorna a lista de extensões permitidas ao usuário.
     * @param       
     * @return      array
     */
     pegaExtensaoPermitida = () => this._extensaoPermitidas;

     /**
      * Limpa dados carregados de galeria e consequentemente permite a geração de novas galerias com a mesma instância
      * @param      
      * @return     this
      */
    unloadGaleria = () => {
        this._id_filecenter_gallery = null;
        this._arquivosGaleria = [];
    }

    /**
     * Carrega uma galeria que já existe
     * @param       int id_galeria
     * @returns     Promise | void - quando void deu erro de conexão no fetch
     */
    carregaGaleria = async (id_galeria) => {
        this._header.delete('Content-Type') || null;
        this._header.append('Content-type', 'application/json');

        let f = await fetch(this._initialUrl + this._urlBase + this._verifyFile, {
            method: 'POST',
            headers: this._header,
            body: JSON.stringify({'verificar': 'carregaGaleria', 'id_filecenter_gallery': id_galeria})
        });

        if(f.ok){
            let j = await f.json();
            this._id_filecenter_gallery = id_galeria;
            this.setaArquivosGaleria(j);
            return new Promise((resolve, reject) => {
                resolve(j);
            });
        }

        return;
    }

    /**
     * Seta um tamanho máximo de arquivo em byte. Não adianta extrapolar o limite do servidor pois o mesmo 
     * não irá obedecer a interface de usuário e sim as configurações propostas no server-side
     * @param       int sizeBit
     * @return      instanceof upload "this"
     */
    setaMaxSize = (sizeBit) => {
        if(/^[0-9]{1,}$/g.test(sizeBit))
            this._maxSize = sizeBit;

        return this;
    }

    /**
     * Seta extensões específicas a serem permitidas ao usuário.
     * @param       string extensao
     * @return      instanceof upload "this"
     */
    setaExtensaoPermitida = (extensao) => {
        extensao = extensao.toLowerCase();
        if(this._extensoesProibidas.indexOf(extensao) > -1){
            console.log("A extensão "+ extensao + " está na lista de extensões proíbidas! Essa extensão representa algum risco ao sistema e por isso não é permitida!");
            return;
        }

        this._extensaoPermitidas.push(extensao);
        return this;
    }

    /**
     * Verifica utilizando-se das regras da classe se algum dos arquivos selecionados ultrapassam o limite tamanho de arquivo,
     * se a extensão é proíbida ou se é permitida através das configurações do programador. Caso alguma das condicionais falhem
     * todos os arquivos são retirados do input. "FileList é readonly por isso somente da para remover todos."
     * @param       
     * @return      bool
     */
    verificaArquivosSelecionados = () => {
        this._erros = [];
        if(this._input.files.length == 0){
            this._erros.push('Nenhum arquivo selecionado.');
            return false;
        }

        let errFiles = [];
        for(let f of this._input.files){
            let ext = this.pegaExtensaoArquivo(f.name);
            if(f.size > this._maxSize || this._extensoesProibidas.indexOf(ext.join('')) > -1 || (this._extensaoPermitidas.length > 0 && this._extensaoPermitidas.indexOf(ext.join('')) === -1))
                errFiles.push(f.name);
        }

        if(errFiles.length > 0){
            let msg = "Arquivos com extensões inválidas ou tamanho do arquivo excedente.\n",
            msgFiles = errFiles.reduce((prev, cur) => prev + '\n' + cur);
            alert(msg+msgFiles);
            this._input.value = '';
            return false;
        }

        return true;
    }

    /**
     * Para enviar os arquivos através de um pipeline é necessário criar um FormData.
     * Essa função cria um FormData setando os arquivos a serem salvos no servidor Filestore do Gcloud e no Database.
     * @param       
     * @return      instanceof FormData
     */
    _geraFormData(){
        let fd = new FormData();
        fd.append('filesize_limit', this._maxSize);
        if(typeof this._id_filecenter_gallery !== 'undefined' && /^[0-9]{1,}$/g.test(this._id_filecenter_gallery))
            fd.append('id_galeria', this._id_filecenter_gallery);

        for(let f of this._input.files)
            fd.append('upload2Files[]', f);

        return fd;
    }

    /**
     * Faz as verificações necessárias dos arquivos e os envia posteriormente ao servidor causando um armazenamento no Gcloud FileStore e nos dados do Database. Retorna sempre um id_filecenter_gallery
     * @param       string bucketName
     * @return      JSON{id_filecenter_gallery: int, error: string|null}
     */
    async send(){
        if(!this.verificaArquivosSelecionados()){
            alert("Arquivos com extensões inválidas ou tamanho do arquivo excedente encontrados!");
            return null;
        }

        if(this.isRequired() && this.contaArquivos() === 0){
            alert('Selecione os arquivos para upload!');
            return null;
        }

        if(this._callbacks['antesInserir'].length > 0){
            for(let cb of this._callbacks['antesInserir']){
                if(!cb(this._input.files))
                    return;
            }
        }

        this._header.delete('Content-Type') || null;
        let fd = this._geraFormData();
        console.log(this._header);
        let f = await fetch(this._initialUrl + this._urlBase + (this._uploadTemporario ? this._uploadFileTemp : this._uploadFile), {
            method: 'POST',
            headers: this._header,
            body: fd
        });

        console.log(f.headers);

        if (f.status === 200) {
            let j = await f.json();
            if(j.upload){
                if(!this._uploadFileTemp)
                    this._id_filecenter_gallery = j.id_galeria;

                this.setaArquivosGaleria(j.dadosGaleria);
            }
            
            return j;
        }

        alert(await f.text());
        return {};
    }
};