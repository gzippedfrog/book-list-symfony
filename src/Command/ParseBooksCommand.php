<?php

namespace App\Command;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

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

        $arg1 = $input->getArgument('source');

        if ($arg1) {
            $file = file_get_contents($arg1);

            $defaultContext = [
                AbstractNormalizer::CALLBACKS => [
                    'publishedDate' => fn($innerObject): string => $innerObject['$date'],
                ],
            ];

            $serializer = new Serializer(
                [new GetSetMethodNormalizer(), new ArrayDenormalizer()],
                [new JsonEncoder()]
            );

            $books = $serializer->deserialize($file, Book::class . '[]', 'json', $defaultContext);

            foreach ($books as $book) {
                $this->entityManager->persist($book);
            }

            $this->entityManager->flush();

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }
}
