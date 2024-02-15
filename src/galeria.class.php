<?php
/**
	* Este pojeto compõe a biblioteca do Sysaengine
	* pt-BR: App de sistemas de Upload do Sysadmcom
	*
	* Está atualizado para
	*    PHP 8.0
	*
	* @package 		amaengine
	* @name 		sysaengine\galeria
	* @version 		1.0.0
	* @copyright 	2021-2030
	* @author 		Anderson M Arruda < andmarruda at gmail dot com >
**/
namespace sysaengine;

class galeria extends upload{
    /**
	 * Mantém o acesso a conexão com o banco de dados
	 */
	protected $id_galeria;

    /**
	 * description 		Constrói a classe passando o nome do banco de dados conectado
	 * access 			public
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			string $dbname
     * param 			string $bucketName
	 * return 			void
	 */
    public function __construct(?string $dbname=NULL, ?string $bucketName=NULL, ?int $maxSize=NULL)
    {
        parent::__construct($dbname, $bucketName, $maxSize);
    }

    /**
     * description      Seta o id da galeria para o carregamento de seus respectivos dados
     * access           public
     * version          1.0.0
     * author           Anderson Arruda < andmarruda@gmail.com >
     * param            int $id_galeria
     * return 			$this "instanceof galeria"
     */
    public function carregaGaleria(int $id_galeria) : galeria
    {
        $this->id_galeria = $id_galeria;
        return $this;
    }

    /**
	 * Cria uma galeria para o armazenamento de arquivos
	 * access 				public
	 * version 				1.0.0
	 * author 				Anderson Arruda < andmarruda@gmail.com >
	 * param 				
	 * return 				$this
	 */
	public function criaGaleria() : galeria
	{
		$stmt = $this->dbconn->execute('INSERT INTO development.filecenter_gallery(status_ativo) VALUES(true) RETURNING id_filecenter_gallery', []);
		if($stmt->rowCount() == 0)
			throw new \Exception("Erro ao criar a galeria");

		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		$this->id_galeria = $row['id_filecenter_gallery'];
        return $this;
	}

	/**
	 * Retorna o id da galeria carregada
	 * access 			public
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			
	 * return 			int
	 */
	public function getIdGaleria() : int
	{
		return $this->id_galeria;
	}

	/**
	 * Transfere uma galeria de dentro da VM do Sysadmcom para um servidor externo. "após o envio e confirmação de envio o mesmo é deletado do servidor local!"
	 * access 			public
	 * version 			1.0.0
	 * author 			Anderson Arruda < andmarruda@gmail.com >
	 * param 			
	 * return 			bool
	 */
	public function transfereGaleriaParaGcloud(int $id_filecenter_gallery) : bool
	{
		return false;
	}
}
?>