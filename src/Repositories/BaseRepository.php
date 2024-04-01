<?php

namespace Hyhy\Common\Repositories;

use Prettus\Repository\Eloquent\BaseRepository as L5Repository;

abstract class BaseRepository extends L5Repository
{
    public function findWhereFirst(array $where, $orderBy = ['id' => 'desc'], $columns = ['*'])
    {
        $this->applyConditions($where);
        $this->applyOrderBy($orderBy);
        return $this->first($columns);
    }

    public function findWhere(array $where, $orderBy = ['id' => 'desc'], $columns = ['*'])
    {
        $this->applyOrderBy($orderBy);
        return parent::findWhere($where, $columns);
    }

    public function findWherePaginate(array $where, $limit = null, $orderBy = ['id' => 'desc'], $columns = ['*'])
    {
        $this->applyConditions($where);
        $this->applyOrderBy($orderBy);
        return $this->paginate($limit, $columns);
    }

    protected function applyConditions(array $where)
    {
        $this->applyOrWhere($where);
        parent::applyConditions($where);
    }

    protected function applyOrderBy($orderBy)
    {
        foreach ($orderBy as $field => $direction) {
            $this->orderBy($field, $direction);
        }
    }

    protected function applyOrWhere(array &$where)
    {
        if (empty($where['orWhere'])) {
            return;
        }

        $conditions = $where['orWhere'];
        unset($where['orWhere']);

        foreach ($conditions as $condition){
            $this->model = $this->model->where(function ($query) use ($condition) {
                foreach ($condition as $field => $value) {
                    if (is_array($value)) {
                        list($field, $conditionWhere, $val) = $value;
                        switch (strtoupper($conditionWhere)) {
                            case 'IN':
                                $query->orWhereIn($field, $val);
                                break;
                            case 'NOTIN':
                                $query->orWhereNotIn($field, $val);
                                break;
                            default:
                                $query->orWhere($field, $conditionWhere, $val);
                                break;
                        }
                    } else {
                        $query->orWhere($field, '=', $value);
                    }
                }
            });
        }

    }
}
