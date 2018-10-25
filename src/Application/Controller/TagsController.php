<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 6/27/18
 * Time: 3:21 PM
 */

namespace Application\Controller;


use Model\Entity\Names;
use Model\Entity\NamesCollection;
use Model\Entity\ResponseBootstrap;
use Model\Service\TagsService;
use Symfony\Component\HttpFoundation\Request;

class TagsController
{

    private $tagsService;
    

    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    
    /**
     * Get Tag
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function get(Request $request):ResponseBootstrap {
        // get data
        $id = $request->get('id');
        $lang = $request->get('lang');
        $state = $request->get('state');

        // create response object
        $response = new ResponseBootstrap();

        // check if required data is present
        if(isset($id) && isset($lang) && isset($state)){
            return $this->tagsService->getTag($id, $lang, $state);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        return $response;
    }


    public function getList(Request $request):ResponseBootstrap {
        // get data
        $from = $request->get('from');
        $limit = $request->get('limit');
        $state = $request->get('state');
        $lang = $request->get('lang');

        // create response object
        $response = new ResponseBootstrap();

        // check if parameters are present
        if(isset($from) && isset($limit)){ //  && isset($state)
            return $this->tagsService->getListOfTags($from, $limit, $state, $lang);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return data
        return $response;
    }


    /**
    * Get Tags by specified parametars
    *
    * @param Request $request
    * @return ResponseBootstrap
    */
   public function getTags(Request $request):ResponseBootstrap {
        // Params
        $lang = $request->get('lang');
        $app = $request->get('app');
        $like = $request->get('like');
        $state = $request->get('state');

        // create response object
        $response = new ResponseBootstrap();
        
        // check if required data is present
        if(isset($lang)){ //  && isset($state)
            return $this->tagsService->getTags($lang, $state, $app, $like);
        }else{
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return data
        return $response;
    }


    /**
     * Get Tags by ids
     * 
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function getIds(Request $request):ResponseBootstrap {
        // get data
        $ids = $request->get('ids');
        $lang = $request->get('lang');
        $state = $request->get('state');

        // to array
        $ids = explode(',', $ids);

        // create response object
        $response = new ResponseBootstrap();

        // check if required data is present
        if(!empty($ids) && !empty($lang) && !empty($state)){
            return $this->tagsService->getTagsById($ids, $lang, $state);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return data
        return $response;
    }


    /**
     * Delete controller
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function delete(Request $request):ResponseBootstrap {
        // get data
        $id = $request->get('id');

        // create response object
        $response = new ResponseBootstrap();

        // check if id is present
        if(isset($id)){
            return $this->tagsService->deleteTag($id);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return response
        return $response;
    }


    /**
     * Release tag
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function postRelease(Request $request):ResponseBootstrap {
        // get data
        $data = json_decode($request->getContent(), true);
        $id = $data['id'];

        // create response object
        $response = new ResponseBootstrap();

        // check if id is set
        if(isset($id)) {
            return $this->tagsService->releaseTag($id);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return response
        return $response;
    }


    /**
     * Add tag
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function post(Request $request):ResponseBootstrap {
        // get data
        $data = json_decode($request->getContent(), true);
        $names = $data['names'];
        $behavior = $data['behavior'];

        // create collection object
        $namesCollection = new NamesCollection();

        // set names into names collection
        foreach($names as $name){
            $temp = new Names();
            $temp->setName($name['name']);
            $temp->setLang($name['lang']);

            $namesCollection->addEntity($temp);
        }

        // create response object
        $response = new ResponseBootstrap();

        // check data
        if(!empty($namesCollection) && !empty($behavior)){
            return $this->tagsService->createTag($namesCollection, $behavior);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return response
        return $response;
    }


    /**
     * Edit tag
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function put(Request $request):ResponseBootstrap {
        // get data
        $data = json_decode($request->getContent(), true);
        $id = $data['id'];
        $names = $data['names'];
        $behavior = $data['behavior'];

        // create collection object
        $namesCollection = new NamesCollection();

        // set names into names collection
        foreach($names as $name){
            $temp = new Names();
            $temp->setName($name['name']);
            $temp->setLang($name['lang']);

            $namesCollection->addEntity($temp);
        }

        // create response object
        $response = new ResponseBootstrap();

        // check data
        if(isset($id) && isset($namesCollection) && isset($behavior)){
            return $this->tagsService->editTag($id, $namesCollection, $behavior);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return response
        return $response;
    }


    /**
     * Get total number of tags
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function getTotal(Request $request):ResponseBootstrap {
        // call service for response
        return $this->tagsService->getTotal();
    }

}