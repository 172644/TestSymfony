<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Article;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\Serializer\SerializerInterface;

class DefaultController extends Controller
{
    private $_links_discover_article = array(
                                                'GET' => 'article_show',
                                                'MODIFY' => 'article_modify',
                                                'DELETE' => 'article_delete',
                                                'CREATE' => 'article_create'
                                            );

    /**
     * @Route("/", name="homepage")
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
     * @Method({"GET"})
     */
    public function showAction(Article $article, Request $request, SerializerInterface $serializer)
    {
        $article->setLink($article->discover($request->getHttpHost(), $this->_links_discover_article, $this->get('router')));

        $response = new Response($serializer->serialize($article,'json'));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/articles/{id}", name="article_delete", requirements = {"id"="\d+"})
     * @Method({"DELETE"})
     */
    public function deleteAction(Article $article, SerializerInterface $serializer)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();

        return new Response('', Response::HTTP_OK);
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
        else if (!empty($data_param)){
            if(json_decode($data_param) != null)
                $article = $serializer->deserialize($data_param, Article::class, 'json');
            else
                return new Response($serializer->serialize(array('error'=>'Bad Json'),'json'), Response::HTTP_BAD_REQUEST);
        }
        else if($json != null){
            if(json_decode($json) != null)
                $article = $serializer->deserialize($json, Article::class, 'json');
            else
                return new Response($serializer->serialize(array('error'=>'Bad Json'),'json'), Response::HTTP_BAD_REQUEST);
        }
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

            $article->setLink($article->discover($request->getHttpHost(), $this->_links_discover_article, $this->get('router')));

            $response = new Response($serializer->serialize($article,'json'), Response::HTTP_CREATED);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
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
        else if (!empty($data_param)) {
            if(json_decode($data_param) != null)
                $article->diff_json($serializer->deserialize($data_param, Article::class, 'json'));
            else
                return new Response($serializer->serialize(array('error'=>'Bad Json'),'json'), Response::HTTP_BAD_REQUEST);
        }
        else if($json != null) {
            if(json_decode($json) != null)
                $article->diff_json($serializer->deserialize($json, Article::class, 'json'));
            else
                return new Response($serializer->serialize(array('error'=>'Bad Json'),'json'), Response::HTTP_BAD_REQUEST);
        }
        else
            return new Response('', Response::HTTP_BAD_REQUEST);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $article->setLink($article->discover($request->getHttpHost(), $this->_links_discover_article, $this->get('router')));

        $response = new Response($serializer->serialize($article,'json'), Response::HTTP_CREATED);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/articles", name="article_list")
     * @Method({"GET"})
     */
    public function listAction(SerializerInterface $serializer, Request $request)
    {
        $articles = $this->getDoctrine()->getRepository('AppBundle:Article')->findAll();

        foreach ($articles as $_article) {
            $_article->setLink($_article->discover($request->getHttpHost(), $this->_links_discover_article, $this->get('router')));
        }

        $response = new Response($serializer->serialize($articles, 'json'));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
