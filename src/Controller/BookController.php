<?php

namespace App\Controller;

use App\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    #[Route('/book/{id}', name: 'book')]
    public function show(Request $request, Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
            'referer' => $request->headers->get('referer')
        ]);
    }
}
