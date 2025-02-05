<?php

namespace App\Scraper;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\Client;

class Scraper
{
    const REGIONS = [
        'Paris La Défense',
        'Paris',
        'Lyon Part-Dieu',
        'Nantes',
        'Toulouse',
        'Lille',
        'Bordeaux',
        'Marseille'
    ];

    const BADGE_NEW = 'Nouveau';
    const BADGE_TOP = 'Top ventes';

    public function scrapTitle(Crawler $crawler): ?string
    {
        $spanNode = $crawler->filterXPath('//header[@id="header"]//h1/span');
        $fullTitleNode = $crawler->filterXPath('//header[@id="header"]//h1');

        if (!$fullTitleNode->count()) {
            return null;
        }

        if ($spanNode->count()) {
            return str_replace($spanNode->text() . "\n", '', $fullTitleNode->text());
        }

        return $fullTitleNode->text();
    }

    public function scrapFullDate(Crawler $crawler): array
    {
        $date = [];
        $node = $crawler->filter('#header > div.center.large.signika > p.infos.signika > span:nth-child(1)');

        if (!$node->count()) {
            return $date;
        }

        $totalDate = $node->text();

        if (preg_match('/(\d{1,3}[hH]\d{0,2})/', $totalDate, $matches)) {
            $date['hour'] = $matches[1];
        }

        if (preg_match("/([\d,]+(?:\.\d+)? jours?)/", $totalDate, $matches)) {
            $date['day'] = $matches[1];
        }

        return $date;
    }

    public function scrapPrice(Crawler $crawler): ?string
    {
        $presentielButton = $crawler->filter('#calendar-modal-presentiel');
        $distantielButton = $crawler->filter('#calendar-modal-distance');
        $priceElement = $crawler->filter('span.col.prix.signika');

        if ($priceElement->count() === 0 ) {
            return null;
        }

        if (!$presentielButton->count() && !$distantielButton->count()) {
           return null;
        }

        if ($presentielButton->count()) {
            $presentielButton->first()->click();
            $price = $priceElement->first()->text();

            if ($price === '' && $distantielButton->count()) {
                $crawler->filter('#calendar-content > a')->click();
                $distantielButton->first()->click();
                $priceElement = $crawler->filter('span.col.prix.signika');

                return $priceElement->first()->text();
            }
        }

        return $priceElement->first()->text();
    }

    public function scrapParisCount(Crawler $crawler): int
    {
        return $crawler
            ->filterXPath('//div[@id="calendar"]//strong[contains(text(), "Paris La Défense") or contains(text(), "Paris")]')
            ->count();
    }

    public function scrapLyonCount(Crawler $crawler): int
    {
        return $crawler
            ->filterXPath('//div[@id="calendar"]//strong[contains(text(), "Lyon Part-Dieu")]')
            ->count();
    }

    public function scrapNantesCount(Crawler $crawler): int
    {
        return $crawler
            ->filterXPath('//div[@id="calendar"]//strong[contains(text(), "Nantes")]')
            ->count();
    }

    public function scrapToulouseCount(Crawler $crawler): int
    {
        return $crawler
            ->filterXPath('//div[@id="calendar"]//strong[contains(text(), "Toulouse")]')
            ->count();
    }

    public function scrapLilleCount(Crawler $crawler): int
    {
        return $crawler
            ->filterXPath('//div[@id="calendar"]//strong[contains(text(), "Lille")]')
            ->count();
    }

    public function scrapBordeauxCount(Crawler $crawler): int
    {
        return $crawler
            ->filterXPath('//div[@id="calendar"]//strong[contains(text(), "Bordeaux")]')
            ->count();
    }

    public function scrapMarseilleCount(Crawler $crawler): int
    {
        return $crawler
            ->filterXPath('//div[@id="calendar"]//strong[contains(text(), "Marseille")]')
            ->count();
    }

    public function scrapOtherCount(Crawler $crawler): int
    {
        $excludeStrings = array_map(function ($region) {
            return sprintf('contains(text(), "%s")', $region);
        }, self::REGIONS);
        $selector = '//div[@id="calendar"]//strong[contains(@class, "lieu")][not('.implode(' or ', $excludeStrings).') and not(ancestor::div[@id="region_0"])]';
        $elements = $crawler->filterXPath($selector);
        return $elements->count();

    }

    public function scrapOnlineCount(Crawler $crawler): int
    {
        return $crawler->filter('#region_0 span.col.date.signika')->count();
    }

    public function scrapComment(Crawler $crawler): ?string
    {
        $commentNode = $crawler->filterXPath('//span[@class="avis"]/a');

        if ($commentNode->count()) {
            return $commentNode->text();
        }

        return null;
    }

    public function scrapPDF(Crawler $crawler): ?string
    {
        $pdfNode = $crawler->filterXPath('//p[@class="tags formation-options"]/a[@href and @target]');
        if ($pdfNode->count()) {
            return 'https://www.m2iformation.fr/' . $pdfNode->first()->attr('href');
        }

        return null;
    }

    public function scrapCategory(Crawler $crawler): ?string
    {
        $categoryNode = $crawler->filterXPath('//div[@class="ariane"]//a');

        if ($categoryNode->count()) {
            return $categoryNode->last()->text();
        }
        return null;
    }

    public function scrapRef(string $url): ?string
    {
        preg_match('/\/([^\/]+)\/?$/', $url, $matches);

        return $matches[1] ?? null;
    }

