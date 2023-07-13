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
    description: 'Parses books info from a JSON',
)]
class ParseBooksCommand extends Command
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('source', InputArgument::REQUIRED, 'path to the json file')
            ->addOption('url', null, InputOption::VALUE_NONE, 'use url instead of file path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $source = $input->getArgument('source');

        if ($source) {
            $jsonData = json_decode(file_get_contents($source), true);
            $em = $this->entityManager;

            foreach ($jsonData as $bookData) {
                $book = $em->getRepository(Book::class)->findOneBy(['title' => $bookData['title']]);

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
                }

                foreach ($bookData['categories'] as $categoryName) {
                    $category = $em->getRepository(Category::class)->findOneBy(['name' => $categoryName]);

                    if (!$category) {
                        $category = new Category();
                        $category->setName($categoryName);

                        $this->entityManager->persist($category);

                    }

                    if (!$category->getBooks()->contains($book)) {
                        $category->addBook($book);
                    }
                }

                $this->entityManager->flush();

            }
            return Command::SUCCESS;

        }
        return Command::FAILURE;
    }
}
