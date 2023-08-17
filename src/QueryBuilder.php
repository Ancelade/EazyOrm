<?php

namespace Ancelade\EazyOrm;

class QueryBuilder
{
    private string $tableName;
    private ?string $connection;
    private array $wheres = [];
    private array $orders = [];
    private array $groupByConditions = [];
    private ?int $limit = null;
    private array $bindings = [];

    /**
     * QueryBuilder constructor.
     * Initializes the table and connection properties from annotations.
     */
    public function __construct($table = null, $connexion = null)
    {
        if(!$table) {
            $this->tableName = $this->getAnnotationValue('table');
        } else {
            $this->tableName = $table;
        }
        if(!$connexion) {
            $this->connection = $this->getAnnotationValue('connection');
        } else {
            $this->connection = $connexion;
        }

    }

    /**
     * Adds a basic WHERE condition.
     *
     * @param string $field The column/field name.
     * @param string $condition The condition (e.g., '=', '<>', '>', etc.).
     * @param mixed $value The value to compare against.
     * @return $this
     */
    public function where(string $field, string $condition, mixed $value): self
    {
        $this->wheres[] = "$field $condition ?";
        $this->bindings[] = $value;
        return $this;
    }

    /**
     * Adds a raw WHERE condition.
     *
     * WARNING: Ensure your raw SQL is safe to prevent SQL injections.
     *
     * @param string $sql The raw SQL string.
     * @param array $values The values to bind.
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function whereRaw(string $sql, array $values): self
    {
        $placeholdersCount = substr_count($sql, '?');
        if ($placeholdersCount !== count($values)) {
            throw new \InvalidArgumentException("Number of placeholders doesn't match number of values.");
        }
        $this->wheres[] = $sql;
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    /**
     * Adds an ORDER BY clause.
     *
     * @param string $field The column/field name.
     * @param string $direction The direction ('ASC' or 'DESC').
     * @return $this
     */
    public function orderBy(string $field, string $direction): self
    {
        $this->orders[] = "$field $direction";
        return $this;
    }

    /**
     * Adds a WHERE IN condition.
     *
     * @param string $field The column/field name.
     * @param array $list The list of values.
     * @return $this
     */
    public function whereIn(string $field, array $list): self
    {
        $placeholders = rtrim(str_repeat('?, ', count($list)), ', ');
        $this->wheres[] = "$field IN ($placeholders)";
        $this->bindings = array_merge($this->bindings, $list);
        return $this;
    }

    /**
     * Adds a GROUP BY clause.
     *
     * @param string $field The column/field name.
     * @return $this
     */
    public function groupBy(string $field): self
    {
        $this->groupByConditions[] = $field;
        return $this;
    }

    /**
     * Fetches the first record.
     *
     * @return array The query and its bindings.
     */
    public function first(): array
    {
        $this->limit = 1;
        return $this->toSql('SELECT *');
    }

    /**
     * Fetches all records with an optional limit.
     *
     * @param int|null $limit The maximum number of records to retrieve.
     * @return array The query and its bindings.
     */
    public function all(?int $limit = null): array
    {
        $this->limit = $limit;
        return $this->toSql('SELECT *');
    }

    /**
     * Fetches a count of records.
     *
     * @return array The query and its bindings.
     */
    public function count(): array
    {
        return $this->toSql('SELECT COUNT(*)');
    }

    /**
     * Builds the SQL query.
     *
     * @param string $selectStatement The SELECT statement.
     * @return array The constructed SQL query and its bindings.
     */
    public function toSql(string $selectStatement): array
    {
        $sql = $selectStatement . " FROM " . $this->tableName;

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        if (!empty($this->groupByConditions)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupByConditions);
        }

        if (!empty($this->orders)) {
            $sql .= " ORDER BY " . implode(', ', $this->orders);
        }

        if ($this->limit) {
            $sql .= " LIMIT " . $this->limit;
        }

        return ['sql' => $sql, 'bindings' => $this->bindings];
    }

    /**
     * Fetches an annotation value from the class doc comment.
     *
     * @param string $annotationName The annotation name.
     * @return string|null The value of the annotation or null if not found.
     */
    private function getAnnotationValue(string $annotationName): ?string
    {
        $reflection = new \ReflectionClass($this);
        $docComment = $reflection->getDocComment();

        if ($docComment === false) {
            return null;
        }

        preg_match_all("/@" . $annotationName . "\s+([^\s]+)/", $docComment, $matches);

        return $matches[1][0] ?? null;
    }
}
