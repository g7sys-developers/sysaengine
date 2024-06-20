<?php
/**
	* Este pojeto compõe a biblioteca do Sysaengine
	* pt-BR: App de sistemas de Upload do Sysadmcom
	*
	* Está atualizado para
	*    PHP 8.0
	*
	* @package 		amaengine
	* @name 		sysaengine\upload
	* @version 		1.0.0
	* @copyright 	2021-2030
	* @author 		Anderson M Arruda < andmarruda at gmail dot com >
**/
namespace sysaengine;
use sysaengine\buckets\bucketInterface;

class upload {
	private $dependencias_js = [
		'js/upload/upload.min.js'
	];

	/**
	 * Path dos arquivos internos do Sysadmcom - Posteriormente será uma pasta temporária para download de arquivos do bucket do cloud storage
	 */
	const INTERNAL_PATH='/var/www/html/sysadmcom/versoes/upload/upload/';

	/**
	 * Path para os arquivos externos do Sysadmcom - Desde que gcloud = false
	 */
	const EXTERNAL_PATH='https://www.sysadmcom.com.br/sysadmcom/versoes/upload/upload/';

	/**
	 * Path para os arquivos temporários do Sysadmcom
	 */
	const INTERNAL_TEMP_PATH = '/var/www/html/sysadmcom/versoes/upload/upload/temp/';

	/**
	 * Informações sobre o UPLOAD ERROR CODE
	 */
	const UPL_ERR_MESSAGES = [
		UPLOAD_ERR_INI_SIZE => 'Upload bem sucedido',
		UPLOAD_ERR_INI_SIZE => 'Upload excede o limite das diretivas do servidor.',
		UPLOAD_ERR_FORM_SIZE => 'Upload excede o limite das diretivas do formulário',
		UPLOAD_ERR_PARTIAL => 'Upload do arquivo foi feito parcialmente.',
		UPLOAD_ERR_NO_FILE => 'Não foi enviado arquivos no upload.',
		UPLOAD_ERR_NO_TMP_DIR => 'Não existe pasta temporária para upload no PHP',
		UPLOAD_ERR_CANT_WRITE => 'Não tem permissão de escrita na pasta temporária do PHP',
		UPLOAD_ERR_EXTENSION => 'Não foi possível receber o arquivo na extensão enviada.'
	];

	/**
	 * Extensões de arquivos não permitidas
	 */
	const EXT_PROIBIDAS = ['php', 'js', 'bat', 'sh', 'exe', 'com', 'reg', 'cmd', 'bin', 'csh', 'ksh', 'out', 'run'];

	/**
	 * Mantém o acesso a conexão com o banco de dados
	 */
	protected $dbconn;

	/**
	 * Variáveis de informações e debug da classe
	 */
	protected $infos=[
		'lastError' => NULL
	];

	/**
	 * Lista de erro de arquivos do upload
	 */
	protected $fileErrList = [];

	/**
	 * description 		Constrói a classe passando o nome do banco de dados conectado
	 * access 			public
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 				string $dbname
	 * param 				string $bucketName
	 * return 			void
	 */
	public function __construct(bucketInterface $bucket)
	{
		$this->dbconn = conn::get_conn();
	}

	/**
	 * description 		Verifica se ocorreu erro no upload do arquivo
	 * access 			protected
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			int $err
	 * return 			bool
	 */
	protected function verificaErroUpload(int $err) : bool
	{
		return $err > 0;
	}

	/**
	 * description 		Pega mensagem de erro do upload do arquivo
	 * access 			public
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			int $err
	 * return 			string
	 */
	public function msgErroUpload(int $err) : string
	{
		return self::UPL_ERR_MESSAGES[$err] ?? 'Erro desconhecido com o número '. $err;
	}

	/**
	 * description 		Verifica as extensões proíbidas
	 * access 			protected
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			string $filename
	 * return 			bool
	 */
	protected function verificaExtensoesProibidas(string $filename) : bool
	{
		preg_match('/(?<=\.)[a-zA-Z0-9]{2,}$/', $filename, $matchs);
		return in_array($matchs[0], self::EXT_PROIBIDAS);
	}

	/**
	 * description 		Verifica o tamanho máximo do arquivo no upload
	 * access 			public
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			int $filesize
	 * return 			bool
	 */
	public function checaFileSize(int $filesize) : bool
	{
		return (is_null($this->maxSize) || $filesize <= $this->maxSize);
	}

