<?php
/**
	* Este pojeto compõe a biblioteca do Sysaengine
	* pt-BR: App de sistemas de validação de Email do Sysadmcom
	*
	* Está atualizado para
	*    PHP 8.0
	*
	* @package 		amaengine
	* @name 		sysaengine\validacaoEmail
	* @version 		1.0.0
	* @copyright 	2021-2030
	* @author 		Anderson M Arruda < andmarruda at gmail dot com >
**/
namespace sysaengine;

require_once(__DIR__. '/../phpmailer/vendor/autoload.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class validacaoEmail{
    /**
     * Quantidde de dígitos do token
     * var          string
     */
    const TOKEN_LEN = 32;

    /**
     * Id do email a ser utilizado
     * var          integer
     */
    const EMAIL_ID = 7;

    /**
	 * Mantém o acesso a conexão com o banco de dados
	 */
	protected $dbconn;

    /**
     * Flag se o email foi validado com sucesso
     */
    protected $flagEmailValidado = false;

    /**
     * DBname do banco de dados
     */
    protected $dbname;

    /**
     * description      Retorna o status do email validdo
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            
     * return           bool
     */
    public function getFlagEmailValidado() : bool
    {
        return $this->flagEmailValidado;
    }

    /**
     * description      Pega as informações do email para serem utilizadas
     * access           private
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            
     * return           array
     */
    private function dadosEmail() : array
    {
        $stmt = $this->dbconn->execute('SELECT * FROM development.cadastro_email WHERE id_email=?', [self::EMAIL_ID]);
        if($stmt->rowCount() == 0)
            return [];
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * description      Gera um token único para validação do email
     * access           private
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            
     * return           string
     */
    private function geraToken() : string
    {
        return substr(bin2hex(random_bytes(self::TOKEN_LEN)), 0, self::TOKEN_LEN);
    }

    /**
     * description      Construção da classe de email
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            ?string $dbname=NULL
     * return           void
     */
    public function __construct(?string $dbname=NULL)
    {
        $this->dbname = $dbname;
        $this->dbconn = sysa::cakeConn($dbname);
    }

    /**
     * description      Executa a validação do email
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            string $token
     * param            string $email
     * param            int $id
     * return           string
     */
    public function validarEmail(string $token, string $email, int $id, bool $producao) : string
    {
        $this->flagEmailValidado = false;
        $stmt = $this->dbconn->execute('SELECT *, validade_token > NOW() AS valido, TO_CHAR(validade_token, \'DD/MM/YYYY HH24:MI:SS\') AS validade_token_br FROM documentacao.validacao_por_email WHERE id_validacao_email=? AND validacao_token=? AND email=?', [$id, $token, $email]);
        if($stmt->rowCount() == 0)
            return 'Não foi possível encontrar o email para validar. Verifique a URL digitada caso o erro persista peça um novo convite.';

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $mensagem_token_expirado = $this->layoutMensagem($row['mensagem_token_expirado'], $row, $producao);
        $mensagem_sucesso = $this->layoutMensagem($row['mensagem_sucesso'], $row, $producao);
        if(!$row['valido'])
            return $mensagem_token_expirado;

        $this->dbconn->execute('UPDATE documentacao.validacao_por_email SET email_verificado=true, validado_em=NOW(), ip_validacao=?, user_agent_validacao=? WHERE id_validacao_email=? AND validacao_token=? AND email=?', [$_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $id, $token, $email]);
        $this->flagEmailValidado = true;
        return $mensagem_sucesso;
    }

    /**
     * description      Verifica se o email está válido pelo ID
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            int $id
     * return           boolean
     */
    public function emailValidado(int $id) : bool
    {
        $stmt = $this->dbconn->execute('SELECT * FROM documentacao.validacao_por_email WHERE id_validacao_email=?', [$id]);
        if($stmt->rowCount() == 0)
            return false;
        
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row['email_verificado'];
    }

    /**
     * description      Verifica se o token do email está expirado
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            int $id
     * return           boolean
     */
    public function tokenExpirado(int $id) : bool
    {
        $stmt = $this->dbconn->execute('SELECT *, validade_token > NOW() AS valido FROM documentacao.validacao_por_email WHERE id_validacao_email=?', [$id]);
        if($stmt->rowCount() == 0)
            return true;
        
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return !$row['valido'];
    }

    /**
     * description      Deleta requisição de validação de email com token expirado
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            int $id
     * return           boolean
     */
    public function deletaTokenExpirado(int $id) : bool
    {
        $stmt = $this->dbconn->execute('DELETE FROM documentacao.validacao_por_email WHERE id_validacao_email=? AND NOT email_verificado AND NOW() > validade_token RETURNING *', [$id]);
        if($stmt->rowCount() == 0)
            return false;

        return true;
    }

    /**
     * description      Altera o layout de string das mensagens
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            string $mensagem
     * param            array | int $dados
     * param            bool $producao
     * return           string
     */
    public function layoutMensagem(string $mensagem, $dados, bool $producao) : string
    {
        if(is_int($dados) || (!is_array($dados) && preg_match('/^[0-9]{1,}$/', $dados) > -1)){
            $stmt = $this->dbconn->execute('SELECT *, TO_CHAR(validade_token, \'DD/MM/YYYY HH24:MI:SS\') AS validade_token_br FROM documentacao.validacao_por_email WHERE id_validacao_email=?', [$dados]);
            if($stmt->rowCount()===0)
                return $mensagem;

            $dados = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        $vars = [
            'email' => $dados['email'],
            'mensagem_token_expirado' => $dados['mensagem_token_expirado'],
            'codemp' => $dados['codemp'],
            'url_validacao' => 'https://ws.sysadmcom.com.br/validarEmail.php?token='. $dados['validacao_token']. '&email='. $dados['email']. '&id='. $dados['id_validacao_email']. ((!$producao) ? '&f=0' : ''),
            'validade_token_br' => $dados['validade_token_br'],
            'validade_token' => $dados['validade_token'],
            'token' => $dados['validacao_token'],
            'G7sys_PATH' => (sysa::getUrlBase())
        ];

        $u = new utils();
        return $u->strTemplateEngine($mensagem, $vars);
    }

    /**
     * description      Deletar requisição de validação de email
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            int $id
     * return           bool
     */
    public function deletaValidacao(int $id) : bool
    {
        $stmt = $this->dbconn->execute('DELETE FROM documentacao.validacao_por_email WHERE id_validacao_email=? RETURNING *', [$id]);
        return $stmt->rowCount();
    }

    /**
	 * description 		Cria uma requisição de validação de email
	 * access 			public
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			string $titulo_email
	 * param 			string $mensagem
     * param 			string $email
     * param 			int $codigo_usuario
     * param 			int $mensagem_token_expirado
     * param 			int $codemp
     * param 			int $mensagem_sucesso
     * param            ?string $dbname=NULL
	 * return 			int
	 */
    public function requerer(string $titulo_email, string $mensagem, string $email, int $codigo_usuario, string $mensagem_token_expirado, int $codemp, string $mensagem_sucesso) : int
    {
        $sql = 'INSERT INTO documentacao.validacao_por_email(titulo_email, mensagem, email, validacao_token, validade_token, email_verificado, codigo_usuario, mensagem_token_expirado, codemp, mensagem_sucesso) VALUES(?, ?, ?, ?, NOW()+(SELECT tempo_expira_token_email FROM tabela110 WHERE codemp=?), false, ?, ?, ?, ?) RETURNING *, TO_CHAR(validade_token, \'DD/MM/YYYY HH24:MI:SS\') AS validade_token_br';
        $token = $this->geraToken();
        $stmt = $this->dbconn->execute($sql, [$titulo_email, $mensagem, $email, $token, $codemp, $codigo_usuario, $mensagem_token_expirado, $codemp, $mensagem_sucesso]);
        if($stmt->rowCount() == 0)
            return -1;
        
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $de = $this->dadosEmail();
        preg_match('/^.*(?=@)/', $de['username'], $fromName);
        preg_match('/^.*(?=@)/', $email, $toName);

        $titulo_email = utf8_decode($this->layoutMensagem($titulo_email, $row, ($this->dbname ?? $_SESSION['sysadmcom']['dbname'])=='ribeiraogg'));
        $mensagem = utf8_decode($this->layoutMensagem($mensagem, $row, ($this->dbname ?? $_SESSION['sysadmcom']['dbname'])=='ribeiraogg'));

        $mail = new PHPMailer(true);
        try {
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->isSMTP();
            $mail->Host       = $de['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $de['username'];
            $mail->Password   = $de['password_email'];
            $mail->SMTPSecure = $de['smtp_secure'];
            $mail->Port       = $de['port'];
            $mail->setFrom($de['username'], 'Sysadmcom');
            $mail->addAddress($email, $toName[0]);
            $mail->isHTML(true);
            $mail->Subject = $titulo_email;
            $mail->Body    = $mensagem;
            $mail->AltBody = $mensagem;

            $mail->send();
            return $row['id_validacao_email'];
        } catch (\Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        return -1;
    }
}
?>