<?php
namespace Model\Core\Helper\SQL;

use Component\DataMapper;

class Helper
{
    
    /**
     * Generates Where In
     * 
     * @param array $array
     * @return String
     */
    public function whereIn(array $array): String {
        $whereId = implode(",", $array);
        return $whereId;
    }
    
}

