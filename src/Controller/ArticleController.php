<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/article')]
final class ArticleController extends AbstractController
{
    #[Route('/article_index', name: 'app_article_index')]
    public function index(): Response
    {
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    #[Route('/article_show', name: 'app_article_show')]
    public function show(): Response 
    {
        return $this->render('article/show.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    #[Route('/article_new', name:'app_article_new')]
    public function new(): Response
    {
        return $this->render('article/new.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }
}
