<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 6/27/18
 * Time: 3:21 PM
 */

namespace Model\Service;

use Model\Core\Helper\Monolog\MonologSender;
use Model\Entity\NamesCollection;
use Model\Entity\ResponseBootstrap;
use Model\Entity\Tags;
use Model\Mapper\TagsMapper;
use Model\Service\Facade\GetTagsFacade;
use Exception;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;

class TagsService
{

    public $tagsMapper;
    private $configuration;

    public function __construct(TagsMapper $tagsMapper)
    {
        $this->tagsMapper = $tagsMapper;
        $this->configuration = $tagsMapper->getConfiguration();
    }


    /**
     * Get tag service
     *
     * @param int $id
     * @param string $lang
     * @param string $state
     * @return ResponseBootstrap
     */
    public function getTag(int $id, string $lang, string $state):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create entity and set its values
            $entity = new Tags();
            $entity->setId($id);
            $entity->setLang($lang);
            $entity->setState($state);

            // get data from database
            $res = $this->tagsMapper->getTag($entity);
            $id = $res->getId();

            // check fetched data and set response
            if(isset($id)){
                $response->setStatus(200);
                $response->setMessage('Success');
                $response->setData([
                    'id' => $res->getId(),
                    'name' => $res->getName(),
                    'lang' => $res->getLang(),
                    'state' => $res->getState(),
                    'behavior' => $res->getBehavior()
                ]);
            }else {
                $response->setStatus(204);
                $response->setMessage('No content');
            }

