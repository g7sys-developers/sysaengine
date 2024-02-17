<?php
    namespace sysaengine\traits;

    use sysaengine\sql_helper\whereInterpreter;

    trait DaoCommon{
        /**
         * Commont SQL
         * @var string
         */
        protected string $commonSql = 'SELECT %S FROM %S';

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

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($preparedWhere['binds']);
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
?>