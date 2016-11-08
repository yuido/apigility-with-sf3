Apigility Skeleton Application with Symfony3 integration
========================================================

The goal of this project is to use Apigility to take advantage of the powerful
API's creation and management tools offered by Apigility and, at the same time,
to use Symfony3 framework to add the required operation functionality.

# How to integrate both frameworks

Clone this project and deploy a Sf3 project:

    git clone https://github.com/yuido/apigility-with-sf3
    symfony new sf

Install vendors:

    cd apigility-with-sf3
    composer.phar install
    

Important: the Symfony3 project mush be deployed at the same level that 
apigility-with-sf3 directory and must be named ``sf``.

Et voilá, you are ready to use the power of both frameworks.

# Example1. RESTFul service with doctrine entities

Enable the development mode and start the php dev server to execute apigility
    
    cd apigility-with-sf3
    composer.phar development-enable
    php -S 0.0.0.0:8080 -ddisplay_errors=0 -t public public/index.php

Creates one new API named ``Library`` and add a new REST Services:

- ``book`` with a field ``title``

The code for this operation have been created in ``module/Library`` directory.
Now, in order to use doctrine entities from the Sf3 project we have to add/change
some code.

First add the following array to ``module/Library/config/module.config.php``:

```php
'zf-hal' => [
    'metadata_map' => [
        ....

        \AppBundle\Entity\Book::class => [
            'identifier_name' => 'id',
            'route_name' => 'library.rest.book',
            'hydrator' => 'ClassMethods',
        ],

```

This code allows to create a HAL response from doctrine entity objects and 
object arrays returned in {Entity}Resource (BookResource and AuthorResource)
methods.


Now swtich to the sf project:

    cd ../sf

Let's create the ``book`` entity.

Book:

```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Book
 *
 * @ORM\Table(name="book")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BookRepository")
 */
class Book
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;
    
    /**
     *
     * @ORM\ManyToOne(targetEntity="Author", inversedBy="books")
     */
    private $author;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Book
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set author
     *
     * @param \AppBundle\Entity\Author $author
     *
     * @return Book
     */
    public function setAuthor(\AppBundle\Entity\Author $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \AppBundle\Entity\Author
     */
    public function getAuthor()
    {
        return $this->author;
    }
}
```

These entity must to have the same fields than the operation we have just 
created in apigility.

And finally we are ready to add the code to the apigility Resources, where we
can use the doctrine entity manager as a service.

``module/Library/src/V1/Rest/Book/BookResource.php``

```php
<?php
namespace Biblioteca\V1\Rest\Book;

use ZF\ApiProblem\ApiProblem;
#use ZF\Rest\AbstractResourceListener;
use Yuido\ResourceListener;

class BookResource extends ResourceListener
{
    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        $book = new \AppBundle\Entity\Book();
        
        $book->setTitle($data->title);
       
        $this->doctrine->persist($book);
        $this->doctrine->flush();
        
        return $book;
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
    }

    /**
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function deleteList($data)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
    }

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        return new ApiProblem(405, 'The GET method has not been defined for individual resources');
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
        $books = $this->doctrine->getRepository('AppBundle:Book')->findAll($params);
        
        return $books;
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
    }

    /**
     * Patch (partial in-place update) a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patchList($data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for collections');
    }

    /**
     * Replace a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function replaceList($data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }

    /**
     * Update a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }
}


```

We have only show the code for ``create`` and ``fetchAll``, the rest of the methods
have to be implemented in a similar way.

It is very important to change ``ZF\Rest\AbstractResourceListener`` by 
``Yuido\ResourceListener``. This last class implements the needed code to
bootstrap the Sf3 kernel and get access to the doctrine entity manager.

Let's take a look at such file:

```php 
<?php

namespace Yuido;

use ZF\Rest\AbstractResourceListener;
use Doctrine\Common\Annotations\AnnotationRegistry;  


class ResourceListener extends AbstractResourceListener{
    protected $doctrine;

    public function __construct() {
        $symfonyApp = __DIR__ . '/../../../sf';
        require_once $symfonyApp . '/app/AppKernel.php';
        $kernel = new \AppKernel('prod', true);
        $kernel->loadClassCache();
        $kernel->boot();
        
        AnnotationRegistry::registerFile(__DIR__. "/../../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php");
        
        $this->doctrine = $kernel->getContainer()->get('doctrine')->getManager();
    }
}

```
If we need to access to whatever symfony service we want, we just have to 
instanciate it and assign to a ResourceListener attribute.

    


