<?php
/**
	* Este pojeto compõe a biblioteca do Sysaengine
	* pt-BR: App de sistemas que controla arquivos relacionados ao Sysaengine ou uploads
	*
	* Está atualizado para
	*    PHP 8.0
	*
	* @package 		sysaengine
	* @name 		  DOS3 -  Digital ocean S3 compatible
	* @version 		2.0.0
	* @copyright 	2021-2030
	* @author 		Anderson Arruda < andmarruda@gmail.com >
**/
namespace sysaengine\buckets;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class DOS3 implements bucketInterface {
	/**
	 * S3 client
	 * @var S3Client
	 */
	private S3Client $s3Client;

	/**
	 * Bucket name
	 * 
	 * @var string
	 */
	private string $bucketName;

  /**
	 * Class data configuration
	 * 
	 * @param
	 * @return void
	 */
	public function __construct(string $space_region, string $space_endpoint, string $space_key, string $space_secret, string $space_bucket)
	{
		$this->s3Client = new S3Client([
			'version'     => 'latest',
			'region'      => $space_region,
			'endpoint'    => $space_endpoint,
			'credentials' => [
				'key'    => $space_key,
				'secret' => $space_secret,
			],
		]);

		$this->bucketName = $space_bucket;
	}

	/**
	 * Upload file to Digital Ocean Spaces
	 * 
 	 * param        string $filepath
	 * @return
	 */
	public function upload(string $filepath)
	{
		$key = basename($filepath);
		$resource = fopen($filepath, 'r');

		try {
			$this->s3Client->putObject([
				'Bucket' => $this->bucketName,
				'Key'    => $key,
				'Body'   => $resource,
				'ACL'    => 'public-read',
			]);
		} catch (AwsException $e) {
			echo $e->getMessage();
		}
	}

	/**
	 * Delete file from Digital ocean spaces
	 * 
	 * param        string $key
	 * @return
	 */
	public function delete(string $key)
	{
		try {
			$this->s3Client->deleteObject([
				'Bucket' => $this->bucketName,
				'Key'    => $key,
			]);
		} catch (AwsException $e) {
			echo $e->getMessage();
		}
	}

	/**
	 * Creates temporary url for the file stored on digital ocean spaces
	 * 
	 * param        string $key
	 * @return
	 */
	public function getTemporaryUrl(string $key)
	{
		$cmd = $this->s3Client->getCommand('GetObject', [
			'Bucket' => $this->bucketName,
			'Key'    => $key,
		]);

		$request = $this->s3Client->createPresignedRequest($cmd, '+5 minutes');

		return (string) $request->getUri();
	}
}