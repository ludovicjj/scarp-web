<?php

namespace App\Command;

use App\Repository\UrlRepository;
use App\Scraper\Scraper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Panther\Client;
use stdClass;

#[AsCommand(name: 'app:fetch-data')]
class FetchDataCommand extends Command
{
    public function __construct(
        private UrlRepository $urlRepository,
        private Scraper $scraper,
        private Filesystem $filesystem,
        private string $path,
        string $name = null
    )
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $urls = $this->urlRepository->findAll();

        $io->progressStart(count($urls));
        $index = $this->initOrGetLastIndex();

        //$this->send("https://www.m2iformation.fr/formation-acculturation-numerique-et-transformation-digitale-de-l-entreprise/ACD-TRANS/", 0);
        for ($i = $index; $i < count($urls); $i++) {
            try {
                $this->send($urls[$i], $i);
                $io->progressAdvance();
                $this->filesystem->dumpFile($this->path.'/index.txt', $i);
                sleep(1);
            } catch (\Exception) {
                $io->error('Failed step ' . $i . ' - ' . $urls[$i]);
                return Command::FAILURE;
            }
        }

        $io->progressFinish();
        $io->success('Fetch Data completed');
        return Command::SUCCESS;
    }

    private function send(string $url, int $index): void
    {
        $client = Client::createFirefoxClient();
        $crawler = $client->request('GET', $url);
        $data = $this->initData($crawler, $url, $client);


        $result = $this->createCollection($data);

        $json = file_get_contents($this->path.'/result.json');
        $data = json_decode($json, true);

        $data['result'][$index] = $result;
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->filesystem->dumpFile($this->path. '/result.json', $json);
    }

    private function initData(Crawler $crawler, string $url, Client $client): stdClass
    {
        $data = new stdClass();
        $data->title = $this->scraper->scrapTitle($crawler);

        /** @var array fullDate */
        $data->fullDate = $this->scraper->scrapFullDate($crawler);
        $data->libDispForm = $this->scraper->scrapLibDispForm($crawler);
        $data->modes = $this->scraper->scrapModes($crawler);
        $data->paris = $this->scraper->scrapParisCount($crawler);
        $data->lyon = $this->scraper->scrapLyonCount($crawler);
        $data->nantes = $this->scraper->scrapNantesCount($crawler);
        $data->toulouse = $this->scraper->scrapToulouseCount($crawler);
        $data->lille = $this->scraper->scrapLilleCount($crawler);
        $data->bordeaux = $this->scraper->scrapBordeauxCount($crawler);
        $data->marseille = $this->scraper->scrapMarseilleCount($crawler);
        $data->other = $this->scraper->scrapOtherCount($crawler);
        $data->online = $this->scraper->scrapOnlineCount($crawler);
        $data->comment = $this->scraper->scrapComment($crawler);
        $data->url = $url;
        $data->pdf = $this->scraper->scrapPDF($crawler);
        $data->cpf = $this->scraper->scrapCPF($crawler);
        $data->category = $this->scraper->scrapCategory($crawler);
        $data->ref = $this->scraper->scrapRef($url);
        $data->certify = $this->scraper->scrapCertifying($crawler);
        $data->opca = $this->scraper->scrapOPCA($crawler);

        /** @var array badges */
        $data->badges = $this->scraper->scrapBadges($crawler, $url, $client);

        return $data;
    }

    private function isValidUrl(stdClass $data): bool
    {
        if (
            empty($data->fullDate) &&
            is_null($data->price) &&
            $data->paris === 0 &&
            $data->lyon === 0 &&
            $data->toulouse === 0 &&
            $data->lille === 0 &&
            $data->bordeaux === 0 &&
            $data->marseille === 0 &&
            $data->other === 0 &&
            $data->online === 0
        ) {
            return false;
        }

        return true;
    }

    private function initOrGetLastIndex(): int
    {
        if (!$this->filesystem->exists($this->path . '/index.txt')) {
            $this->filesystem->dumpFile($this->path . '/index.txt', "0");

            return 0;
        }

        return (int)file_get_contents($this->path . '/index.txt');
    }

    private function createCollection(stdClass $data): array
    {
        $collections = [
            'categorie'                             => $data->category,
            'modalites'                             => $data->modes,
            'lib_disp_form'                         => $data->libDispForm,
            'best'                                  => $data->badges['best'],
            'top_vente'                             => $data->badges['top'],
            'certifiant'                            => $data->certify,
            'cpf'                                   => $data->cpf,
            'nouveaute'                             => $data->badges['new'],
            'opca'                                  => $data->opca,
            'reference_concurent'                   => $data->ref,
            'title_concurrent'                      => $data->title,
            'duree_totale_en_jours'                 => $data->fullDate['day'] ?? '',
            'duree_heure'                           => $data->fullDate['hour'] ?? '',
            'prix_formation'                        => $data->badges['price'],
            'sessions_paris_presentiel'             => $data->paris,
            'sessions_a_distance'                   => $data->online,
            'sessions_lyon_presentiel'              => $data->lyon,
            'sessions_nantes_presentiel'            => $data->nantes,
            'sessions_toulouse_presentiel'          => $data->toulouse,
            'sessions_lille_presentiel'             => $data->lille,
            'sessions_bordeaux_presentiel'          => $data->bordeaux,
            'sessions_marseille_presentiel'         => $data->marseille,
            'sessions_autres_regions_presentiel'    => $data->other,
            'commentaire'                           => $data->comment,
            'lien'                                  => $data->url,
            'pdf'                                   => $data->pdf,
        ];

        return $collections;
    }
}