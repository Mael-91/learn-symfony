<?php

namespace App\Controller\Core\Comment;

use App\Entity\Blog;
use App\Entity\BlogComment;
use App\Event\UserActivityEvent;
use App\Exceptions\UserNotConnectedException;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @todo Faire les réponses en Json
 */

class BlogCommentController extends AbstractController {

    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(EntityManagerInterface $manager, EventDispatcherInterface $dispatcher) {
        $this->manager = $manager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Permet de commenter un article
     * @param Request $request
     * @param Blog $post
     * @return JsonResponse
     * @throws \Exception
     */
    public function comment(Request $request, Blog $post): JsonResponse {
        $content = $request->request->get('comment_zone');
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to comment');
            throw new UserNotConnectedException();
        }
        if (empty($content)) {
            $this->addFlash('error', 'The content must not be null');
            return new JsonResponse(['code' => 400, 'message' => 'The content must not be null'], JsonResponse::HTTP_BAD_REQUEST);
        }
        if (!$this->isCsrfTokenValid('comment' . $post->getId(), $request->request->get('_csrf_token_comment'))) {
            throw new InvalidCsrfTokenException();
        }
        $comment = new BlogComment();
        $comment->setPost($post)
            ->setAuthor($user)
            ->setContent($content);
        $this->manager->persist($comment);
        $this->manager->flush();
        $this->dispatcher->dispatch(new UserActivityEvent($user, null, $comment, null));
        $this->addFlash('success', 'Well done ! Your comment has been sent.');
        return new JsonResponse(['code' => 200, 'message' => 'commentaire envoyé avec succès'], JsonResponse::HTTP_CREATED);
    }

    /**
     * Permet de répondre à un commentaire
     * @param Request $request
     * @param Blog $post
     * @param BlogComment $comment
     * @return JsonResponse
     * @ParamConverter("comment", class="App\Entity\BlogComment")
     */
    public function reply(Request $request, Blog $post, BlogComment $comment): JsonResponse {
        $content = $request->request->get('reply_zone');
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to comment');
            throw $this->createAccessDeniedException();
        }
        if (empty($content)) {
            $this->addFlash('error', 'The content must not be null');
            return new JsonResponse(['code' => 400, 'message' => 'The content must not be null'], JsonResponse::HTTP_BAD_REQUEST);
        }
        if (!$this->isCsrfTokenValid('reply_comment' . $comment->getId(), $request->request->get('_csrf_token_reply'))) {
            throw new InvalidCsrfTokenException();
        }
        $reply = new BlogComment();
        $reply->setAuthor($user)
            ->setPost($post)
            ->setContent($content)
            ->setParent($comment);
        $this->manager->persist($reply);
        $this->manager->flush();
        $this->dispatcher->dispatch(new UserActivityEvent($user, null, $reply, null));
        $this->addFlash('success', 'Well done, you reply has been sent.');
        return new JsonResponse(['code' => 200, 'message' => 'réponse envoyée avec succès'], JsonResponse::HTTP_CREATED);
    }
}