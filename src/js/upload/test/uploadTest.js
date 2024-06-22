describe('Sysaengine - Upload Test', () => {
    const u = new upload('sysadmcom', 'https://sysadmcom.com.br/sysadmcom/versoes/1.2.0.1/ajax/sysaengine/');

    function criaInput(id, type, multiple=false, required=false){
        let i = document.createElement('INPUT');
        i.type = type;
        i.id = id;
        i.required = required;

        if(multiple)
            i.multiple = multiple;

        return i;
    }

    //teste de input sem formulário
    it('Setar Input Type File Teste - TRUE', () => {
        let i = criaInput('campoUpload', 'file');
        u.setInput(i).should.be.eql(true);
    });

    //teste de validação se o input está sendo validade de forma correta
    it('Setar Input Type Texto Teste - FALSE', () => {
        let i = criaInput('campoUpload', 'text');
        u.setInput(i).should.be.eql(false);
    });

    //teste de input type file com possibilidade de envio de múltiplos arquivos
    it('Setar Input Type Multiple File Teste - TRUE', () => {
        let i = criaInput('campoUpload', 'file', true);
        u.setInput(i);
        u.isMultiple().should.be.eql(true);
    });

    //teste de input type file é obrigatório
    it('Setar Input Type File Required Teste - TRUE', () => {
        let i = criaInput('campoUpload', 'file', true, true);
        u.setInput(i);
        u.isRequired().should.be.eql(true);
    });

    //teste de tamanho máximo padrão de arquivos
    it('Tamanho padrão máximo de arquivos Teste - 20971520', () => {
        let i = criaInput('campoUpload', 'file', true, true);
        u.setInput(i);
        u.pegaMaxSize().should.be.eql(20971520);
    });

    //teste de mudança de tamanho máximo de arquivos
    let b = 2000000001;
    it('Tamanho padrão máximo de arquivos Teste - '+ b, () => {
        let i = criaInput('campoUpload', 'file', true, true);
        u.setInput(i);
        u.setaMaxSize(b);
        u.pegaMaxSize().should.be.eql(b);
    });

    //teste de permissão de tipo de arquivo
    it('Permissão de extensão de arquivo Teste - [\'jpg\', \'gif\']', () => {
        let i = criaInput('campoUpload', 'file', true, true);
        u.setInput(i);

        u.setaExtensaoPermitida('jpg')
         .setaExtensaoPermitida('gif');

        u.pegaExtensaoPermitida().should.be.eql(['jpg', 'gif']);
    });

    //teste de extensões permitidas com falha - pdf
    it('Verifica extensão sem permissão Teste - pdf', () => {
        let i = criaInput('campoUpload', 'file', true, true);
        u.setInput(i);
        u.setaExtensaoPermitida('jpg')
         .setaExtensaoPermitida('gif');

        u.verificaExtensoesEscolhidas('pdf').should.be.eql(false);
    });

    //testa verificação de extensões proíbidas
    it('Verificação de extensões proíbidas Teste - bat => true', () => {
        let i = criaInput('campoUpload', 'file', true, true);
        u.setInput(i);
        u.verificaExtensoesProibida('bat').should.be.eql(true);
    });

    //testa verificação de extensões proíbidas
    it('Verificação de extensões proíbidas Teste - exe => true', () => {
        let i = criaInput('campoUpload', 'file', true, true);
        u.setInput(i);
        u.verificaExtensoesProibida('exe').should.be.eql(true);
    });

    //testa verificação de extensões proíbidas
    it('Verificação de extensões proíbidas Teste - sh => true', () => {
        let i = criaInput('campoUpload', 'file', true, true);
        u.setInput(i);
        u.verificaExtensoesProibida('sh').should.be.eql(true);
    });

    //verificação de total de arquivos selecionados
    it('Conta total de arquivos selecionados', () => {
        let i = criaInput('campoUpload', 'file', true, true);
        u.setInput(i);
        u.contaArquivos().should.be.eql(0);
    });
});