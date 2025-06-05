<?php

namespace App\Controller\Api;

use App\Entity\Article;
use App\Entity\ArticleLike;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ArticleLikeRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/api')]
class ArticleController extends AbstractController
{
    #[Route('/articles', name: 'api_articles_list', methods: ['GET'])]
    public function list(Request $request, ArticleRepository $articleRepository): JsonResponse
    {
        $draw = $request->query->getInt('draw', 1);
        $start = $request->query->getInt('start', 0);
        $length = $request->query->getInt('length', 10);
        $search = $request->query->all('search')['value'] ?? null;
        $order = $request->query->all('order')[0] ?? ['column' => 0, 'dir' => 'desc'];

        $columnMapping = [
            0 => 'a.id',
            1 => 'a.title',
            2 => 'c.title',
            5 => 'a.createdAt'
        ];

        $orderColumn = $columnMapping[$order['column']] ?? 'a.id';
        $orderDir = $order['dir'] ?? 'desc';

        $results = $articleRepository->findForDataTable($start, $length, $search, $orderColumn, $orderDir);

        $data = [];
        foreach ($results['data'] as $article) {
            $categoryNames = $article->getCategories()->map(fn($category) => $category->getName())->toArray();

            $data[] = [
                'id' => $article->getId(),
                'title' => htmlspecialchars($article->getTitle(), ENT_QUOTES),
                'categories' => $categoryNames ? implode(', ', $categoryNames) : 'Aucune catégorie',
                'commentsCount' => $article->getComments()->count(),
                'likesCount' => $article->getLikes()->count(),
                'createdAt' => $article->getCreatedAt()->format('d/m/Y H:i'),
                'actions' => $this->renderView('article/_actions.html.twig', [
                    'article' => $article
                ])
            ];
        }

        return $this->json([
            'draw' => $draw,
            'recordsTotal' => $results['totalCount'],
            'recordsFiltered' => $results['filteredCount'],
            'data' => $data
        ]);
    }

    #[Route('/articles/search', name: 'api_articles_search', methods: ['GET'])]
    public function search(Request $request, ArticleRepository $articleRepository): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (strlen($query) < 2) {
            return new JsonResponse(['results' => []]);
        }

        $articles = $articleRepository->searchByTitle($query, 10);

        $results = [];
        foreach ($articles as $article) {
            $categoryNames = array_map(fn($category) => $category->getTitle(), $article->getCategories()->toArray());

            $results[] = [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'categories' => $categoryNames
            ];
        }

        return new JsonResponse(['results' => $results]);
    }

    #[Route('/article/{id}/comment', name: 'api_article_comment', methods: ['POST'])]
    public function addComment(Article $article, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $comment = new Comment();
        $comment->setArticle($article);
        $comment->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'commentHtml' => $this->renderView('comment/_comment.html.twig', [
                    'comment' => $comment
                ]),
                'commentsCount' => $article->getComments()->count()
            ]);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return new JsonResponse([
            'success' => false,
            'error' => $errors[0] ?? 'Formulaire invalide'
        ], Response::HTTP_BAD_REQUEST);
    }

    

    #[Route('/article/{id}/like', name: 'app_article_like', methods: ['POST'])]
public function likeArticle(Request $request, Article $article, EntityManagerInterface $em): JsonResponse
{
    $userIp = $request->getClientIp();
    $likeRepository = $em->getRepository(ArticleLike::class);
    
    // Recherche par ipAddress au lieu de ip
    $existingLike = $likeRepository->findOneBy([
        'article' => $article,
        'ipAddress' => $userIp
    ]);

    if ($existingLike) {
        $em->remove($existingLike);
        $action = 'unliked';
    } else {
        $like = new ArticleLike();
        $like->setArticle($article)
             ->setIpAddress($userIp) // Utilisez setIpAddress()
             ->setCreatedAt(new \DateTimeImmutable()); // Initialisez la date
        $em->persist($like);
        $action = 'liked';
    }

    $em->flush();

    return $this->json([
        'success' => true,
        'action' => $action,
        'likesCount' => $article->getLikes()->count()
    ]);
}
}
