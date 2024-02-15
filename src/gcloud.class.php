<?php
/**
	* Este pojeto compõe a biblioteca do Sysaengine
	* pt-BR: App de sistemas do Google Cloud
	*
	* Está atualizado para
	*    PHP 8.0
	*
	* @package 		Sysaengine
	* @name 		gcloud
	* @version 		2.0.0
	* @copyright 	2021-2030
	* @author 		Anderson Arruda < andmarruda@gmail.com >
**/
namespace sysaengine;
require_once(__DIR__. '/../googleapi/vendor/autoload.php');
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;

abstract class gcloud{
    private $storage;
    protected $bucketName;
    private $bucket;
    protected $bucketInfo;

/**
 * description       	Constrói a classe do gcloud app autenticando o usuário
 * access				public
 * version				1.0.0
 * author				Anderson Arruda < andmarruda@gmail.com >
 * param 				string $bucketName
 * param                ?string $dbname
 * return				void
**/
    public function __construct(string $bucketName, ?string $dbname=NULL){
        $this->searchBucket($bucketName);
    }

/**
 * Pega dados do bucket através do através do id do glcoud bucket
 * access               protected
 * version              1.0.0
 * author               Anderson Arruda < andmarruda@gmail.com >
 * param                int $id_gcloud_bucket
 * return               array
 */
protected function bucketInfoById(int $id_gcloud_bucket) : array
{
    $sql = 'SELECT * FROM development.gcloud_bucket WHERE id_gcloud_bucket=?';
    $stmt = $this->dbconn->execute($sql, [$id_gcloud_bucket]);
    if($stmt->rowCount()===0)
        throw new \Exception('O bucket '. $id_gcloud_bucket. ' do cloud storage não foi encontrado nos cadastros de bucket no Sysadmcom!');

    return $stmt->fetch(\PDO::FETCH_ASSOC);
}

/**
 * Pega dados do bucket através do nome do bucket
 * access               protected
 * version              1.0.0
 * author               Anderson Arruda < andmarruda@gmail.com >
 * param                string $bucketName
 * return               array
 */
protected function bucketInfoByName(string $bucketName) : array
{
    $sql = 'SELECT * FROM development.gcloud_bucket WHERE bucket_name=?';
    $stmt = $this->dbconn->execute($sql, [$bucketName]);
    if($stmt->rowCount()===0)
        throw new \Exception('O bucket '. $bucketName. ' do cloud storage não foi encontrado nos cadastros de bucket no Sysadmcom!');

    return $stmt->fetch(\PDO::FETCH_ASSOC);
}

/**
 * description          Pega informações do bucket e ao mesmo tempo o valida
 * access               private
 * version              1.0.0
 * author               Anderson Arruda < andmarruda@gmail.com >
 * param                string $bucketName
 * return               void
 */
private function searchBucket(string $bucketName) : void
{
    $this->bucketName = $bucketName;
    $this->bucketInfo = $this->bucketInfoByName($bucketName);
    $this->storage = new StorageClient([
        'keyFile' => json_decode($this->bucketInfo['json_auth_key'], true)
    ]);
    $this->bucket = $this->storage->bucket($this->bucketName);
}

/**
 * description          Lista os arquivos presentes no bucket
 * access               public
 * version              1.0.0
 * author               Anderson Arruda < andmarruda@gmail.com >
 * param                string $bucket_name
 * param                ?string $prefix=NULL
 * return               array
 */
    public function list(?string $prefix=NULL) : array
    {
        $arr = [];
        foreach($this->bucket->objects() as $obj){
            if(!is_null($prefix)){
                if(preg_match('/^'. $prefix.'/', $obj->name()))
                    array_push($arr, $obj->name());

                continue;
            }

            array_push($arr, $obj->name());
        }

        return $arr;
    }

/**
 * description       	Faz upload de arquivo para um bucket do google cloud
 * access				public
 * version				1.0.0
 * author				Anderson Arruda < andmarruda@gmail.com >
 * param 				string $bucket_name
 * param                string $filepath
 * return				StorageObject
**/
    public function upload(string $filepath) : ?StorageObject
    {
        try{
            if(!file_exists($filepath))
                return false;

            $obj = $this->bucket->upload(
                fopen($filepath, 'r')
            );
            return $obj;
        } catch(\Exception $err){
            var_dump($err->getMessage());
            return NULL;
        }
    }

/**
 * description       	Deleta um arquivo do bucket do google cloud
 * access				public
 * version				1.0.0
 * author				Anderson Arruda < andmarruda@gmail.com >
 * param 				string $bucket_name
 * param                string $filepath
 * return				bool
**/
    public function delete(string $filename) : bool
    {
        try{
            $obj = $this->bucket->object($filename);
            $obj->delete();
            return true;
        } catch(\Exception $err){
            return false;
        }
    }

/**
 * description          Verifica se o arquivo existe no bucket
 * access               public
 * version              1.0.0
 * author               Anderson Arruda < andmarruda@gmail.com >
 * param                string $filename
 * return               bool
 */
    public function gcloudArquivoExiste(string $nomeArquivo) : bool
    {
        try{
            $obj = $this->bucket->object($nomeArquivo);
            return $obj->exists();
        } catch(\Exception $err){
            return false;
        }
    }

/**
 * description       	Faz download de um arquivo do bucket do google cloud
 * access				public
 * version				1.0.0
 * author				Anderson Arruda < andmarruda@gmail.com >
 * param 				string $bucket_name
 * param                string $filepath
 * param                string $destination
 * return				bool
**/
    public function download(string $filename, string $destination) : bool
    {
        try{
            $destDir = dirname($destination);
            if(!is_readable($destDir) || !is_writable($destDir))
                return false;

            $obj = $this->bucket->object($filename);
            $obj->downloadToFile($destination);
            return true;
        } catch(\Exception $err){
            return false;
        }
    }
}
?>