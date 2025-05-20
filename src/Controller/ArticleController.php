<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleForm;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Comment;
use App\Form\CommentForm;

#[Route('/article')]
final class ArticleController extends AbstractController
{
    #[Route(name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $article = new Article();
    
    $article->setCreatedAt(new \DateTimeImmutable());
    $article->setUpdatedAt(null);

    $form = $this->createForm(ArticleForm::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($article);
        $entityManager->flush();

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('article/new.html.twig', [
        'article' => $article,
        'form' => $form,
    ]);
}


    #[Route('/{id}', name: 'app_article_show', methods: ['GET', 'POST'])]
public function show(Request $request, Article $article, EntityManagerInterface $em): Response
{
    $comment = new Comment();
    $comment->setArticle($article);
    $comment->setCreatedAt(new \DateTimeImmutable());

    $form = $this->createForm(CommentForm::class, $comment);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($comment);
        $em->flush();

        return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
    }

    return $this->render('article/show.html.twig', [
        'article' => $article,
        'commentForm' => $form->createView(),
    ]);
}
    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleForm::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/article/{id}', name: 'app_article_delete', methods: ['POST'])]
public function delete(Request $request, Article $article, EntityManagerInterface $em): Response
{
    if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
        $em->remove($article);
        $em->flush();
    }

    return $this->redirectToRoute('app_article_index');
}

}