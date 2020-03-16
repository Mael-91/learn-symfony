<?php

namespace App\Controller\Dashboard\Blog;

use App\Entity\Blog;
use App\Form\BlogType;
use App\Repository\BlogCategoryRepository;
use App\Repository\BlogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends AbstractController {

    /**
     * @var BlogRepository
     */
    private $blogRepository;
    /**
     * @var BlogCategoryRepository
     */
    private $categoryRepository;

    public function __construct(BlogRepository $blogRepository, BlogCategoryRepository $categoryRepository) {
        $this->blogRepository = $blogRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @return Response
     */
    public function index(): Response {
        $numPost = $this->blogRepository->countPost();
        $numCategory = $this->categoryRepository->countCategory();
        return $this->render('pages/dashboard/blog/dashboard_blog.html.twig', [
            'current_menu' => 'dashboard-blog',
            'is_dashboard' => 'true',
            'numbersPost' => $numPost,
            'numbersCategory' => $numCategory
        ]);
    }

    /**
     * @return Response
     */
    public function managePost(): Response {
        $posts = $this->blogRepository->findAll();
        return $this->render('pages/dashboard/blog/posts.html.twig', [
            'current_menu' => 'blog-posts-manage',
            'is_dashboard' => 'true',
            'posts' => $posts
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response {
        $blog = new Blog();
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($blog);
            $this->manager->flush();
            $this->addFlash('success-blog', 'La publication est un succès !');
            return $this->redirectToRoute('admin.blog.manage.post');
        }
        return $this->render('pages/dashboard/blog/crud_posts/create.html.twig', [
            'current_menu' => 'blog-posts-manage',
            'is_dashboard' => 'true',
            'blog' => $blog,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Blog $blog
     * @param Request $request
     * @return Response
     */
    public function edit(Blog $blog, Request $request): Response {
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();
            $this->addFlash('success-blog', 'La modification est un succès !');
            return $this->redirectToRoute('admin.blog.manage.post');
        }
        return $this->render('pages/dashboard/blog/crud_posts/edit.html.twig', [
            'current_menu' => 'blog-posts-manage',
            'is_dashboard' => 'true',
            'blog' => $blog,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Blog $blog
     * @param Request $request
     * @return Response
     */
    public function delete(Blog $blog, Request $request): Response {
        if ($this->isCsrfTokenValid('delete' . $blog->getId(), $request->get('_token'))) {
            $this->manager->remove($blog);
            $this->manager->flush();
            $this->addFlash('success-blog', 'La suppression est un succès !');
        }
        return $this->redirectToRoute('admin.blog.manage.post');
    }
}