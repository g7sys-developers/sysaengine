<?php
    namespace sysaengine\traits;

    use \PDO;
    use \PDOStatement;

    trait DaoFunction{
        /**
         * Commont SQL
         * @var string
         */
        protected string $commonFuncSqlTable = 'SELECT * FROM %s(%s)';

        /**
         * Commont SQL
         * @var string
         */
        protected string $commonFuncSql = 'SELECT %s(%s)';

        /**
         * Last SQL executed
         * 
         * @var string
         */
        protected string $lastSqlExecuted = '';

        /**
         * Select statement of the function
         * 
         * @param string $orderBy
         * @param string $groupBy
         * @return PDOStatement
         */
        public function selectStatementFunc(?string $orderBy=null, ?string $groupBy=null): PDOStatement
        {
            $dbinfo = $this->dbObjectInfo;

            $functionInput = array_filter($this->cols, function ($item) {
                return $item['parameter_mode'] == 'IN';
            });

            $params = rtrim(str_repeat('?, ', count($functionInput)), ', ');
            if ($dbinfo['return_type'] == 'record') {
                $sql = sprintf($this->commonFuncSqlTable, $this->schema.'.'.$this->relname, $params);
                if (!empty($orderBy)) {
                    $sql .= ' ORDER BY ' . $orderBy;
                }

                if (!empty($groupBy)) {
                    $sql .= ' GROUP BY ' . $groupBy;
                }
            } else {
                $sql = sprintf($this->commonFuncSql, $this->schema . '.' . $this->relname, $params);
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array_column($functionInput, 'value'));

            $this->lastSqlExecuted = $sql;
            return $stmt;
        }

        /**
         * Common select
         * @version 1.0.0
         * @author Anderson Arruda < andmarruda@gmail.com >
         * @param
         * @return array
         */
        public function selectFunction(?string $orderBy=null, ?string $groupBy=null): array
        {
            $stmt = $this->selectStatementFunc(...func_get_args());
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
    }
