<?php

namespace App\Command;

use App\Entity\Book;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:parse-books',
    description: 'Parses books info from a JSON file',
)]
class ParseBooksCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file_path', InputArgument::REQUIRED, 'path to the json file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file_path = $input->getArgument('file_path');

        $books_added = 0;
        $categories_added = 0;
        $jsonData = json_decode(file_get_contents($file_path), true);
        $em = $this->entityManager;

        foreach ($jsonData as $bookData) {
            $book = $em->getRepository(Book::class)->findOneBy([
                'title' => $bookData['title'],
                'authors' => [implode(',', $bookData['authors'])]
            ]);

            if (!$book) {
                $book = new Book();
                $book->setTitle($bookData['title']);
                $book->setIsbn($bookData['isbn'] ?? null);

                $date = $bookData['publishedDate']['$date'] ?? null;
                $book->setPublishedDate($date ? new \DateTime($date) : null);

                $book->setThumbnailUrl($bookData['thumbnailUrl'] ?? null);
                $book->setAuthors($bookData['authors']);
                $book->setStatus($bookData['status']);
                $book->setShortDescription($bookData['shortDescription'] ?? null);
                $book->setLongDescription($bookData['longDescription'] ?? null);
                $book->setPageCount($bookData['pageCount'] > 0 ? $bookData['pageCount'] : null);

                $this->entityManager->persist($book);
                $books_added++;
            }

            if (empty($bookData['categories'])) {
                $bookData['categories'] = ['New'];
            }

            foreach ($bookData['categories'] as $categoryName) {
                $category = $em->getRepository(Category::class)->findOneByName($categoryName);

                if (!$category) {
                    $category = new Category();
                    $category->setName($categoryName);

                    $this->entityManager->persist($category);
                    $categories_added++;
                }

                $category->addBook($book);
            }

            $this->entityManager->flush();

        }
        $io->success("Command successful\nBooks added: $books_added\nCategories added: $categories_added");
        return Command::SUCCESS;
    }
}
