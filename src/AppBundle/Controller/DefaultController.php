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
     * @Route("/{json}", name="homepage")
     */
    public function indexAction($json = null, Request $request, SerializerInterface $serializer)
    {
        //dump($this->get('kernel')->getEnvironment());
        //dump($serializer->deserialize($request->request->get("json"), null, 'json'));
        //dump($json);
        //$data_param = $request->getContent();
       // dump($data_param);

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/articles/{id}", name="article_show", requirements = {"id"="\d+"})
     */
    public function showAction(Article $article, SerializerInterface $serializer)
    {
        $normalizers = new \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer();
        $json_array = $normalizers->normalize($article);

        $json_array['_link'] = $article->discover();

        $data = $serializer->serialize($json_array,'json');

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/articles/{json}", name="article_create", requirements = {"id"="\d+"})
     * @Method({"POST"})
*/
    public function createAction($json = null, Request $request, SerializerInterface $serializer)
    {
        $data_param = $request->getContent();
        $article = null;

        if(!empty($request->request->get("content")))
        {
            $article = new Article();
            $article->setContent($request->request->get("content"));
            $article->setTitle("title");
        }
        else if (!empty($data_param))
            $article = $serializer->deserialize($data_param, Article::class, 'json');
        else if($json != null)
            $article = $serializer->deserialize($json, Article::class, 'json');
        else
            return new Response('', Response::HTTP_BAD_REQUEST);

        $errors = $this->get('validator')->validate($article);

        if(count($errors)) {
            //dump($errors);
            $data = $serializer->serialize($errors,'json');
            return new Response($data, Response::HTTP_BAD_REQUEST);
        }
        else
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
            return new Response('', Response::HTTP_CREATED);
        }
    }

    /**
     * @Route("/articles/{id}/{json}", name="article_modify", requirements = {"id"="\d+"})
     * @Method({"PUT"})
     */
    public function modifyAction($json = null, Article $article, Request $request, SerializerInterface $serializer)
    {
        $data = $request->getContent();

        if(!empty($request->request->get("content")) || !empty($request->request->get("title")))
        {
            if(!empty($request->request->get("content")))
                $article->setContent($request->request->get("content"));
            if(!empty($request->request->get("title")))
                $article->setTitle("title");
        }
        else if (!empty($data_param))
            $article->diff_json($serializer->deserialize($data_param, Article::class, 'json'));
        else if($json != null)
            $article->diff_json($serializer->deserialize($json, Article::class, 'json'));
        else
            return new Response('', Response::HTTP_BAD_REQUEST);

        $em = $this->getDoctrine()->getManager();
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
