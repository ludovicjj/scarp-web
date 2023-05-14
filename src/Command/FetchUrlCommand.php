<?php

namespace App\Command;

use App\Repository\UrlRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\Client;

#[AsCommand(name: 'app:fetch-url')]
class FetchUrlCommand extends Command
{
    const PATTERN_FORMATION_URL = 'https://www.m2iformation.fr/formation-';

    public function __construct(
        private string $url,
        private UrlRepository $urlRepository,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $data = $this->getFilteredUrlFromSiteMap();
        $this->urlRepository->persist($data);

        $io->success('Fetch URL completed');
        return Command::SUCCESS;
    }

    private function getFilteredUrlFromSiteMap(): array
    {
        $client = Client::createChromeClient();
        $crawler = $client->request('GET', $this->url);

        $xml = $crawler->getText();
        $xmlCrawler = new Crawler($xml);

        // All urls
        $urls = $xmlCrawler->filterXPath('//url/loc')->each(function (Crawler $node) {
            return $node->text();
        });

        // Filter urls
        $filteredUrls = array_filter($urls, function ($url) {
            return str_starts_with($url, self::PATTERN_FORMATION_URL);
        });

        // Reorder index
        return [
            'url' => array_values($filteredUrls)
        ];
    }
}