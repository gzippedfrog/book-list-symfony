<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    #[Route('/books', name: 'books')]
    public function index(Request $request, BookRepository $bookRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $per_page = $bookRepository::PAGINATOR_PER_PAGE;
        $offset = ($page - 1) * $per_page;
        $paginator = $bookRepository->getPaginator($offset);
        $pages_total = ceil(count($paginator) / $per_page);

        return $this->render('book/index.html.twig', [
            'books' => $paginator,
            'previous' => $page - 1,
            'next' => min($pages_total, $page + 1),
        ]);
    }

    #[Route('/book/{id}', name: 'book')]
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }
}
