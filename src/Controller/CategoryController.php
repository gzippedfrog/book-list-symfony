<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/category/{id}', name: 'category')]
    public function show(Request $request, Category $category, BookRepository $bookRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $per_page = $bookRepository::PAGINATOR_PER_PAGE;
        $offset = ($page - 1) * $per_page;
        $paginator = $bookRepository->getPaginator($offset);
        $pages_total = ceil(count($paginator) / $per_page);

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'books' => $paginator,
            'previous' => $page - 1,
            'next' => min($pages_total, $page + 1),
        ]);
    }
}
