<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table()
 *
 * @Serializer\ExclusionPolicy("ALL")
 */
class Article
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank()
     *
     * @Serializer\Expose
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     *
     * @Serializer\Expose
     */
    private $content;


    private $_link;

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->_link;
    }

    /**
     * @param mixed $link
     */
    public function setLink($link)
    {
        $this->_link = $link;
    }

    public function __construct()
    {
        $this->_link = $this->discover();
    }


    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function discover($_url, $_links, $_route)
    {
        $id_array = array('id' => $this->getId());

        foreach($_links as $action => $route)
        {
            $_method = $_route->getRouteCollection()->get($route)->getMethods();
            $autoDiscover[$action] = array("url" => $_url, 'uri' => $_route->generate($route, $id_array), "method" => $_method[0]);
        }
        return $autoDiscover;
    }

    public function diff_json(Article $_article)
    {
        if(!empty($_article->getContent()) && $_article->getContent() != null)
            $this->setContent($_article->getContent());
        if(!empty($_article->getTitle()) && $_article->getTitle() != null)
            $this->setTitle($_article->getTitle());
    }
}