	/**
	 * description 		Verificação de dados para a variável de informações d class
	 * access 			private
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			string $varName
	 * return 			never
	 */
	public function existeEmInfos(string $varName) : void
	{
		if(!array_key_exists($varName, $this->infos))
			throw new \Exception('Não é possível setar dados para uma variável não esperada com o nome '. $varName. ' verifique a ortografia e tente novamente!');
	}

	/**
	 * description 		Seta variáveis de infos da class upload
	 * access 			public
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com>
	 * param 			string $varName
	 * param 			mixed $val
	 * return 			void
	 */
	public function __set(string $varName, $val)
	{
		$this->existeEmInfos($varName);
		$this->infos[$varName] = $val;
	}

	/**
	 * description 		Pega variáveis setadas nas infos da classe
	 * access 			public
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			string $varName
	 * return 			mixed
	 */
	public function __get(string $varName)
	{
		$this->existeEmInfos($varName);
		return $this->infos[$varName];
	}

	/**
	 * description 		Retorna informações do arquivo
	 * access 			private
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			int $id_filecenter
	 * return 			array
	 */
	private function pegaDadosArquivo(int $id_filecenter) : ?array
	{
		$sql = 'SELECT 
				df.*, dgb.bucket_name, dgb.bucket_url_base, dgb.bucket_public, dgb.sysadmcom_bucket
			FROM 
				development.filecenter df
				LEFT JOIN development.gcloud_bucket dgb USING(id_gcloud_bucket)
			WHERE df.id_filecenter=?
		';

		$stmt = $this->dbconn->execute($sql, [$id_filecenter]);
		if($stmt->rowCount() === 0){
			$this->lastError = 'Arquivo com o id '. $id_filecenter. ' não foi localizado';
			return null;
		}

		$file=$stmt->fetch(\PDO::FETCH_ASSOC);

		if(!$file['file_exists']){
			$this->lastError = 'O arquivo '. $file['name_file']. ' não foi encontrado em nossos discos! Utilize o analisador de uploads para verificar o que pode ter ocorrido!';
			return null;
		}

		return $file;
	}

	/**
	 * description 		Pega a URL do arquivo http | https
	 * access 			public
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			int $id_filecenter
	 * return 			?string
	 */
	public function pegaUrlArquivoExterno(int $id_filecenter) : ?string
	{
		$file = $this->pegaDadosArquivo($id_filecenter);
		if(is_null($file))
			return null;

		if($file['is_gcloud_storage'] && !$file['bucket_public']){
			$this->lastError = 'Somente o proprietário do arquivo poderá acessá-lo. O bucket não é público. Para acessar o arquivo e mostrá-lo ao usuário do sistema utilize a função pegaBytesArquivo.';
			return null;
		}

		return is_null($file['bucket_url_base']) ? self::EXTERNAL_PATH. $file['name_file'] : $file['bucket_url_base']. $file['name_file'];
	}

	/**
	 * descritpin 		Verifica se um arquivo pertence a uma galeria
	 * access 			public
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			int $id_filecenter
	 * return 			bool
	 */
	public function arquivoEmGaleria(int $id_filecenter) : bool
	{
		$f = $this->pegaDadosArquivo($id_filecenter);
		return !is_null($f['id_filecenter_gallery']);
	}

	/**
	 * description 		Pega o PATH interno de um arquivo pelo id_filecenter ou retorna null em caso do arquivo estar em fontes externas
	 * access 			public
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			int $id_filecenter
	 * return 			string | null
	 */
	public function pegaCaminhoArquivo(int $id_filecenter) : ?string
	{
		$file = $this->pegaDadosArquivo($id_filecenter);
		if(is_null($file))
			return null;

		if($file['is_gcloud_storage']){
			$this->lastError = 'Arquivo armazenado externamente. Utilize a função pegaArquivoExterno ou pegaUrlArquivoExterno para acessar esse arquivo.';
			return null;
		}

		return self::INTERNAL_PATH. $file['name_file'];
	}

