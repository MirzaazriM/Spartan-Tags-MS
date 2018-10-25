<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 6/27/18
 * Time: 2:53 PM
 */

namespace Model\Entity;


use Model\Contract\HasId;

class Tags implements HasId
{

    private $id;
    private $name;
    private $lang;
    private $app;
    private $ids = [];
    private $collection;
    private $state;
    private $behavior;
    private $tagParent;
    private $version;
    private $limit;
    private $from;


    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param mixed $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param mixed $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version): void
    {
        $this->version = $version;
    }

    /**
     * @return mixed
     */
    public function getTagParent()
    {
        return $this->tagParent;
    }

    /**
     * @param mixed $tagParent
     */
    public function setTagParent($tagParent): void
    {
        $this->tagParent = $tagParent;
    }

    /**
     * @return mixed
     */
    public function getBehavior()
    {
        return $this->behavior;
    }

    /**
     * @param mixed $behavior
     */
    public function setBehavior($behavior): void
    {
        $this->behavior = $behavior;
    }
    
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @return mixed
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @return multitype:
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * @return mixed
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param mixed $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * @param mixed $app
     */
    public function setApp($app)
    {
        $this->app = $app;
    }

    /**
     * @param multitype: $ids
     */
    public function setIds($ids)
    {
        $this->ids = $ids;
    }

    /**
     * @param mixed $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

}