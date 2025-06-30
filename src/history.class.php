<?php
/**
	* Este pojeto compõe a biblioteca Sysaengine do Sysadmcom
	*
	* Está atualizado para
	*    PHP 7.4
	*
	* @package      sysaengine
	* @name         html
	* @version      1.0.0
	* @copyright    2020-2025
	* @author       Anderson Arruda < andmarruda@gmail.com >
	*
**/
namespace sysaengine;

class history
{
	/**
	 * Insere o historico de eventos de DB
	 * 
	 * @name        save
	 * @access      public
	 * @version		1.0.0
	 * @param 		int $codigo_usuario
	 * @param 		array $input_data
	 * @param		array $output_data
	 * @param 		string $command
	 * @param 		string $entity
	 * @param		string $action
	 * @return 		void
	 */
	public static function save(
		array $input_data,
		array $output_data,
		string $command,
		string $entity,
		string $action
	): void {
		try {
			$conn = conn::get_conn();
			$sql = "INSERT INTO history (codigo_usuario, input_data, output_data, command, entity, action, created_at) VALUES (?, ?, ?, ?, ?, ?, now())";
			$stmt = $conn->prepare($sql);
			$stmt->execute([
				sysa::getCodigoUsuario(),
				json_encode($input_data),
				json_encode($output_data),
				$command,
				$entity,
				$action
			]);
		} catch (\Exception $e) {
			throw new \Exception("Erro ao salvar histórico: " . $e->getMessage());
		}
	}
}
