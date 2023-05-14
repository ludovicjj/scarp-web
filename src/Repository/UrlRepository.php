<?php

namespace App\Repository;

use Symfony\Component\Filesystem\Filesystem;

class UrlRepository
{
    public function __construct(private string $urlPath)
    {
    }

    public function persist(array $data): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $fileSystem = new Filesystem();

        $fileSystem->dumpFile($this->urlPath.'/data.json', $json);
    }

    public function findAll(): array
    {
        $json = file_get_contents($this->urlPath.'/data.json');
        $data = json_decode($json, true);

        return $data['url'];
    }

    public function remove(string $url): void
    {
        $json = file_get_contents($this->urlPath.'/data.json');
        $data = json_decode($json, true);
        $key = array_search($url, $data['url']);

        if ($key !== false) {
            unset($data['url'][$key]);
            $data['url'] = array_values($data['url']); // reorder index
        }

        $this->persist($data);
    }
}
