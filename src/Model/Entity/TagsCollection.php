<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 6/27/18
 * Time: 2:55 PM
 */

namespace Model\Entity;


use Component\Collection;
use Model\Contract\HasId;

class TagsCollection extends Collection
{

    private $statusCode;

    public function buildEntity(): HasId
    {
        // TODO: Implement buildEntity() method.
        // what to return
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param mixed $statusCode
     */
    public function setStatusCode($statusCode): void
    {
        $this->statusCode = $statusCode;
    }



}