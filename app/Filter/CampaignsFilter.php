<?php

namespace App\Filter;

use App\QueryFilter;

class CampaignsFilter extends QueryFilter
{
    public function input()
    {
        return parent::filters();
    }

    public function name($input = '')
    {
        if (! $input) {
            return $this;
        }

        return $this->builder->where('name', 'LIKE', '%' . $input . '%');
    }

    public function description($input = '')
    {
        if (! $input) {
            return $this;
        }

        return $this->builder->where('description', 'LIKE', '%' . $input . '%');
    }

    public function address($input = '')
    {
        if (! $input) {
            return $this;
        }

        return $this->builder->where('address', 'LIKE', '%' . $input . '%');
    }



    
}
