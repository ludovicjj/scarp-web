<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Filesystem\Filesystem;

class ExcelService
{
    public function __construct(private string $excelPathDir)
    {
    }

    public function createXlsx(array $collections): void
    {
        // Créer un nouveau classeur
        $spreadsheet = new Spreadsheet();
        // Sélectionner la première feuille
        $sheet = $spreadsheet->getActiveSheet();

        // Titre des cols
        $sheet->setCellValue('A1', 'Concurrent');
        $sheet->setCellValue('B1', 'Categorie');
        $sheet->setCellValue('C1', 'Lib_Disp_Form');
        $sheet->setCellValue('D1', 'Modalité');
        $sheet->setCellValue('E1', 'Modalité 2');
        $sheet->setCellValue('F1', 'Modalité 3');
        $sheet->setCellValue('G1', 'Best');
        $sheet->setCellValue('H1', 'TOP Vente');
        $sheet->setCellValue('I1', 'Certifiant');
        $sheet->setCellValue('J1', 'CPF');
        $sheet->setCellValue('K1', 'Diplomant');
        $sheet->setCellValue('L1', 'Nouveaute');
        $sheet->setCellValue('M1', 'OPCA');
        $sheet->setCellValue('N1', 'Reference_concurrente');
        $sheet->setCellValue('O1', 'Titre Concurrent');
        $sheet->setCellValue('P1', 'Durée totale en jours');
        $sheet->setCellValue('Q1', 'Durée heure');
        $sheet->setCellValue('R1', 'Prix formation');
        $sheet->setCellValue('S1', 'Sessions Paris (présentiel)');
        $sheet->setCellValue('T1', 'Sessions à distance');
        $sheet->setCellValue('U1', 'Sessions Lyon (présentiel)');
        $sheet->setCellValue('V1', 'Sessions Nantes (présentiel)');
        $sheet->setCellValue('W1', 'Sessions Toulouse (présentiel)');
        $sheet->setCellValue('X1', 'Sessions Lille (présentiel)');
        $sheet->setCellValue('Y1', 'Sessions Bordeaux (présentiel)');
        $sheet->setCellValue('Z1', 'Sessions Marseille (présentiel)');
        $sheet->setCellValue('AA1', 'Sessions autres régions (présentiel)');
        $sheet->setCellValue('AB1', 'Commentaire');
        $sheet->setCellValue('AC1', 'Lien');
        $sheet->setCellValue('AD1', 'PDF');

        $columns = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
            'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',
            'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD'
        ];
        //->setWidth(20);
        foreach ($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // commence a ecrire a la row 2
        $row = 2;

        foreach ($collections as $value) {

            if ($value['title_concurrent'] !== "Trouvez votre formation en Classe à Distance parmi nos 2 400 formations") {
                $sheet->setCellValue('A' . $row, 'm2iformation');
                $sheet->setCellValue('B' . $row, $value['categorie']);
                $sheet->setCellValue('C' . $row, $value['lib_disp_form']);
                $sheet->setCellValue('D' . $row, $value['modalites'][0] ?? null);
                $sheet->setCellValue('E' . $row, $value['modalites'][1] ?? null);
                $sheet->setCellValue('F' . $row, $value['modalites'][2] ?? null);
                $sheet->setCellValue('G' . $row, $value['best']);
                $sheet->setCellValue('H' . $row, $value['top_vente']);
                $sheet->setCellValue('I' . $row, $value['certifiant']);
                $sheet->setCellValue('J' . $row, $value['cpf']);
                $sheet->setCellValue('K' . $row, '');
                $sheet->setCellValue('L' . $row, $value['nouveaute']);
                $sheet->setCellValue('M' . $row, $value['opca']);
                $sheet->setCellValue('N' . $row, $value['reference_concurent']);
                $sheet->setCellValue('O' . $row, $value['title_concurrent']);
                $sheet->setCellValue('P' . $row, $value['duree_totale_en_jours']);
                $sheet->setCellValue('Q' . $row, $value['duree_heure']);
                $sheet->setCellValue('R' . $row, $value['prix_formation']);
                $sheet->setCellValue('S' . $row, $value['sessions_paris_presentiel']);
                $sheet->setCellValue('T' . $row, $value['sessions_a_distance']);
                $sheet->setCellValue('U' . $row, $value['sessions_lyon_presentiel']);
                $sheet->setCellValue('V' . $row, $value['sessions_nantes_presentiel']);
                $sheet->setCellValue('W' . $row, $value['sessions_toulouse_presentiel']);
                $sheet->setCellValue('X' . $row, $value['sessions_lille_presentiel']);
                $sheet->setCellValue('Y' . $row, $value['sessions_bordeaux_presentiel']);
                $sheet->setCellValue('Z' . $row, $value['sessions_marseille_presentiel']);
                $sheet->setCellValue('AA' . $row, $value['sessions_autres_regions_presentiel']);
                $sheet->setCellValue('AB' . $row, $value['commentaire']);
                $sheet->setCellValue('AC' . $row, $value['lien']);
                $sheet->setCellValue('AD' . $row, $value['pdf']);
                $row++;
            }
        }

        // Créer un nouveau writer pour générer le fichier Excel
        $writer = new Xlsx($spreadsheet);

        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($this->excelPathDir)) {
            $fileSystem->mkdir($this->excelPathDir);
        }

        $filename = $this->excelPathDir . '/data.xlsx';
        $writer->save($filename);
    }
}