    public function scrapCertifying(Crawler $crawler): bool
    {
        //$sectionNode = $crawler->filterXPath('//section[contains(@class, "grey") and contains(@class, "to-sticky")]');
        $calendarNode = $crawler->filterXPath('//*[@id="calendar"]');
        if ($calendarNode->count()) {
            $certifyLink = $calendarNode->filterXPath('.//a[@href="diplomes-et-certifications/"]');
            if ($certifyLink->count()) {
                $filteredText = $certifyLink->filterXPath('./*[not(self::span)]')->each(function (Crawler $node) {
                    return trim($node->text(null, true));  // Extraire le texte sans le span
                });
                return count($filteredText) > 0;
            }
        }

        return false;
    }

    public function scrapCertifyingContent(Crawler $crawler): string
    {
        //$sectionNode = $crawler->filterXPath('//section[contains(@class, "grey") and contains(@class, "to-sticky")]');
        $calendarNode = $crawler->filterXPath('//*[@id="calendar"]');

        if ($calendarNode->count()) {
            $certifyLink = $calendarNode->filterXPath('.//a[@href="diplomes-et-certifications/"]');
            if ($certifyLink->count()) {
                // $certifyingContent =  $certifyLink->text(null, true);

                $filteredText = $certifyLink->filterXPath('./*[not(self::span)]')->each(function (Crawler $node) {
                    return trim($node->text(null, true));  // Extraire le texte
                });
                return implode(' ', $filteredText);
            }
        }

        return '';
    }

    public function scrapCPF(Crawler $crawler): bool
    {
        //$sectionNode = $crawler->filterXPath('//section[contains(@class, "grey") and contains(@class, "to-sticky")]');
        $calendarNode = $crawler->filterXPath('//*[@id="calendar"]');
        if ($calendarNode->count()) {
            $fundingLink = $calendarNode->filterXPath('.//a[@href="financement/"]');
            if ($fundingLink->count()) {
               return str_contains($fundingLink->text(), 'CPF');
            }
        }

        return false;
    }

    public function scrapOPCA(Crawler $crawler): bool
    {
        $calendarNode = $crawler->filterXPath('//*[@id="calendar"]');
        if ($calendarNode->count()) {
            $fundingLink = $calendarNode->filterXPath('.//a[@href="financement/"]');
            if ($fundingLink->count()) {
                return str_contains($fundingLink->text(), 'Actions-CO');
            }
        }

        return false;

//        //$cpfNode = $crawler->filterXPath('//p[@class="infos signika"]/span/a[contains(text(),"AC")]');
//        $spanNode = $crawler->filterXPath('//header[@id="header"]//div[@style]/span');
//
//        if (!$spanNode->count()) {
//            return false;
//        }
//
//        return str_contains($spanNode->text(), 'OPCO');
    }

    public function scrapLibDispForm(Crawler $crawler): string
    {
        $fullTitleNode = $crawler->filterXPath('//header[@id="header"]//h1');

        if ($fullTitleNode->count()) {
            $text = $fullTitleNode->text();
            if (str_contains($text, 'certification')) {
                return "stage certifiant";
            }
        }

        return "stage";
    }

    public function scrapModes(Crawler $crawler): array
    {
        $modes = [];

        $colNodes = $crawler->filterXPath('//div[@id="calendar-select"]//div[@class="blocks signika to-adjust"]//div[@class="col"]');
        if ($colNodes->count()) {
            $modeContainerNode = $colNodes->first();
            $modeNode = $modeContainerNode->filterXPath('//p[@class="adjust"]//strong');
            if ($modeNode->count()) {
                $modeText = $modeNode->text();
                $modeArray = explode(',', $modeText);
                foreach ($modeArray as $mode) {
                    $modes[] = trim($mode);
                }
                return $modes;
            }
        }

        return $modes;
    }

    public function scrapBadges(Crawler $crawler, string $url, Client $client): array
    {
        $badges = [
            'top' => false,
            'new' => false,
            'price' => null,
        ];

        $categoryNode = $crawler->filterXPath('//div[@class="ariane"]//a');

        if ($categoryNode->count()) {
            $link = $categoryNode->last()->link();
            $crawler = $client->click($link);

            $path = substr(parse_url($url, PHP_URL_PATH), 1);
            $linkFormation = $crawler->filterXPath('//a[@href="' . $path . '"]');


            if ($linkFormation->count()) {
                $h3Node = $linkFormation->filterXPath('parent::h3');
                $isBestNode = $linkFormation->filterXPath('ancestor::article[contains(@class, "is_essentiel")]');

                // enable show more
                $seeMoreNode = $crawler->filter('a.show-more-results');
                if ($seeMoreNode->count()) {
                    $client->click($seeMoreNode->link());
                }

                // badge
                if ($h3Node->count()) {
                    $sup = $h3Node->filterXPath('.//sup');
                    if ($sup->count()) {
                        if (str_contains($sup->text(), self::BADGE_TOP)) {
                            $badges['top'] = true;
                        }
                        if (str_contains($sup->text(), self::BADGE_NEW)) {
                            $badges['new'] = true;
                        }

                    }
                }

                // price
                $articleNode = $h3Node->filterXPath('ancestor::article');
                if ($articleNode->count()) {
                    $priceNode = $articleNode->filterXPath('.//p[@class="price"]');
                    if ($priceNode->count()) {
                        $badges['price'] = $priceNode->text();
                    }
                }
            }
        }

        return $badges;
    }
}
