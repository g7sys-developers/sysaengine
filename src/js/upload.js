/**
	* Este pojeto compõe a biblioteca do Amaengine para o Sysadmcom
	* pt-BR: App de sistemas do Google Cloud e VM Local
	*
	* Está atualizado para
	*    Javascript
	*
	* @package 		amaengine
	* @name 		upload
	* @version 		2.0.0
	* @copyright 	2021-2030
	* @author 		Anderson M Arruda < andmarruda at gmail dot com >
**/

class upload{
    constructor(target, multipleFiles, acceptExtensions, maxFileSize=5242880){
        this._denyExtensions = [
            '.exe',
            '.sh',
            '.dll',
            '.bat'
        ];
        this._maxFileSize = maxFileSize;

        target = target instanceof HTMLElement ? target : document.getElementById(target);
        if(target===null){
            console.log('Impossível prosseguir com o amaengine2.upload sem um target definido. Veja a documentação no Sysadmcom.');
            return;
        }

        this.formElement = target.closest('FORM');
        if(this.formElement===null){
            console.log('O target deve estar dentro de uma tag form! Veja a documentação no Sysadmcom.');
            return;
        }

        this.multipleFiles = multipleFiles;
        this.target = target;
        this.acceptExtensions = Array.isArray(acceptExtensions) ? acceptExtensions : null;
    }

    changeMaxFileSize(newSize){
        if(isNaN())
    }

    uiUpload(err, errText, file){

    }

    doUpload(event){
        for(let f of event.target.files){
            alert(f.size);
        }
    }

    ui(id, name, styles){
        this.inputFile = document.createElement('INPUT');
        this.inputFile.type = 'file';
        this.inputFile.setAttribute('id', id);
        this.inputFile.setAttribute('name', name);
        if(this.multipleFiles)
            this.inputFile.multiple = true;

        if(this.acceptExtensions !== null)
            this.inputFile.setAttribute('accept', this.acceptExtensions.join(','));

        if(typeof styles === 'object'){
            this.inputFileStyle = styles;
            Object.assign(this.inputFile.style, styles);
        }

        let obj = this;
        this.inputFile.addEventListener('change', (event) => {
            obj.doUpload(event);
        });

        this.target.appendChild(this.inputFile);
    }
}