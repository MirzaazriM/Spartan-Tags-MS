<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 6/29/18
 * Time: 11:27 AM
 */

namespace Model\Entity;


use Component\Collection;
use Model\Contract\HasId;

class NamesCollection extends Collection
{

    public function buildEntity(): HasId
    {
        // TODO: Implement buildEntity() method.
        return new Names;

    }

    public function getIds()
    {
        return parent::getIds(); // TODO: Change the autogenerated stub
    }

    public function addEntity(HasId $entity, $key = null)
    {
        return parent::addEntity($entity, $key); // TODO: Change the autogenerated stub
    }

}