	/**
	 * Transfere um arquivo de dentro da VM do Sysadmcom para um servidor externo. "após o envio e confirmação de envio o mesmo é deletado do servidor local!"
	 * access 			public
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			
	 * return 			bool
	 */
	public function transfereParaGcloud(int $id_filecenter, bool $deleteFile=true) : bool
	{
		$file = $this->pegaDadosArquivo($id_filecenter);
		//var_dump(self::INTERNAL_PATH. $file['name_file']);die;
		if(!is_null($file['id_filecenter_gallery'])){
			$this->lastError = 'Esse método somente transfere arquivos para o Gcloud. Para transferir uma galeria utilize o comando transfereGaleriaParaGcloud';
			return false;
		}

		if($file['is_gcloud_storage']){
			$this->lastError = 'Arquivo já foi transferido anteriormente para o Google Cloud.';
			return false;
		}
		$uploaded = $this->upload(self::INTERNAL_PATH. $file['name_file']);
		if($this->verificaTransferenciaGcloud($uploaded)){
			$this->updateGcloudFilecenter($id_filecenter);
			if($deleteFile)
				unlink(self::INTERNAL_PATH. $file['name_file']);
			return true;
		}

		return false;
	}

	/**
	 * description 			Verifica a transfêrencia de arquivo para o Gcloud
	 * access 				public
	 * version 				1.0.0
	 * author 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				
	 * return 				bool
	 */
	private function verificaTransferenciaGcloud(?\Google\Cloud\Storage\StorageObject $uploaded) : bool
	{
		if(is_null($uploaded)){
			$this->lastError = 'Ocorreu um erro inesperado! Por favor tente novamente mais tarde! Verifique os logs de erro do apache.';
			return false;
		}

		if($uploaded->exists()){
			return true;
		}

		$this->lastError = 'Ocorreu um erro inesperado! Por favor tente novamente mais tarde! Verifique os logs de erro do apache.';
		return false;
	}

	/**
	 * Verifica se o nome já existe no bucket e no filecenter
	 * access 			private
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			string $filename
	 * return 			boolean
	 */
	public function nomeExiste(string $filename) : bool
	{
		$sql = 'SELECT COUNT(*) AS total FROM development.filecenter WHERE name_file=?';
		$stmt = $this->dbconn->execute($sql, [$filename]);
		if($stmt->rowCount() === 0)
			return false;

		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		if($row['total'] === 0)
			return false;

		if($this->bucketName != '' && !$this->gcloudArquivoExiste($filename))
			return false;

		return true;
	}

	/**
	 * Gera um novo nome de arquivo e valida se o nome de arquivo já existe no bucket e no filecenter
	 * access 			public
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			string $originalName
	 * return 			string
	 */
	public function geraNome(string $originalName) : string
	{
		$name = microtime(). $originalName;
		$name = str_replace(' ', '', $name);
		while($this->nomeExiste($name)){
			$rand = rand(0, getrandmax());
			$name = microtime(). $rand. $originalName;
			$name = str_replace(' ', '', $name);
		}
		return $name;
	}

	/**
	 * Download de arquivo e atualização no bucket do Gcloud
	 * access 			public
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			string $url
	 * param 			int $id_filecenter
	 * return 			bool
	 */
	public function updateDownloadToGcloud(string $url, int $id_filecenter) : bool
	{
		$dados = $this->pegaDadosArquivo($id_filecenter);
		if(!is_null($dados['id_filecenter_gallery'])){
			$this->lastError = 'Esse método somente atualiza um arquivo no Gcloud. Para atualizar uma galeria ainda está em construção!';
			return false;
		}

		if(!$dados['is_gcloud_storage']){
			$this->lastError = 'Este arquivo não está presente no Gcloud Bucket. Não é possível atualizar um arquito do Bucket que não está no Bucket';
			return false;
		}

		$c = curl_init();
		curl_setopt_array($c, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => 'GET'
		]);
		$r = curl_exec($c);
		$infos = curl_getinfo($c);
		curl_close($c);
		if($infos['http_code'] != '200'){
			$this->lastError = 'Não foi possível fazer o download do arquivo. Status da URL '. $infos['http_code']. ' - '. $url;
			return false;
		}

