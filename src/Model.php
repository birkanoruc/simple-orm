<?php

namespace Birkanoruc\SimpleOrm;

use Birkanoruc\SimpleOrm\Database;

class Model
{
    protected $table;
    protected $connection;
    protected $query;
    protected $bindings = [];

    public function __construct(Database $database)
    {
        $this->connection = $database;
        $this->query = "SELECT * FROM " . $this->table;
    }

    /**
     * Querying Methods
     */

    public function all()
    {
        $this->connection->query($this->query, $this->bindings);
        return $this->connection->get();
    }

    public function first()
    {
        $this->query .= " LIMIT 1";
        $this->connection->query($this->query, $this->bindings);
        return $this->connection->find();
    }

    public function find($id)
    {
        $this->query .= " WHERE id = :id";
        $this->bindings['id'] = $id;
        $this->connection->query($this->query, $this->bindings);
        return $this->connection->find();
    }

    public function where($column, $operator, $value)
    {
        if (strpos($this->query, 'WHERE') !== false) {
            $this->query .= " AND $column $operator :$column";
        } else {
            $this->query .= " WHERE $column $operator :$column";
        }
        $this->bindings[$column] = $value;
        return $this;
    }

    public function orWhere($column, $operator, $value)
    {
        if (strpos($this->query, 'WHERE') !== false) {
            $this->query .= " OR $column $operator :$column";
        } else {
            $this->query .= " WHERE $column $operator :$column";
        }
        $this->bindings[$column] = $value;
        return $this;
    }

    public function whereIn($column, $values)
    {
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->query .= " WHERE $column IN ($placeholders)";
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    public function whereNotIn($column, $values)
    {
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->query .= " WHERE $column NOT IN ($placeholders)";
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    public function with($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }

        foreach ($relations as $relation) {
            if (method_exists($this, $relation)) {
                $this->$relation();
            }
        }

        return $this;
    }

    public function has($relation)
    {
        if (method_exists($this, $relation)) {
            $relatedQuery = $this->$relation();
            return !empty($relatedQuery);
        }

        return false;
    }

    public function doesntHave($relation)
    {
        if (method_exists($this, $relation)) {
            $relatedQuery = $this->$relation();
            return empty($relatedQuery);
        }

        return false;
    }

    /**
     * Inserting, Updating, and Deleting Methods
     */

    public function create($data)
    {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $query = "INSERT INTO " . $this->table . " ($columns) VALUES ($placeholders)";
        $this->connection->query($query, array_values($data));
        return $this->connection->get();
    }

    public function update($id, $data)
    {
        $set = implode(', ', array_map(fn($key) => "$key = ?", array_keys($data)));
        $query = "UPDATE " . $this->table . " SET $set WHERE id = ?";
        $params = array_merge(array_values($data), [$id]);
        $this->connection->query($query, $params);
        return $this->connection->get();
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $this->connection->query($query, [$id]);
        return $this->connection->get();
    }

    public function destroy($ids)
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query = "DELETE FROM " . $this->table . " WHERE id IN ($placeholders)";
        $this->connection->query($query, $ids);
        return $this->connection->get();
    }

    /**
     * Aggregates Methods
     */

    public function count()
    {
        $this->query = "SELECT COUNT(*) AS count FROM " . $this->table;
        $this->connection->query($this->query, $this->bindings);
        return $this->connection->find()['count'];
    }

    public function sum($column)
    {
        $this->query = "SELECT SUM($column) AS sum FROM " . $this->table;
        $this->connection->query($this->query, $this->bindings);
        return $this->connection->find()['sum'];
    }

    public function avg($column)
    {
        $this->query = "SELECT AVG($column) AS avg FROM " . $this->table;
        $this->connection->query($this->query, $this->bindings);
        return $this->connection->find()['avg'];
    }

    public function min($column)
    {
        $this->query = "SELECT MIN($column) AS min FROM " . $this->table;
        $this->connection->query($this->query, $this->bindings);
        return $this->connection->find()['min'];
    }

    public function max($column)
    {
        $this->query = "SELECT MAX($column) AS max FROM " . $this->table;
        $this->connection->query($this->query, $this->bindings);
        return $this->connection->find()['max'];
    }

    /**
     * Eager Loading Methods
     */

    public function loader($relations)
    {
        foreach ($relations as $relation) {
            if (method_exists($this, $relation)) {
                $this->$relation();
            }
        }
    }

    public function withCount($relations)
    {
        foreach ($relations as $relation) {
            if (method_exists($this, $relation)) {
                $relatedQuery = $this->$relation();
                $count = count($relatedQuery);
                $this->setAttribute($relation . '_count', $count);
            }
        }
    }

    /**
     * Accessors and Mutators Methods
     */

    public function getAttribute($key)
    {
        return $this->{$key};
    }

    public function setAttribute($key, $value)
    {
        $this->{$key} = $value;
    }

    /**
     * Relationships Methods
     */
    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $foreignKey = $foreignKey ?: strtolower(basename(str_replace('\\', '/', get_class($related)))) . '_id';
        $localKey = $localKey ?: 'id';

        $query = "SELECT * FROM " . (new $related)->table . " WHERE $foreignKey = :$localKey LIMIT 1";
        $this->connection->query($query, [$localKey => $this->{$localKey}]);
        return $this->connection->find();
    }

    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $foreignKey = $foreignKey ?: strtolower(basename(str_replace('\\', '/', get_class($related)))) . '_id';
        $localKey = $localKey ?: 'id';

        $query = "SELECT * FROM " . (new $related)->table . " WHERE $foreignKey = :$localKey";
        $this->connection->query($query, [$localKey => $this->{$localKey}]);
        return $this->connection->get();
    }

    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        $foreignKey = $foreignKey ?: strtolower(basename(str_replace('\\', '/', get_class($related)))) . '_id';
        $ownerKey = $ownerKey ?: 'id';

        $query = "SELECT * FROM " . (new $related)->table . " WHERE $ownerKey = :$foreignKey LIMIT 1";
        $this->connection->query($query, [$foreignKey => $this->{$foreignKey}]);
        return $this->connection->find();
    }

    public function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null)
    {
        $foreignPivotKey = $foreignPivotKey ?: strtolower(basename(str_replace('\\', '/', get_class($this)))) . '_id';
        $relatedPivotKey = $relatedPivotKey ?: strtolower(basename(str_replace('\\', '/', get_class($related)))) . '_id';
        $parentKey = $parentKey ?: 'id';
        $relatedKey = $relatedKey ?: 'id';

        $query = "SELECT * FROM $table WHERE $foreignPivotKey = :$parentKey";
        $this->connection->query($query, [$parentKey => $this->{$parentKey}]);
        $pivotIds = array_column($this->connection->get(), $relatedPivotKey);

        if (empty($pivotIds)) {
            return [];
        }

        $relatedModel = new $related();
        $query = "SELECT * FROM " . $relatedModel->table . " WHERE $relatedKey IN (" . implode(',', array_fill(0, count($pivotIds), '?')) . ")";
        $this->connection->query($query, $pivotIds);
        return $this->connection->get();
    }
}
