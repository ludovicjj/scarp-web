<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Service\ExcelService;

#[AsCommand(name: 'app:excel-create')]
class ExcelCommand extends Command
{
    public function __construct(
        private ExcelService $excelService,
        private string $path,
        string $name = null
    )
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $json = file_get_contents($this->path . '/result.json');
        $data = json_decode($json, true);

        $this->excelService->createXlsx($data['result']);

        $io->success('Excel generated');
        return Command::SUCCESS;
    }
}