            // return data
            return $response;

        }catch(Exception $e){
            // send monolog record
            $this->getMonologInstance()->sendMonologRecord($this->configuration, 1000, "Get tag service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Get Tags/Search Tags/Get Tags By App
     *
     * Connects to a facade and handles the different states
     * 
     * @param string $lang
     * @param string $app
     * @param string $like
     * @param string $state
     * @return ResponseBootstrap
     */
    public function getTags(string $lang, string $state = null, string $app = null, string $like = null): ResponseBootstrap {

        try {
            // Create Response Object
            $response = new ResponseBootstrap();


            // Create facade object and call neccessary functions for retreving data
            $facade = new GetTagsFacade($lang, $app, $like ,$state, $this->tagsMapper);
            $res = $facade->handleTags();


//                        $response->setStatus(200);
//            $response->setMessage('Success');
//            $response->setData(
//                ["lang" => $state]
//            );
//
//return $response;

            // convert data to array for appropriate response
            if(gettype($res) === 'object'){
                $data = [];

                for($i = 0; $i < count($res); $i++){
                    $data[$i]['id'] = $res[$i]->getId();
                    $data[$i]['name'] = $res[$i]->getName();
                    $data[$i]['language'] = $res[$i]->getLang();
                    $data[$i]['state'] = $res[$i]->getState();
                    $data[$i]['behavior'] = $res[$i]->getBehavior();
                }
            }else if(gettype($res) === 'array'){
                $data = $res;
            }


            // check data and set response
            if(!empty($data)){
                $response->setStatus(200);
                $response->setMessage('Success');
                $response->setData(
                    $data
                );
            }else {
                $response->setStatus(204);
                $response->setMessage('No content');
            }

            // return data
            return $response;

        }catch(Exception $e){
            // send monolog record
            $this->getMonologInstance()->sendMonologRecord($this->configuration, 1000, "Get tags service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Get list of tags
     *
     * @param int $from
     * @param int $limit
     * @return ResponseBootstrap
     */
    public function getListOfTags(int $from, int $limit, string $state = null, string $lang = null):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create entity and set its values
            $entity = new Tags();
            $entity->setFrom($from);
            $entity->setLimit($limit);
            $entity->setState($state);
            $entity->setLang($lang);

            // call mapper for data
            $data = $this->tagsMapper->getList($entity);

            // set response according to data content
            if(!empty($data)){
                $response->setStatus(200);
                $response->setMessage('Success');
                $response->setData(
                    $data
                );
            }else {
                $response->setStatus(204);
                $response->setMessage('No content');
            }

            // return data
            return $response;

        }catch (\Exception $e){
            // send monolog record
            $this->getMonologInstance()->sendMonologRecord($this->configuration, 1000, "Get tags list service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Get tags by given ids, lang and state parameters
     *
     * @param array $ids
     * @param string $lang
     * @param string $state
     * @return ResponseBootstrap
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getTagsById(array $ids, string $lang, string $state):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // set ids as a string
            $identifier = implode(',', $ids);

            // create cashing adapter
            $cache = new PhpArrayAdapter(
            // single file where values are cached
                __DIR__ . '/cached_files/' . $identifier . '.cache',
                // a backup adapter, if you set values after warmup
                new FilesystemAdapter()
            );

            // get identifier
            $ids_identifier = $cache->getItem($identifier);

            // loop through cached responses and check if there is an identifier match
            $dir = "../src/Model/Service/cached_files/*";
            foreach(glob($dir) as $file)
            {
                $filenamePartOne = substr($file, 34);
                $position = strpos($filenamePartOne, '.');
                $filename = substr($filenamePartOne, 0, $position);

                // check if filename is equal to the given ids
                if($ids_identifier->getKey() == $filename){
                    // if yes get cached data
                    $cacheItem = $cache->getItem('raw.tags');
                    $data = $cacheItem->get();
                }
            }

       // $data = [];  // Disable caching

            // fetch data from database if there is no cached response
            if(empty($data)){
                // create entity and set its values
                $entity = new Tags();
                $entity->setLang($lang);
                $entity->setIds($ids);
                $entity->setState($state);

                // get data from database
                $res = $this->tagsMapper->getTagsById($entity);

                // convert data to array for appropriate response
                $data = [];
                for($i = 0; $i < count($res); $i++){
                    $data[$i]['id'] = $res[$i]->getId();
                    $data[$i]['name'] = $res[$i]->getName();
                    $data[$i]['language'] = $res[$i]->getLang();
                    $data[$i]['state'] = $res[$i]->getState();
                    $data[$i]['behavior'] = $res[$i]->getBehavior();
                    $data[$i]['version'] = $res[$i]->getVersion();
                }

                // cache data
                $values = array(
                    'id' => $identifier,
                    'raw.tags' => $data,
                );
                $cache->warmUp($values);
            }

            // check data and set response
            if(!empty($data)){
                $response->setStatus(200);
                $response->setMessage('Success');
                $response->setData(
                    $data
                );
            }else {
                $response->setStatus(204);
                $response->setMessage('No content');
            }

            // return data
            return $response;

        }catch(Exception $e){
            // send monolog record
            $this->getMonologInstance()->sendMonologRecord($this->configuration, 1000, "Get tags by id service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }

    }


    /**
     * Delete tag service
     *
     * @param int $id
     * @return ResponseBootstrap
     */
    public function deleteTag(int $id):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create entity and set its values
            $entity = new Tags();
            $entity->setId($id);

            // get response from database
            $res = $this->tagsMapper->deleteTag($entity)->getResponse();

            // check data and set response
            if($res[0] == 200){
                $response->setStatus(200);
                $response->setMessage('Success');
            }else {
                $response->setStatus(304);
                $response->setMessage('Not modified');
            }

            // return response
            return $response;

        }catch(Exception $e){
            // send monolog record
            $this->getMonologInstance()->sendMonologRecord($this->configuration, 1000, "Delete tag service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Release tag service
     *
     * @param int $id
     * @return ResponseBootstrap
     */
    public function releaseTag(int $id):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create entity and set its values
            $entity = new Tags();
            $entity->setId($id);

            // get response from database
            $res = $this->tagsMapper->releaseTag($entity)->getResponse();

            // check data and set response
            if($res[0] == 200){
                $response->setStatus(200);
                $response->setMessage('Success');
            }else {
                $response->setStatus(304);
                $response->setMessage('Not modified');
            }

            // return response
            return $response;

        }catch (Exception $e){
            // send monolog record
            $this->getMonologInstance()->sendMonologRecord($this->configuration, 1000, "Release tag service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Create tag service
     *
     * @param NamesCollection $names
     * @param string $behavior
     * @return ResponseBootstrap
     */
    public function createTag(NamesCollection $names, string $behavior):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create entity and set its values
            $entity = new Tags();
            $entity->setCollection($names);
            $entity->setBehavior($behavior);

            // get response from database
            $res = $this->tagsMapper->createTag($entity)->getResponse();

            // check data and set response
            if($res[0] == 200){
                $response->setStatus(200);
                $response->setMessage('Success');
            }else {
                $response->setStatus(304);
                $response->setMessage('Not modified');
            }

            // return response
            return $response;

        }catch(Exception $e){
            // send monolog record
            $this->getMonologInstance()->sendMonologRecord($this->configuration, 1000, "Create tag service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Edit tag service
     *
     * @param int $id
     * @param NamesCollection $names
     * @param string $behavior
     * @return ResponseBootstrap
     */
    public function editTag(int $id, NamesCollection $names, string $behavior):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create entity and set its values
            $entity = new Tags();
            $entity->setId($id);
            $entity->setCollection($names);
            $entity->setBehavior($behavior);

            // get response from database
            $res = $this->tagsMapper->editTag($entity)->getResponse();

            // check data and set response
            if($res[0] == 200){
                $response->setStatus(200);
                $response->setMessage('Success');
            }else {
                $response->setStatus(304);
                $response->setMessage('Not modified');
            }

            // return response
            return $response;

        }catch(Exception $e){
            // send monolog record
            $this->getMonologInstance()->sendMonologRecord($this->configuration, 1000, "Edit tag service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Get total number of tags
     *
     * @return ResponseBootstrap
     */
    public function getTotal():ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // call mapper for data
            $data = $this->tagsMapper->getTotal();

            // check data and set response
            if(!empty($data)){
                $response->setStatus(200);
                $response->setMessage('Success');
                $response->setData([
                    $data
                ]);
            }else {
                $response->setStatus(204);
                $response->setMessage('No content');
            }

            // return response
            return $response;

        }catch (Exception $e){
            // send monolog record
            $this->getMonologInstance()->sendMonologRecord($this->configuration, 1000, "Get total tags service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Get monolog instance
     *
     * @return MonologSender
     */
    public function getMonologInstance(){
        $monologHelper = new MonologSender();
        return $monologHelper;
    }
}