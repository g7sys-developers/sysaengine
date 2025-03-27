<?php
    namespace sysaengine\traits;

    use sysaengine\sql_helper\whereInterpreter;
    use \PDO;
    use \PDOStatement;
    use sysaengine\log;

    trait DaoCommon{
        /**
         * Código base para todos os selects
         * 
         * @version 1.0.0
         * @author Anderson Arruda < andmarruda@gmail.com >
         * @param	string $fields
         * @param	string $where
         * @param	string $orderBy
         * @param	string $groupBy
         * @return	array
         */
        private function prepareSelectCommon(string $fields='*', string $where='', string $orderBy='', string $groupBy=''): array
        {
            $sql = sprintf($this->commonSql, $fields, $this->schema.'.'.$this->relname);

            if($where != '')
            {
                $preparedWhere = $this->prepareWhere($where);
                $sql .= " WHERE ". $preparedWhere['where'];
            }

            if($groupBy != '')
                $sql .= " GROUP BY $groupBy";

            if($orderBy != '')
                $sql .= " ORDER BY $orderBy" ;

            return [
                'sql' => $sql,
                'binds' => $preparedWhere['binds'] ?? []
            ];
        }

        /**
         * Commont SQL
         * @var string
         */
        protected string $commonSql = 'SELECT %s FROM %s';

        /**
         * Where preparation
         * @version 1.0.0
         * @author Anderson Arruda < andmarruda@gmail.com >
         * @param   string $where
         * @return  array['where' => string, 'binds' => array]
         */
        private function prepareWhere(string $where) : array
        {
            if($this->useIndex)
            {
                $columns = $this->getIndex($where);
                if(count($columns) == 0)
                    throw new \Exception("Index $where doesn't exists for this database object");

                return whereInterpreter::arrayToIndex($columns);
            } else {
                return whereInterpreter::execute($where, $this->cols);
            }
        }

        /**
         * Common select
         * @version 1.0.0
         * @author Anderson Arruda < andmarruda@gmail.com >
         * @param
         * @return array
         */
        public function selectCommon(string $fields='*', string $where='', string $orderBy='', string $groupBy='') : array
        {
            $stmt = $this->selectStatementCommon(...func_get_args());
            $results = [0 => ['none' => 'Nenhum resultado encontrado!']];

            if($stmt->rowCount() > 0)
            {
                $results = [];
                while($result=$stmt->fetch(PDO::FETCH_ASSOC))
                    $results[] = $result;
            }
            
            $this->useIndex = true;
            return $results;
        }

        /**
         * Common select to PDOStatement
         * @version 1.0.0
         * @author Anderson Arruda < andmarruda@gmail.com >
         * @param
         * @return PDOStatement
         */
        public function selectStatementCommon(string $fields='*', string $where='', string $orderBy='', string $groupBy='') : PDOStatement
        {
            $preparedSql = $this->prepareSelectCommon(...func_get_args());
            $stmt = $this->conn->prepare($preparedSql['sql']);
            $executed = $stmt->execute($preparedSql['binds']);

            log::logInfo("SQL: " . $preparedSql['sql'] . " BINDS: " . json_encode($preparedSql['binds']));
            if (!$executed) {
                throw new \Exception("Erro ao executar a query: " . $stmt->errorInfo()[2]);
            }

            return $stmt;
        }

        /**
         * Transforma o comando select em um retorno do tipo SQL
         * 
         * @version 1.0.0
         * @author Anderson Arruda < andmarruda@gmail.com >
         * @param string $fields
         * @param string $where
         * @param string $orderBy
         * @param string $groupBy
         * @return string
         */
        public function selectToSql(string $fields='*', string $where='', string $orderBy='', string $groupBy=''): string
        {
            $preparedSql = $this->prepareSelectCommon(...func_get_args());
            foreach ($preparedSql['binds'] as $bind) {
                $value = is_string($bind) ? "'" . addslashes($bind) . "'" : $bind;
                $preparedSql['sql'] = preg_replace('/\?/', $value, $preparedSql['sql'], 1);
            }

            return $preparedSql['sql'];
        }
    }
