<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use App\Service\MercureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Chat;

#[Route('/chat')]
class ChatController extends AbstractController
{
    public function __construct(
        private MercureService $mercureService,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/{receiverId}', name: 'chat_index', methods: ['GET', 'POST'])]
    public function index(
        int $receiverId,
        MessageRepository $messageRepository,
        Request $request
    ): Response {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser instanceof UserInterface) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $receiver = $this->entityManager->getRepository(User::class)->find($receiverId);
        if (!$receiver) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $users = $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.id != :currentUserId')
            ->setParameter('currentUserId', $currentUser->getId())
            ->getQuery()
            ->getResult();

        $messages = $messageRepository->findConversation($currentUser->getId(), $receiverId);

        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setSender($currentUser);
            $message->setReceiver($receiver);
            $message->setCreatedAt(new \DateTime());
            
            $this->entityManager->persist($message);
            $this->entityManager->flush();

            $this->mercureService->publishChatMessage(
                $this->getConversationId($currentUser->getId(), $receiverId),
                [
                    'id' => $message->getId(),
                    'content' => $message->getContent(),
                    'senderId' => $currentUser->getId(),
                    'receiverId' => $receiverId,
                    'createdAt' => $message->getCreatedAt()->format('c'),
                    'type' => 'new_message'
                ]
            );

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['status' => 'success']);
            }

            return $this->redirectToRoute('chat_index', ['receiverId' => $receiverId]);
        }

        return $this->render('chat/index.html.twig', [
            'messages' => $messages,
            'receiver' => $receiver,
            'users' => $users,
            'form' => $form->createView(),
            'unreadCounts' => [],
            'mercureTopics' => [
                'chat' => "/chat/{$this->getConversationId($currentUser->getId(), $receiverId)}"
            ]
        ]);
    }

    #[Route('/api/send', name: 'chat_api_send', methods: ['POST'])]
    public function apiSend(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $currentUser = $this->getUser();

        $message = new Message();
        $message->setContent($data['content']);
        $message->setSender($currentUser);
        $message->setReceiver($this->entityManager->getRepository(User::class)->find($data['receiverId']));
        $message->setCreatedAt(new \DateTime());

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $this->mercureService->publishChatMessage(
            $this->getConversationId($currentUser->getId(), $data['receiverId']),
            [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'senderId' => $currentUser->getId(),
                'receiverId' => $data['receiverId'],
                'createdAt' => $message->getCreatedAt()->format('c'),
                'type' => 'new_message'
            ]
        );

        return new JsonResponse(['status' => 'success']);
    }

    #[Route('/mercure/token', name: 'chat_mercure_token', methods: ['GET'])]
    public function getMercureToken(): JsonResponse
    {
        $currentUser = $this->getUser();
        return new JsonResponse([
            'token' => $this->mercureService->generateToken([
                "/user/{$currentUser->getId()}",
                "/chat/{$currentUser->getId()}/*"
            ])
        ]);
    }


private function getConversationId(int $user1Id, int $user2Id): int
{
    $chatRepo = $this->entityManager->getRepository(Chat::class);

    $chat = $chatRepo->findOneByUsers($user1Id, $user2Id);

    if (!$chat) {
        $chat = new Chat();
        $chat->addUser($this->entityManager->getReference(User::class, $user1Id));
        $chat->addUser($this->entityManager->getReference(User::class, $user2Id));
        $this->entityManager->persist($chat);
        $this->entityManager->flush();
    }

    return $chat->getId();
}

}