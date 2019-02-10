<?php

namespace Illuminate\Database\Query\Processors;

use Exception;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

class SqlServerProcessor extends Processor
{
    /**
     * Process an "insert get ID" query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $sql
     * @param  array   $values
     * @param  string  $sequence
     * @return int
     */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $connection = $query->getConnection();

        $statement = $connection->insert($sql, $values);

        if ($connection->getConfig('odbc') === true) {
            $id = $this->processInsertGetIdForOdbc($statement);
        } else {
            $id = $connection->getPdo()->lastInsertId();
        }

        return is_numeric($id) ? (int) $id : $id;
    }

    /**
     * Process an "insert get ID" query for ODBC.
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @return int
     *
     * @throws \Exception
     */
    protected function processInsertGetIdForOdbc($statement)
    {
      $id = null;

      if ($statement) {
        // loop through the rowsets until we get the id, although in most cases
        // it should be in the first one
        do {
          $id = $statement->fetchColumn();
        }
        while (!$id && $statement->nextRowset());
      }

      if (! $id) {
        throw new Exception('Unable to retrieve last inserted ID for ODBC.');
      }

      return $id;
    }

    /**
     * Process the results of a column listing query.
     *
     * @param  array  $results
     * @return array
     */
    public function processColumnListing($results)
    {
        return array_map(function ($result) {
            return ((object) $result)->name;
        }, $results);
    }
}
