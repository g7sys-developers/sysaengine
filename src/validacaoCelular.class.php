<?php
/**
	* Este pojeto compõe a biblioteca do Sysaengine
	* pt-BR: App de sistemas de validação de Celular do Sysadmcom
	*
	* Está atualizado para
	*    PHP 8.0
	*
	* @package 		amaengine
	* @name 		sysaengine\validacaoCelular
	* @version 		1.0.0
	* @copyright 	2021-2030
	* @author 		Anderson M Arruda < andmarruda at gmail dot com >
**/

namespace sysaengine;

class validacaoCelular{
    /**
	 * Mantém o acesso a conexão com o banco de dados
	 */
	protected $dbconn;

    /**
     * URL da api da KingSMS
     * @var             string
     */
    const URL_KINGSMS = 'http://painel.kingsms.com.br/kingsms/api.php';

    /**
     * Username da kingsms
     * @var             string
     */
    private $kingsms_login = 'g7bankpay';

    /**
     * Flag se o email foi validado com sucesso
     */
    protected $flagCelularValidado = false;

    /**
     * description      Retorna o status do celular validdo
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            
     * return           bool
     */
    public function getFlagCelularValidado() : bool
    {
        return $this->flagCelularValidado;
    }

    /**
     * description      cURL de envio de estímulo para a kingsms
     * access           private
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            
     * return           array
     */
    private function execCurl(string $url, array $data, bool $debug=false) : array
    {
        $url = $url. '?'. http_build_query($data);
        $c = curl_init();
        curl_setopt_array($c, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET'
        ]);
        $r = curl_exec($c);

        if($debug){
            $infos = curl_getinfo($c);
            $error = curl_error($c);
            echo '<pre>';
            var_dump($infos, $error);
            echo '</pre>';
        }

