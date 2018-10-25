<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 6/27/18
 * Time: 3:22 PM
 */

namespace Model\Mapper;

use Model\Core\Helper\CacheDeleter\DeleteCache;
use Model\Core\Helper\Monolog\MonologSender;
use Model\Entity\Shared;
use PDO;
use PDOException;
use Component\DataMapper;
use Model\Entity\Tags;
use Model\Entity\TagsCollection;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Zend\Code\Generator\DocBlock\Tag;

class TagsMapper extends DataMapper
{

    public function getConfiguration()
    {
        return $this->configuration;
    }


    /**
     * Get single tag
     *
     * @param Tags $entity
     * @return Tags
     */
    public function getTag(Tags $entity):Tags {

        // create response object
        $response = new Tags();

        try {
            // set database instructions
            $sql = "SELECT 
                        t.id,
                        t.behaviour,
                        t.state,
                        t.version,
                        tn.name,
                        tn.language
                    FROM tag AS t
                    INNER JOIN tag_name AS tn ON t.id = tn.tag_parent
                    WHERE t.state = ?
                    AND t.id = ?
                    AND tn.language = ?";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $entity->getState(),
                $entity->getId(),
                $entity->getLang()
            ]);

            // fetch data
            $data = $statement->fetch();

            // set response
            if($statement->rowCount() > 0){
                $response->setId($data['id']);
                $response->setName($data['name']);
                $response->setLang($data['language']);
                $response->setState($data['state']);
                $response->setBehavior($data['behaviour']);
            }

        }catch(PDOException $e){
            // send monolog record in case of failure
            $this->getMonologInstance()->sendMonologRecord($this->configuration, $e->errorInfo[1], "Get tag mapper: " . $e->getMessage());
        }

        // return data
        return $response;
    }


    /**
     * Get all tags with given state and language
     *
     * @param Tags $entity
     * @return TagsCollection
     */
    public function getTags(Tags $entity):TagsCollection {

        // create response object
        $tagsCollection = new TagsCollection();

        try {

            // get state
            $state = $entity->getState();

            // check state and set appropriate query
            if($state === null or $state === ''){
                // set database instructions
                $sql = "SELECT 
                        t.id,
                        t.behaviour,
                        t.state,
                        t.version,
                        tn.name,
                        tn.language,
                        tn.tag_parent
                    FROM tag AS t 
                    INNER JOIN tag_name AS tn ON t.id = tn.tag_parent
                    WHERE tn.language = ?";

                    $statement = $this->connection->prepare($sql);
                    $statement->execute([
                        $entity->getLang()
                    ]);
            }else {
                // set database instructions
                $sql = "SELECT 
                        t.id,
                        t.behaviour,
                        t.state,
                        t.version,
                        tn.name,
                        tn.language,
                        tn.tag_parent
                    FROM tag AS t 
                    INNER JOIN tag_name AS tn ON t.id = tn.tag_parent
                    WHERE t.state = ?
                    AND tn.language = ?";
                $statement = $this->connection->prepare($sql);
                $statement->execute([
                    $entity->getState(),
                    $entity->getLang()
                ]);
            }

            // loop through fetched data and set tag values
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                // create tag entity
                $tags = new Tags();

                // set its values
                $tags->setName($row['name']);
                $tags->setLang($row['language']);
                $tags->setId($row['id']);
                $tags->setState($row['state']);
                $tags->setBehavior($row['behaviour']);

                // add to tags collection
                $tagsCollection->addEntity($tags);
            }

            // set responce according to results of previous actions
            if($statement->rowCount() == 0){
                $tagsCollection->setStatusCode(204);
            }else {
                $tagsCollection->setStatusCode(200);
            }

        }catch(PDOException $e){
            $tagsCollection->setStatusCode(204);
            // send monolog record in case of failure
            $this->getMonologInstance()->sendMonologRecord($this->configuration, $e->errorInfo[1], "Get tags mapper: " . $e->getMessage());
        }

        // return data
        return $tagsCollection;
    }



    public function getList(Tags $tags){

        try {

            // get state
            $state = $tags->getState();

            // check if state is set and set query
            if($state === null or $state === ''){
                // set database instructions
                $sql = "SELECT
                            t.id,
                            t.behaviour,
                            t.state,
                            tn.name,
                            tn.language
                        FROM tag AS t
                        INNER JOIN tag_name AS tn ON t.id = tn.tag_parent
                        LIMIT :from,:limit";
                // set statement
                $statement = $this->connection->prepare($sql);
                // set from and limit as core variables
                $from = $tags->getFrom();
                $limit = $tags->getLimit();
                // bind parametars
                $statement->bindParam(':from', $from, PDO::PARAM_INT);
                $statement->bindParam(':limit', $limit, PDO::PARAM_INT);
                // execute query
                $statement->execute();

            }else {
                // set database instructions
                $sql = "SELECT
                            t.id,
                            t.behaviour,
                            t.state,
                            tn.name,
                            tn.language
                        FROM tag AS t
                        INNER JOIN tag_name AS tn ON t.id = tn.tag_parent
                        WHERE tn.language = :lang AND t.state = :state
                        LIMIT :from,:limit";
                // set statement
                $statement = $this->connection->prepare($sql);
                // set from and limit as core variables
                $from = $tags->getFrom();
                $limit = $tags->getLimit();
                $language = $tags->getLang();

                // bind parametars
                $statement->bindParam(':from', $from, PDO::PARAM_INT);
                $statement->bindParam(':limit', $limit, PDO::PARAM_INT);
                $statement->bindParam(':state', $state);
                $statement->bindParam(':lang', $language);
                // execute query
                $statement->execute();
            }


            // set data
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);


        }catch (PDOException $e){
            $data = [];
            // send monolog record in case of failure
            $this->getMonologInstance()->sendMonologRecord($this->configuration, $e->errorInfo[1], "List tags mapper: " . $e->getMessage());
        }

        // return data
        return $data;
    }



    /**
     * Search tags
     *
     * @param Tags $entity
     * @return TagsCollection
     */
    public function searchTags(Tags $entity):TagsCollection {

        // create response object
        $tagsCollection = new TagsCollection();

        try {
            // set database instructions
            $sql = "SELECT 
                        t.id,
                        t.behaviour,
                        t.state,
                        t.version,
                        tn.name,
                        tn.language,
                        tn.tag_parent
                    FROM tag AS t 
                    INNER JOIN tag_name AS tn ON t.id = tn.tag_parent
                    WHERE name LIKE ?
                    AND tn.language = ?
                    AND t.state = ?
                    AND tn.name LIKE ?";
            $name = '%' . $entity->getName() . '%';
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $name,
                $entity->getLang(),
                $entity->getState(),
                $name
            ]);

            // loop through fetched data and set entities values
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                // create entity
                $tags = new Tags();

                // set its values
                $tags->setName($row['name']);
                $tags->setLang($row['language']);
                $tags->setId($row['id']);
                $tags->setState($row['state']);
                $tags->setBehavior($row['behaviour']);
                $tags->setTagParent($row['tag_parent']);

                // add to tags collection
                $tagsCollection->addEntity($tags);
            }

            // set response according to results of previous actions
            if($statement->rowCount() == 0){
                $tagsCollection->setStatusCode(204);
            }else {
                $tagsCollection->setStatusCode(200);
            }

        }catch(PDOException $e){
            $tagsCollection->setStatusCode(204);
            // send monolog record in case of failure
            $this->getMonologInstance()->sendMonologRecord($this->configuration, $e->errorInfo[1], "Search tags mapper: " . $e->getMessage());
        }

        // return data
        return $tagsCollection;
    }


    /**
     * Get tags by given ids
     *
     * @param Tags $tags
     * @return TagsCollection
     */
    public function getTagsById(Tags $tags):TagsCollection {

        // create response object
        $tagsCollection = new TagsCollection();

        // use helper function to convert array into comma separated string
        $whereIn = $this->sqlHelper->whereIn($tags->getIds());
    
        try {
            // set database instructions
            $sql = "SELECT
                        t.id,
                        t.behaviour,
                        t.state,
                        t.version,
                        tn.name,
                        tn.language
                    FROM tag AS t
                    INNER JOIN tag_name AS tn ON tn.tag_parent = t.id
                    WHERE t.id IN(".$whereIn.")
                    AND t.state = ?
                    AND tn.language = ?";
            $statement = $this->connection->prepare($sql);
            $statement->execute(
                [
                    $tags->getState(),
                    $tags->getLang()
                ]
            );

            // fetch data
            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

            // loop through data and set tags values
            foreach($rows as $row) {
                // create new entity
                $tags = new Tags();

                // set entity values
                $tags->setName($row['name']);
                $tags->setLang($row['language']);
                $tags->setId($row['id']);
                $tags->setState($row['state']);
                $tags->setBehavior($row['behaviour']);
                $tags->setVersion($row['version']);

                // add entity to tags collection
                $tagsCollection->addEntity($tags);
            }

            // set response according to results of previous actions
            if($statement->rowCount() == 0){
                $tagsCollection->setStatusCode(204);
            }else {
                $tagsCollection->setStatusCode(200);
            }

        }catch(PDOException $e){
            $tagsCollection->setStatusCode(204);
            // send monolog record in case of failure
            $this->getMonologInstance()->sendMonologRecord($this->configuration, $e->errorInfo[1], "Get tags by id mapper: " . $e->getMessage());
        }

        // return data
        return $tagsCollection;
    }


    /**
     * Delete tag mapper
     *
     * @param Tags $entity
     * @return Shared
     */
    public function deleteTag(Tags $entity):Shared {

        // create response object
        $shared = new Shared();

        try {
            // begin transaction
            $this->connection->beginTransaction();

            // set database instructions
            $sql = "DELETE 
                        tg.*,
                        tgn.*,
                        tga.*,
                        tgna.*
                    FROM tag AS tg 
                    LEFT JOIN tag_name AS tgn ON tg.id = tgn.tag_parent
                    LEFT JOIN tag_audit AS tga ON tg.id = tga.tag_parent
                    LEFT JOIN tag_name_audit AS tgna ON tgn.id = tgna.tag_name_parent    
                    WHERE tg.id = ?
                    AND tg.state != 'R'";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $entity->getId()
            ]);

            // set response according to results of previous actions
            if($statement->rowCount() > 0){
                $shared->setResponse([200]);
            }else {
                $shared->setResponse([304]);
            }

            // commit transaction
            $this->connection->commit();

        }catch(PDOException $e){
            // rollback everything in case of failure
            $this->connection->rollBack();
            $shared->setResponse([304]);

            // send monolog record in  case of failure
            $this->getMonologInstance()->sendMonologRecord($this->configuration, $e->errorInfo[1], "Delete tag mapper: " . $e->getMessage());
        }

        // return response
        return $shared;
    }


    /**
     * Release tag mapper
     *
     * @param Tags $entity
     * @return Shared
     */
    public function releaseTag(Tags $entity):Shared {

        // create response object
        $shared = new Shared();

        try {
            // begin transaction
            $this->connection->beginTransaction();

            // set database instructions
            $sql = "UPDATE 
                      tag  
                    SET state = 'R'
                    WHERE id = ?";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $entity->getId()
            ]);

            // set response values and update tag version
            if($statement->rowCount() > 0){
                $shared->setResponse([200]);

                //Version id
                $version = $this->lastVersion();

                //Update tag with id
                $sql = "UPDATE tag SET version = ? WHERE id = ?";
                $statement = $this->connection->prepare($sql);
                $statement->execute(
                    [
                        $version,
                        $entity->getId()
                    ]
                );

            }else {
                $shared->setResponse([304]);
            }

            // commit transaction
            $this->connection->commit();

        }catch(PDOException $e){
            // rollback everything in case of failure
            $this->connection->rollBack();
            $shared->setResponse([304]);

            // send monolog record in case of failure
            $this->getMonologInstance()->sendMonologRecord($this->configuration, $e->errorInfo[1], "Release tag mapper: " . $e->getMessage());
        }

        // return response
        return $shared;
    }


    /**
     * Insert tag
     *
     * @param Tags $entity
     * @return Shared
     */
    public function createTag(Tags $entity):Shared {

        // create response object
        $shared = new Shared();

        try {
            // begin transaction
            $this->connection->beginTransaction();

            // get last version
            $lastVersionId = $this->lastVersion();

            // set database instructins for inserting tag
            $sql = "INSERT INTO
                       tag (state, behaviour, version) 
                       VALUES('P', ?, ?)";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $entity->getBehavior(),
                $lastVersionId
            ]);

            // if tag is inserted, insert its names
            if($statement->rowCount() > 0){

                // get last id for the value of tag parent
                $lastId = $this->connection->lastInsertId();

                // set database instructions for inserting names of the tags
                $sql = "INSERT INTO
                            tag_name (name, language, tag_parent)
                            VALUES (?,?,?)";
                $statement = $this->connection->prepare($sql);

                // loop through collection and insert data
                $names = $entity->getCollection();
                for($i = 0; $i < count($names); $i++){
                    // extract name and laguage from collection
                    $name = $names[$i]->getName();
                    $language = $names[$i]->getLang();

                    // execute inserting
                    $statement->execute([
                        $name,
                        $language,
                        $lastId
                    ]);
                }

                // set response
                $shared->setResponse([200]);

            }else {
                $shared->setResponse([304]);
            }

            // commit transaction
            $this->connection->commit();

        }catch(PDOException $e){
            // rollback everything in case of any failure
            $this->connection->rollBack();
            $shared->setResponse([304]);

            // send monolog record in case of failure
            $this->getMonologInstance()->sendMonologRecord($this->configuration, $e->errorInfo[1], "Create tag mapper: " . $e->getMessage());
        }

        // return response
        return $shared;
    }


    /**
     * Edit tag mapper
     *
     * @param Tags $entity
     * @return Shared
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function editTag(Tags $entity):Shared {

        // create response object
        $shared = new Shared();

        try {
            // begin transaction
            $this->connection->beginTransaction();

            // update behavior of the tag if neccessary
            $sql = "UPDATE tag SET behaviour = ? WHERE id = ?";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $entity->getBehavior(),
                $entity->getId()
            ]);

            // if behavior is changed, update tags version
            if($statement->rowCount() > 0){
                // get version
                $lastVersion = $this->lastVersion();

                // set database instructions
                $sql = "UPDATE tag SET version = ? WHERE id = ?";
                $statement = $this->connection->prepare($sql);
                $statement->execute([
                    $lastVersion,
                    $entity->getId()
                ]);

                // send requests for deleting all cached files at exercise, workouts and mobile MS
                $deleter = new DeleteCache($this->configuration);
                $deleter->deleteCacheAtParentMicroservices();
            }

            // insert names for tags
            $sql = "INSERT INTO
                        tag_name (name, language, tag_parent)
                        VALUES (?,?,?)
                    ON DUPLICATE KEY
                    UPDATE 
                        name = VALUES(name),
                        language = VALUES(language),
                        tag_parent = VALUES(tag_parent)";
            $statement = $this->connection->prepare($sql);

            // loop through collection and insert data
            $names = $entity->getCollection();
            foreach($names as $name){
                $statement->execute([
                    $name->getName(),
                    $name->getLang(),
                    $entity->getId()
                ]);
            }

            // set response according to results of previous actions
            if($statement->rowCount() > 0){
                $shared->setResponse([200]);
            }else {
                $shared->setResponse([304]);
            }

            // commit transaction
            $this->connection->commit();

        }catch(PDOException $e){
            // rollback everything in case of any failure
            $this->connection->rollBack();
            $shared->setResponse([304]);

            // send monolog record in case of failure
            $this->getMonologInstance()->sendMonologRecord($this->configuration, $e->errorInfo[1], "Edit tag mapper: " . $e->getMessage());
        }

        // return response
        return $shared;
    }


    /**
     * Get total number of tags
     *
     * @return null
     */
    public function getTotal() {

        try {

            // set database instructions
            $sql = "SELECT COUNT(*) as total FROM tag";
            $statement = $this->connection->prepare($sql);
            $statement->execute();

            // fetch total number
            $total = $statement->fetch(PDO::FETCH_ASSOC)['total'];

        }catch(PDOException $e){
            $total = null;

            // send monolog record in case of failure
            $this->getMonologInstance()->sendMonologRecord($this->configuration, $e->errorInfo[1], "Get total tags mapper: " . $e->getMessage());
        }

        return $total;
    }


    /**
     * Get last version number function
     *
     * @return string
     */
    public function lastVersion(){
        // set database instructions
        $sql = "INSERT INTO version VALUES(null)";
        $statement = $this->connection->prepare($sql);
        $statement->execute([]);

        // set last id
        $lastId = $this->connection->lastInsertId();

        // return last id
        return $lastId;
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