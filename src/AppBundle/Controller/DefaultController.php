<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Article;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Serializer\SerializerInterface;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        //dump($this->get('kernel')->getEnvironment());

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/articles/get/{id}", name="article_show")
     */
    public function showAction(Article $article, SerializerInterface $serializer)
    {
        $data = $serializer->serialize($article,'json');

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/articles", name="article_create")
     * @Method({"POST"})
     */
    public function createAction(Request $request, SerializerInterface $serializer)
    {
        $data_param = $request->getContent();
        $article = null;

        if (!empty($data_param))
        {
            try {
                $article = $serializer->deserialize($data_param, Article::class, 'json');
            } catch (\Exception $e)
            {
                return new Response('', Response::HTTP_BAD_REQUEST);
            }
        }
        else if(!empty($request->request->get("content")))
        {
            $article = new Article();
            $article->setContent($request->request->get("content"));
            $article->setTitle("title");
        }
        else if(!empty($request->request->get("json")))
        {
            try {
                $article = $serializer->deserialize($request->request->get("json"), Article::class, 'json');
            } catch (\Exception $e)
            {
                return new Response('', Response::HTTP_BAD_REQUEST);
            }
        }
        else
        {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        if($article->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
        }
        else
        {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        return new Response('', Response::HTTP_CREATED);
    }

    /**
     * @Route("/articles/update/{id}", name="article_modify")
     * @Method({"PUT"})
     */
    public function modifyAction(Article $article, Request $request)
    {
        $data = $request->getContent();

        if (!empty($data))
            $article = $this->get('jms_serializer')->deserialize($data, 'AppBundle\Entity\Article', 'json');
        else
        {
            if(!empty($request->request->get("content")))
                $article->setContent($request->request->get("content"));
            if(!empty($request->request->get("title")))
                $article->setTitle("title");
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        return new Response('', Response::HTTP_CREATED);
    }

    /**
     * @Route("/articles", name="article_list")
     * @Method({"GET"})
     */
    public function listAction(SerializerInterface $serializer)
    {
        $articles = $this->getDoctrine()->getRepository('AppBundle:Article')->findAll();
        $data = $serializer->serialize($articles,'json');

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
