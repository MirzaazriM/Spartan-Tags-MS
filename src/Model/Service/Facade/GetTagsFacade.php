<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 6/27/18
 * Time: 3:05 PM
 */
namespace Model\Service\Facade;

use Model\Entity\ResponseBootstrap;
use Model\Entity\Tags;
use Model\Service\TagsService;
use Model\Entity\TagsCollection;

class GetTagsFacade
{
    
    private $lang;
    private $app;
    private $like;
    private $state;
    private $tagsMapper;
    private $configuration;

    
    public function __construct(string $lang, string $app = null, string $like = null, string $state = null, $tagsMapper) {
        $this->lang = $lang;
        $this->app = $app;
        $this->like = $like;
        $this->state = $state;
        $this->tagsMapper = $tagsMapper;
        $this->configuration = $tagsMapper->getConfiguration();
    }
    
    
    /**
     * Hanlde Function To Be Called
     * 
     * @return TagsCollection
     */
    public function handleTags() {
        $data = null;

        // Calling By App
        if(!empty($this->app)){
            $data = $this->getTagsByApp();
        }
        // Calling by Search
        else if(!empty($this->like)){
            $data = $this->searchTags();
        }
        // Calling by State
        else{
            $data = $this->getTagsFacade();
        }

        // return data
        return $data;
    }
    
    
    /**
     * Get Tags 
     * 
     * {@inheritDoc}
     * @see \Model\Service\TagsService::getTags()
     */
    public function getTagsFacade() {
        // create entity and set its values
        $entity = new Tags();
        $entity->setLang($this->lang);
        $entity->setState($this->state);

        // call mapper for data
        $collection = $this->tagsMapper->getTags($entity);

        // return collection
        return $collection;
    }


    /**
     * Get Tags By App
     *
     * @return mixed
     */
    public function getTagsByApp() {
        // call apps MS for data
        $client = new \GuzzleHttp\Client();
        $result = $client->request('GET', $this->configuration['apps_url'] . '/apps/data?app=' . $this->app . '&lang=' . $this->lang . '&state=' . $this->state . '&type=tags', []);
        $data = json_decode($result->getBody()->getContents(), true);

        // return data
        return $data;
    }


    /**
     * Search for Tags
     *
     * @return TagsCollection
     */
    public function searchTags():TagsCollection {
        // create entity and set its values
        $entity = new Tags();
        $entity->setName($this->like);
        $entity->setLang($this->lang);
        $entity->setState($this->state);

        // call mapper for data
        $data = $this->tagsMapper->searchTags($entity);

        // return data
        return $data;
    }
    
}