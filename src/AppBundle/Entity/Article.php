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

    public function discover()
    {
        $autoDiscover['GET'] = array("url" => "http://127.0.0.1/TestSymfony/web/app_dev.php", 'uri' => '/articles/'.$this->getId(), "method" => "GET");
        $autoDiscover['ADD'] = array("url" => "http://127.0.0.1/TestSymfony/web/app_dev.php", 'uri' => '/articles/'.$this->getId(), "method" => "POST");
        $autoDiscover['DELETE'] = array("url" => "http://127.0.0.1/TestSymfony/web/app_dev.php", 'uri' => '/articles/'.$this->getId(), "method" => "DELETE");
        $autoDiscover['MODIFY'] = array("url" => "http://127.0.0.1/TestSymfony/web/app_dev.php", 'uri' => '/articles/'.$this->getId(), "method" => "PUT");
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