		$pdf = file_get_contents($url);
		$this->delete($dados['name_file']);
		file_put_contents(self::INTERNAL_PATH. $dados['name_file'], $pdf);
		$this->upload(self::INTERNAL_PATH. $dados['name_file']);
		unlink(self::INTERNAL_PATH. $dados['name_file']);
		return true;
	}

	/**
	 * Download de arquivo e transfere pro Gcloud
	 * access 			public
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			string $url
	 * param 			string $originalName
	 * return 			int
	 */
	public function downloadToGcloud(string $url, string $originalName, int $codigo_usuario) : int
	{
		$pdf = file_get_contents($url);
		$name = $this->geraNome($originalName);
		file_put_contents(self::INTERNAL_PATH. $name, $pdf);
		$size = filesize(self::INTERNAL_PATH. $name);
		$this->upload(self::INTERNAL_PATH. $name);
		unlink(self::INTERNAL_PATH. $name);

		return $this->insereFilecenter($name, $originalName, $size, $codigo_usuario);
	}

	/**
	 * Cria a linha de controle do filecenter para uploads do Gcloud
	 * access 			private
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			string $name
	 * param 			string $original
	 * param 			float $size
	 * param 			int $codigo_usuario
	 * return 			int
	 */
	private function insereFilecenter(string $name, string $original, float $size, int $codigo_usuario, ?string $temporaryFinalDate=NULL, ?int $id_filecenter_gallery=NULL) : int
	{
		$infos = $this->bucketInfoByName($this->bucketName);
		$sql = 'INSERT INTO development.filecenter (name_file, original_filename, file_size, codigo_usuario_insert, is_gcloud_storage, id_gcloud_bucket, temporary_final_date, id_filecenter_gallery) VALUES(?, ?, ?, ?, ?, ?, ?, ?) RETURNING *';
		$stmt = $this->dbconn->execute($sql, [$name, $original, $size, $codigo_usuario, true, $infos['id_gcloud_bucket'], $temporaryFinalDate, $id_filecenter_gallery]);
		if($stmt->rowCount()===0)
			return -1;

		$row=$stmt->fetch(\PDO::FETCH_ASSOC);
		return $row['id_filecenter'];
	}

	/**
	 * Deleta arquivo do filecenter
	 * access 			private
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			int $id_filecenter
	 * return 			bool
	 */
	private function deletaFilecenter(int $id_filecenter) : bool
	{
		$sql = 'WITH delFilecenter AS (DELETE FROM development.filecenter WHERE id_filecenter=? RETURNING *) SELECT * FROM delFilecenter';
		$stmt = $this->dbconn->execute($sql, [$id_filecenter]);
		if($stmt->rowCount()===0)
			return false;

		return true;
	}

	/**
	 * Deleta arquivo do bucket do Google pelo id do filecenter
	 * access 			public
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			int $id_filecenter
	 * return 			bool
	 */
	public function deletaGcloudPorId(int $id_filecenter) : bool
	{
		$dados = $this->pegaDadosArquivo($id_filecenter);
		if(is_null($dados))
			return false;

		if(!$this->gcloudArquivoExiste($dados['name_file']))
			return false;

		$deldb = $this->deletaFilecenter($id_filecenter);
		if($deldb)
			return $this->delete($dados['name_file']);

		return false;
	}

	/**
	 * Updata informações do filecenter para transferir para o Google Cloud
	 * access 			private
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			int $id_filecenter
	 * return 			void
	 */
	private function updateGcloudFilecenter(int $id_filecenter) : void
	{
		$sql = 'UPDATE development.filecenter SET temporary_final_date=NULL, is_gcloud_storage=TRUE, id_gcloud_bucket=? WHERE id_filecenter=?';
		$this->dbconn->execute($sql, [$this->bucketInfo['id_gcloud_bucket'], $id_filecenter]);
	}

	/**
	 * description 			Pega a url externa do arquivo num bucket do Gcloud
	 * access 				public
	 * version 				1.0.0
	 * author 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				int $id_filecenter
	 * return 				?string
	 */
	public function pegaUrlArquivo(int $id_filecenter) : ?string
	{
		$f=$this->pegaDadosArquivo($id_filecenter);
		$url = self::EXTERNAL_PATH;
		if($f['is_gcloud_storage']){
			$b=$this->bucketInfoById($f['id_gcloud_bucket']);
			if(!$b['bucket_public']){
				$this->lastError = 'Para acessar arquivos de bucket não público utilize a função neverArquivoBucket';
				return null;
			}

			$url = $b['bucket_url_base'];
		}
		return $url.$f['name_file'];
	}

	

	/**
	 * description 			Upload de arquivo único para o Google Bucket
	 * access 				public
	 * version 				1.0.0
	 * author 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				array $arquivo
	 * param 				int $codigo_usuario
	 * return 				?int
	 */
	public function uploadArquivoUnicoGcloud(array $arquivo, int $codigo_usuario, ?int $id_galeria=NULL) : ?int
	{
		$nome = $this->geraNome($arquivo['name']);
		$path = self::INTERNAL_PATH. $nome;
		if(@move_uploaded_file($arquivo['tmp_name'], $path)){
			$size = filesize($path);
			$uploaded = $this->upload($path);
			if($this->verificaTransferenciaGcloud($uploaded)){
				$id = $this->insereFilecenter($nome, $arquivo['name'], $size, $codigo_usuario);
				unlink($path);
				return $id;
			}

			return null;
		}

		return null;
	}

	/**
	 * description 			Verificação de upload do arquivo e converte em mensagem ao usuário
	 * access 				public
	 * version 				1.0.0
	 * author 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				int $errorCode
	 * return 				string
	 */
	public function uploadCodeToMessage(int $errorCode) : string
	{
		$messages = [
			UPLOAD_ERR_INI_SIZE => 'O arquivo ultrapassa a diretiva upload_max_filesize. O arquivo deve ter no máximo '. ini_get('upload_max_filesize'). '!',
			UPLOAD_ERR_FORM_SIZE => 'O arquivo ultrapassa a diretiva upload_max_filesize. O arquivo deve ter no máximo '. ini_get('upload_max_filesize'). '!',
			UPLOAD_ERR_PARTIAL => 'O arquivo foi enviado parcialmente. Por isso é impossível concluir sua requisição, tente novamente!',
			UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado.',
			UPLOAD_ERR_NO_TMP_DIR => 'Faltando a pasta de arquivos temporários.',
			UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever o arquivo no disco HDD.',
			UPLOAD_ERR_EXTENSION => 'A extensão do arquivo é inválida! Orientação UPLOAD_ERR_CANT_WRITE',
			UPLOAD_ERR_OK => 'Upload concluído com sucesso!'
		];
	
        return $messages[$errorCode] ?? 'Erro de upload desconhecido';
	}

	/**
	 * description 			Pega a url externa do arquivo num bucket do Gcloud
	 * access 				public
	 * version 				1.0.0
	 * author 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				int $id_filecenter
	 * return 				mixed
	 */
	public function neverArquivoBucket(int $id_filecenter)
	{
		
	}
	
	/**
	 * description       	Invoca dependências do javascript para o novo sistema de upload
	 * access				public
	 * version				1.0.0
	 * author 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				
	 * return				string
	**/
	public function invocarJs() : string
	{
		$js = '';
		foreach($this->dependencias_js as $dependencia)
			$js .= '<script type="text/javascript" src="'. sysa::getUrlBase(). $dependencia. '"></script>';

		return $js;
	}

	/**
	 * Pega informações de erros de upload
	 * access 				public
	 * version 				1.0.0
	 * author 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				
	 * return 				array
	 */
	public function getLastFileError() : array
	{
		return $this->fileErrList;
	}

	/**
	 * Carrega dados da galeria em geral
	 * access				public
	 * version 				1.0.0
	 * author				Anderson Arruda < andmarruda@gmail.com >
	 * param 				
	 * return				json
	 */
	public function carregaDadosGaleria(int $id_filecenter_gallery) : string
	{
		$sql = 'SELECT
			dfg.*, df.id_filecenter, df.name_file, df.id_sysadmcom_versao,
			pg_size_pretty(df.file_size) AS file_size, df.data_hora_arquivo, df.codigo_usuario_insert, 
			dgb.bucket_name, dgb.bucket_url_base, dgb.bucket_public, 
			REGEXP_REPLACE(name_file, \'^.*\.\', \'\', \'gi\') AS filetype
		FROM
			development.filecenter_gallery dfg
			JOIN development.filecenter df USING(id_filecenter_gallery)
			JOIN development.gcloud_bucket dgb USING(id_gcloud_bucket)
		WHERE
			dfg.id_filecenter_gallery=? AND df.is_gcloud_storage';
		$rows = sysa::parser($this->dbconn->execute($sql, [$id_filecenter_gallery]))->rowsToArray();
		return json_encode($rows);
	}

	/**
	 * Carrega informações dos arquivos da galeria
	 * access 				public
	 * version 				1.0.0
	 * author 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				
	 * return 				array
	 */
	public function carregaDadosArquivosGaleria() : array
	{
		if(isset($this->id_galeria)){
			$sql = 'SELECT 
				df.id_filecenter, df.name_file, df.file_size, df.file_exists, df.is_gcloud_storage, dgb.bucket_url_base
			FROM 
				development.filecenter df
				LEFT JOIN development.gcloud_bucket dgb ON dgb.id_gcloud_bucket=df.id_gcloud_bucket
			WHERE
				df.id_filecenter_gallery=?';
			$stmt = $this->dbconn->execute($sql, [$this->id_galeria]);
			$infos = [];
			while($row=$stmt->fetch(\PDO::FETCH_ASSOC))
				$infos[] = $row;

			return $infos;
		}

		return [];
	}

	/**
	 * Deleta uma galeria por completo
	 * access 				public
	 * version 				1.0.0
	 * author 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				int $id_filecenter_gallery
	 * return 				array
	 */
	public function deleteGaleriaCascade(int $id_filecenter_gallery) : array
	{
		$r = ['deletada' => false, 'arquivos' => [], 'error' => false, 'errMsg' => ''];
		$sql = 'SELECT COUNT(*) AS total FROM development.filecenter_gallery WHERE id_filecenter_gallery=?';
		$stmt = $this->dbconn->execute($sql, [$id_filecenter_gallery]);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		if($row['total'] == 0){
			$r['error'] = true;
			$r['errMsg'] = 'A galeria com o id '. $id_filecenter_gallery. ' não foi encontrada!';
			return $r;
		}

		$stmt_files = $this->dbconn->execute('SELECT * FROM development.filecenter WHERE id_filecenter_gallery=?', [$id_filecenter_gallery]);
		if($stmt_files->rowCount() > 0){
			while($file=$stmt_files->fetch(\PDO::FETCH_ASSOC)){
				$this->deletaGcloudPorId($file['id_filecenter']);
				array_push($r['arquivos'], $file['name_file']);
			}
		}

		try{
			$this->dbconn->execute('DELETE FROM development.filecenter_gallery WHERE id_filecenter_gallery=?', [$id_filecenter_gallery]);
			$r['deletada']=true;
		} catch(\Exception $err){
			$r['error']=true;
			$r['errMsg'] = 'Erro inesperado ao deletar a galeria! Error: '. $err->getMessage();
		}

		return $r;
	}

	/**
	 * Download do arquivo do gcloud para o servidor interno de maneira temporária
	 * access 				public
	 * version 				1.0.0
	 * author 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				int $id_filecenter
	 * return 				string 'qndo length = 0 | arquivo não existe no banco de dados ou algum erro aconteceu ou não está no gcloud
	 */
	public function gcloudToInternalTemp(int $id_filecenter) : string
	{
		$arquivo = $this->pegaDadosArquivo($id_filecenter);
		if(is_null($arquivo) || !$arquivo['is_gcloud_storage'])
			return '';

		$path = self::INTERNAL_TEMP_PATH. $arquivo['name_file'];
		if(file_exists($path))
			return $path;

		if($this->download($arquivo['name_file'], $path))
			return $path;

		return '';
	}

	/**
	 * Verifica se o arquivo temporário existe
	 * access 				public
	 * version 				1.0.0
	 * author 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				int $id_filecenter
	 * return 				bool
	 */
	public function existsInternalTemp(int $id_filecenter) : bool
	{
		$arquivo = $this->pegaDadosArquivo($id_filecenter);
		if(is_null($arquivo) || !$arquivo['is_gcloud_storage'])
			return false;

		$path = self::INTERNAL_TEMP_PATH. $arquivo['name_file'];
		return file_exists($path);
	}

	/**
	 * Deleta o arquivo temporário gerado anteriormente
	 * access				public
	 * version 				1.0.0
	 * author 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				int $id_filecenter
	 * return 				bool
	 */
	public function deletaInternalTemp(int $id_filecenter) : bool
	{
		$arquivo = $this->pegaDadosArquivo($id_filecenter);
		if(is_null($arquivo) || !$arquivo['is_gcloud_storage'])
			return false;

		$path = self::INTERNAL_TEMP_PATH. $arquivo['name_file'];
		if(!file_exists($path))
			return false;

		return unlink($path);
	}

	/**
	 * Deleta arquivo temporário por nome
	 * access 				public
	 * version 				1.0.0
	 * author 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				string $name
	 * return 				bool
	 */
	public function deletaInternalTempName(string $name) : bool
	{
		$path = self::INTERNAL_TEMP_PATH. $name;
		if(!file_exists($path))
			return false;

		return unlink($path);
	}

	/**
	 * Remove todos os arquivos temporários da pasta
	 * access 				public
	 * version 				1.0.0
	 * author 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				
	 * return 				void
	 */
	public function deletaTodosTemp() : void
	{
		@shell_exec('rm '. self::INTERNAL_TEMP_PATH. '* -R');
	}

	/**
	 * Verificações de arquivos que vieram através de upload retornando uma array com os dados de erros e etc...
	 * access 				private
	 * version 				1.0.0
	 * author 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				
	 * return 				?array
	 */
	private function uploadVerificacoes(string $fileName, int $fileSize, int $fileError) : ?array
	{
		if(!$this->checaFileSize($fileSize)){
			return [
				'filename' => $fileName,
				'error'    => 'FILE_SIZE',
				'message'  => 'O tamanho do arquivo excede o limite de '. $this->maxSize. ' bytes'
			];
		}

		if($this->verificaErroUpload($fileError)){
			return [
				'filename' => $fileName,
				'error'    => 'UPLOAD_ERROR',
				'message'  => $this->msgErroUpload($fileError)
			];
		}

		if($this->verificaExtensoesProibidas($fileName)){
			return [
				'filename' => $fileName,
				'error'    => 'EXT_PROIBIDAS',
				'message'  => 'A extensão do arquivo não é permitida pelo sistema!'
			];
		}

		return NULL;
	}

	/**
	 * Envia arquivos para a pasta temporária do sistema que será deletados todos os dias às 00h
	 * access 				public
	 * version 				1.0.0
	 * author 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				
	 * return 				array
	 */
	public function uploadTemporario(array $arquivos) : array
	{
		$this->fileErrList = [];
		$ret = ['arquivos' => [], 'upload' => true, 'fileError' => []];
		foreach($arquivos['name'] as $idx => $arquivo){
			$verificacoes = $this->uploadVerificacoes($arquivos['name'][$idx], $arquivos['size'][$idx], $arquivos['error'][$idx]);
			if(!is_null($verificacoes)){
				array_push($this->fileErrList, $verificacoes);
				continue;
			}

			$nome = $this->geraNome($arquivos['name'][$idx]);
			$path = self::INTERNAL_TEMP_PATH. $nome;
			if(@move_uploaded_file($arquivos['tmp_name'][$idx], $path)){
				$ret['arquivos'][] = $nome;
			}
		}

		if(count($this->fileErrList) > 0){
			$ret['upload'] = false;
			$ret['fileError'] = $this->fileErrList;
		}

		return $ret;
	}

	/**
	 * Envia arquiovs para o servidor do Google Cloud e faz a referência para uma galeria
	 * access 				public
	 * version 				1.0.0
	 * authro 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				array $arquivos
	 * return 				boolean
	 */
	public function uploadArquivosGaleria(array $arquivos, int $codigo_usuario) : bool
	{
		$this->fileErrList = [];
		foreach($arquivos['name'] as $idx => $arquivo){
			$verificacoes = $this->uploadVerificacoes($arquivos['name'][$idx], $arquivos['size'][$idx], $arquivos['error'][$idx]);
			if(!is_null($verificacoes)){
				array_push($this->fileErrList, $verificacoes);
				continue;
			}			

			$nome = $this->geraNome($arquivos['name'][$idx]);
			$path = self::INTERNAL_PATH. $nome;
			if(@move_uploaded_file($arquivos['tmp_name'][$idx], $path)){
				$size = filesize($path);
				$uploaded = $this->upload($path);
				if($this->verificaTransferenciaGcloud($uploaded)){
					$id = $this->insereFilecenter($nome, $arquivos['name'][$idx], $size, $codigo_usuario, NULL, $this->id_galeria);
					unlink($path);
				}
			}
		}
		
		return count($this->fileErrList)==0;
	}
}
?>