        curl_close($c);
        return json_decode($r, true);
    }

    /**
     * description      Verifica o saldo de SMS disponível
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            
     * return           array
     */
    public function saldoKingSms() : array
    {
        $get = ['acao' => 'saldo', 'login' => $this->kingsms_login, 'token' => sysa::getKingsmsToken()];
        $arr = $this->execCurl(self::URL_KINGSMS, $get, false);
        $stmt = $this->dbconn->prepare('INSERT INTO documentacao.saldo_kingsms(login, token_sms, status, cause) VALUES(?, ?, ?, ?) ON CONFLICT ON CONSTRAINT saldo_kingsms_ukey DO UPDATE SET status=EXCLUDED.status, cause=EXCLUDED.cause');
        $stmt->execute([$this->kingsms_login, sysa::getKingsmsToken(), $arr['status'], $arr['cause']]);
        return $arr;
    }

    /**
     * description      valida celular com ddd
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            string $cel
     * return           bool
     */
    public function verificaCelular(string $cel) : bool
    {
        $cel = preg_replace('/[^0-9]/', '', $cel);
        return preg_match('/^([14689][0-9]|2[12478]|3([1-5]|[7-8])|5([13-5])|7[193-7])9[0-9]{8}$/', $cel);
    }

    /**
     * description      Envia SMS pelo recurso da kingsms
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            string $numeroCelular
     * param            string $mensagem
     * return           bool
     */
    public function enviaKingSms(string $numeroCelular, string $mensagem, ?int $id_validacao_por_celular=NULL) : bool
    {
        $numeroCelular = preg_replace('/[^0-9]/', '', $numeroCelular);
        if(strlen($mensagem) > 160)
            return false;

        $get = ['acao' => 'sendsms', 'login' => $this->kingsms_login, 'token' => sysa::getKingsmsToken(), 'numero' => $numeroCelular, 'msg' => $mensagem];
        $ret = $this->execCurl(self::URL_KINGSMS, $get, false);

        if(isset($id_validacao_por_celular)) {
            $stmt = $this->dbconn->prepare('INSERT INTO documentacao.sms_validacao_celular(id_validacao_por_celular, status, cause, kingsms_id) VALUES(?, ?, ?, ?)');
            $stmt->execute([$id_validacao_por_celular, $ret['status'], $ret['cause'], ($ret['id'] ?? '-1')]);
        }
        
        $this->saldoKingSms();

        return true;
    }

    /**
     * description      Construção da classe de celular
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            ?string $dbname=NULL
     * return           void
     */
    public function __construct()
    {
        $this->dbconn = conn::get_conn();
    }

    /**
     * description      Verifica se o celular já foi validado
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            int $id_validacao_por_celular
     * return           bool
     */
    public function celularValidado(int $id_validacao_por_celular) : bool
    {
        $stmt = $this->dbconn->prepare('SELECT * FROM documentacao.validacao_por_celular WHERE id_validacao_por_celular=? AND celular_verificado');
        $stmt->execute([$id_validacao_por_celular]);
        return $stmt->rowCount();
    }

    /**
     * description      Verifica se a validação do celular já está expirada
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            int $id_validacao_por_celular
     * return           bool
     */
    public function celularExpirado(int $id_validacao_por_celular) : bool
    {
        $stmt = $this->dbconn->prepare('SELECT * FROM documentacao.validacao_por_celular WHERE id_validacao_por_celular=? AND NOT celular_verificado AND NOW()>validade_token');
        $stmt->execute([$id_validacao_por_celular]);
        return $stmt->rowCount();
    }

    /**
     * description      Deleta validação de celular com o token expirado
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            int $id_validacao_por_celular
     * return           bool
     */
    public function deletaCel(int $id_validacao_por_celular) : bool
    {
        $stmt = $this->dbconn->prepare('DELETE FROM documentacao.validacao_por_celular WHERE id_validacao_por_celular=? AND NOT celular_verificado AND NOW()>validade_token RETURNING *');
        $stmt->execute([$id_validacao_por_celular]);
        return $stmt->rowCount();
    }

    /**
     * description      Validação de SMS
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            int $id_validacao_por_celular
     * return           string
     */
    public function validar(int $id_validacao_por_celular, int $token, string $celular) : string
    {
        $this->flagCelularValidado=false;
        $cel = preg_replace('/[^0-9]{1,}/', '', $celular);
        $stmt = $this->dbconn->prepare('SELECT *, NOW()>validade_token AS token_expirado FROM documentacao.validacao_por_celular WHERE id_validacao_por_celular=? AND celular=? AND token_enviado=?');
        $stmt->execute([$id_validacao_por_celular, $cel, $token]);
        if($stmt->rowCount()==0){
            $this->flagCelularValidado=false;
            return 'O número '. $celular. ' não foi encontrado para ser validado';
        }

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($row['token_expirado']){
            $this->flagCelularValidado=false;
            return 'O token para o número '. $celular. ' já expirou. Tente novamente.';
        }

        $stmt = $this->dbconn->prepare('UPDATE documentacao.validacao_por_celular SET celular_verificado=true, ip_verificacao=?, user_agent_verificacao=?, data_verificacao=NOW() WHERE id_validacao_por_celular=? AND celular=? AND token_enviado=? RETURNING *');
        $stmt->execute([$_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $id_validacao_por_celular, $cel, $token]);
        $this->flagCelularValidado=true;
        return 'O número de celular '. $celular. ' foi válidado com sucesso.';
    }

    /**
     * description      Deleta validação de celular independente de estar validado, expirado etc...
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            int $id_validacao_por_celular
     * return           bool
     */
    public function deletaRequisicao(int $id_validacao_por_celular) : bool
    {
        $stmt = $this->dbconn->prepare('DELETE FROM documentacao.validacao_por_celular WHERE id_validacao_por_celular=? RETURNING *');
        $stmt->execute([$id_validacao_por_celular]);
        return $stmt->rowCount();
    }

    /**
     * description      Cria um pedido de verificação de celular
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            string $celular
     * param            int $codemp
     * param            int $codigo_usuario
     * return           int
     */
    public function requerer(string $celular, int $codemp, int $codigo_usuario, string $mensagem) : int
    {
        $token = rand(100000, 999999);
        $cel = preg_replace('/[^0-9]{1,}/', '', $celular);
        $sql = 'INSERT INTO documentacao.validacao_por_celular(celular, token_enviado, validade_token, codemp, codigo_usuario, mensagem) VALUES(?, ?, (NOW() + (SELECT tempo_expira_token_celular FROM tabela110 WHERE codemp=?)), ?, ?, ?) RETURNING *, TO_CHAR(validade_token, \'DD/MM/YYYY HH24:MI:SS\') AS validade_token_br';
        $stmt = $this->dbconn->prepare($sql);
        $stmt->execute([$cel, $token, $codemp, $codemp, $codigo_usuario, $mensagem]);
        if($stmt->rowCount() === 0)
            return -1;

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $vars = [
            'celular' => $celular,
            'celularNumeros' => $cel,
            'token' => $token,
            'validade_token_br' => $row['validade_token_br'],
            'validade_token' => $row['validade_token'],
        ];

        $u = new utils();
        $mensagem = $u->strTemplateEngine($mensagem, $vars);

        $this->enviaKingSms($cel, $mensagem, $row['id_validacao_por_celular']);

        //enviar mensagem sms por celular "AGUARDANDO DECISÃO GILMAR GUEDES E GABRIEL GUEDES - 21/03/2022 - 16:16"
        return $row['id_validacao_por_celular'];
    }
}
?>