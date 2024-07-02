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
use sysaengine\buckets\bucketInterface;

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
	 * param 				bucketInterface $bucket
	 * param 				string $tempPath
	 * return 			void
	 */
	public function __construct(bucketInterface $bucket, string $tempPath)
	{
		parent::__construct($bucket, $tempPath);
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
			$stmt = $this->dbconn->prepare('SELECT * FROM development.filecenter_gallery WHERE id_filecenter_gallery = ?');
			$stmt->execute([$id_galeria]);
			if($stmt->rowCount() == 0)
				throw new \Exception("Galeria não encontrada");

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
		$stmt = $this->dbconn->query('INSERT INTO development.filecenter_gallery(status_ativo) VALUES(true) RETURNING id_filecenter_gallery');
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
	 * Carrega os arquivos pertencentes a uma galeria e ja 
	 * retorna com a url temporária para exibição
	 * 
	 * @param
	 * @return array
	 */
	public function carregaArquivos() : array
	{
		$stmt = $this->dbconn->prepare('SELECT * FROM development.filecenter WHERE id_filecenter_gallery = ?');
		$stmt->execute([$this->id_galeria]);
		$files = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		foreach($files as $key => $file)
		{
			$files[$key]['url'] = $this->bucket->getTemporaryUrl($file['file_key']);
		}

		return $files;
	}
}
?>