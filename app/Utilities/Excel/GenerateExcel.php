<?php

namespace App\Utilities\Excel;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GenerateExcel implements WithEvents, WithTitle, WithMultipleSheets
{
    private $sheet;
    private $otherSheet;
    private $title;
    private $headers;
    private $data;
    private $extraSetting;
    private $type;

    /**
     * GenerateExcel constructor.
     * @param string|array $title
     * @param array $headers
     * @param array $data
     * @param array $extraSetting
     * @param null $type 0 => main, 1 => main with option, 2 => option
     * @param GenerateExcel|null $otherSheet
     */
    public function __construct($title = 'Default Title', $headers = [], $data = [], $extraSetting = [], $type = null, $otherSheet = null)
    {
        $this->sheet = null;
        $this->otherSheet = $otherSheet;
        $this->title = $title;
        $this->headers = $headers;
        $this->data = $data;
        $this->extraSetting = $extraSetting;
        $this->type = $type;

        if (is_null($this->type)) {
            foreach ($this->headers as $header) {
                foreach ($header as $headerRow) {
                    // if there is options available, means this excel will have multiple sheets to store the options
                    if (array_key_exists('options', $headerRow) && !empty($headerRow['options'])) {
                        $this->type = 1;
                        break;
                    }
                }
            }
        }

    }

    /**
     * @inheritDoc
     */
    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function (BeforeWriting $event) {
                $workbook = $event->getWriter()->getDelegate();
                $workbook->getSecurity()->setLockStructure(true);
                $workbook->getSecurity()->setWorkbookPassword('UNPROTECTaTyOuRoWnRIsK');
            },
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // lock all cell, prevent user edit
                $sheet->getProtection()->setPassword('UNPROTECTaTyOuRoWnRIsK');

                $y = 1;
                $headersRowCount = count($this->headers);

                /* setup header row - START */
                if ($headersRowCount > 0) {
                    foreach ($this->headers as $headersRow) {
                        $x = 'A';
                        foreach ($headersRow as $header) {
                            $coordinate = $header['coordinate'] ?? $x.$y;
                            $style = $header['style'] ?? [];

                            // apply global header style on every headers
                            if (array_key_exists('header_style', $this->extraSetting)) {
                                $style = array_merge($style, $this->extraSetting['header_style']);
                            }

                            $this->setCell($sheet, $coordinate, $header['value'], $style);
                            $x++;
                        }
                        $y++;
                    }
                }
                /* setup header row - END */

                /* setup data row - START */
                // prepare space for user to fill in data
                if (empty($this->data)) {
                    $this->fillEmptyRow($sheet, $y);
                } elseif (!empty($this->data)) {
                    // fill in data by row or column, default is row
                    $fillInBy = 'row';
                    if (array_key_exists('data_flow_column', $this->extraSetting)) {
                        if ($this->extraSetting['data_flow_column']) {
                            $fillInBy = 'column';
                        }
                    }

                    // starting column or row
                    $xStart = 'A';
                    $yStart = $y;

                    $x = $xStart;
                    foreach ($this->data as $rowOrColumn) {
                        if ($fillInBy === 'column') {
                            $y = $yStart;
                            // not support set dropdown for filled cell for column mode yet
                            $counter = null;
                        } else {
                            $x = $xStart;
                            $counter = 0;
                        }

                        foreach ($rowOrColumn as $index => $value) {
                            $style = isset($rowOrColumn['style']) ? array_merge( $rowOrColumn['style'], ['protection' => false]) : ['protection' => false];

                            // apply global body style on body
                            if (array_key_exists('body_style', $this->extraSetting)) {
                                $style = array_merge($style, $this->extraSetting['body_style']);
                            }
                            // setup dropdown even the cell has been filled
                            if ($counter !== null && $headersRowCount > 0) {
                                if (array_key_exists('option_range', $this->headers[$headersRowCount - 1][$counter])) {
                                    $this->setDropdown($sheet, $x, $y, $this->headers[$headersRowCount - 1][$counter]['option_range']);
                                }
                            }

                            // apply global column style on body's column
                            if (array_key_exists('column_style', $this->extraSetting) && (array_key_exists($x, $this->extraSetting['column_style']) || array_key_exists($index, $this->extraSetting['column_style']))) {
                                $style = array_merge($style, $this->extraSetting['column_style'][$index] ?? $this->extraSetting['column_style'][$x]);
                            }

                            // check if current cell has/need option range set
                            $xIndex = $this->alphabetToNumber($x);

                            if ($headersRowCount >= 2 && array_key_exists($xIndex, $this->headers[1]) && array_key_exists('option_range', $this->headers[1][$xIndex])) {
                                $this->setDropdown($sheet, $x, $y, $this->headers[1][$xIndex]['option_range']);
                            }

                            $this->setCell($sheet, $x.$y, $value, $style);

                            if ($counter !== null && $headersRowCount > 0) {
                                if (array_key_exists('lookup_arguments', $this->headers[$headersRowCount - 1][$counter])) {
                                    $this->setLookup($sheet, $x . $y, $this->headers[$headersRowCount - 1][$counter]['lookup_arguments']);
                                }
                            }

                            if ($fillInBy === 'column') {
                                $y++;
                            } else {
                                $x++;
                                $counter++;
                            }
                        }

                        if ($fillInBy === 'column') {
                            $x++;
                        } else {
                            $y++;
                        }
                    }

                    // force fill empty row
                    if (array_key_exists('force_fill_empty_row', $this->extraSetting) && $this->extraSetting['force_fill_empty_row']) {
                        // default row count is 100
                        $maxRowCount = 100;
                        if (array_key_exists('max_row', $this->extraSetting) && is_numeric($this->extraSetting['max_row'])) {
                            $maxRowCount = $this->extraSetting['max_row'];
                        }
                        $this->fillEmptyRow($sheet, $y, $maxRowCount - $headersRowCount, $headersRowCount);
                    }
                }
                /* setup data row - END */

                /* setup default cell style - START */
                $borders = [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    'top'        => ['borderStyle' => Border::BORDER_THICK],
                    'bottom'     => ['borderStyle' => Border::BORDER_THICK],
                    'left'       => ['borderStyle' => Border::BORDER_THICK],
                    'right'      => ['borderStyle' => Border::BORDER_THICK]
                ];

                $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
                    'borders' => $borders
                ]);

                if (array_key_exists('freeze_pane', $this->extraSetting)) {
                    $sheet->freezePane($this->extraSetting['freeze_pane']);
                }
                /* setup default cell style - END */

                // hide sheet if title starts with 'no_read_'
                if (str_starts_with($this->title, 'no_read_')) {
                    $sheet->setSheetState(Worksheet::SHEETSTATE_VERYHIDDEN);
                }

                // enable cell password protection
                $sheet->getProtection()->setSheet(true);
                $this->sheet = $sheet;
            }
        ];
    }

    /**
     * @inheritDoc
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function sheets(): array
    {
        if ($this->type === 1) {
            $sheets = [];
            $options = [];

            $x = 'A';
            $columnIndex = 1;
            foreach ($this->headers as $headerIndex => $header) {
                foreach ($header as $headerRowIndex => $headerRow) {
                    if (array_key_exists('options', $headerRow) && !empty($headerRow['options'])) {
                        $options[] = $headerRow['options'];
                        $this->headers[$headerIndex][$headerRowIndex]['option_range'] = '$'.$x.'$1:'.'$'.$x.'$'.count($headerRow['options']);
                        $x++;

                        if (array_key_exists('lookup_for_column', $headerRow) && !empty($headerRow['lookup_for_column']) && array_key_exists('lookup_for_name', $headerRow) && !empty($headerRow['lookup_for_name'])) {
                            // Retrieve the lookup range from options
                            $headerKey = array_search($headerRow['lookup_for_name'], array_column($this->headers[$headerIndex], 'value'));
                            $fromRange = explode(':', $this->headers[$headerIndex][$headerKey]['option_range'])[0];
                            $endRange = explode(':', $this->headers[$headerIndex][$headerRowIndex]['option_range'])[1];

                            $this->headers[$headerIndex][$headerRowIndex]['lookup_arguments']['lookup_column'] = $headerRow['lookup_for_column'];
                            $this->headers[$headerIndex][$headerRowIndex]['lookup_arguments']['lookup_range'] = $fromRange.':'.$endRange;
                            $this->headers[$headerIndex][$headerRowIndex]['lookup_arguments']['column_index'] = $headerRow['column_index'] ?? $columnIndex;

                            $columnIndex++;
                        }
                    }
                }
            }

            $sheets[] = new GenerateExcel('no_read_options', [], $options, ['data_flow_column' => true]);
            $sheets[] = new GenerateExcel($this->title, $this->headers, $this->data, $this->extraSetting, 0, $sheets[0]);
            $this->type = 0;
            return $sheets;
        }
        return [$this];
    }

    /**
     * useful for others to get access current sheet from outside
     *
     * @return Worksheet|null
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    private function alphabetToNumber($value)
    {
        $value = strtoupper($value);
        $length = strlen($value);

        if(preg_match("/^[A-Z]+$/",$value) === false) {
            return null;
        }

        $it = 0;
        $result = 0;

        for($i = $length - 1; $i >- 1; $i--) {
            //cumulate letter value
            $result += (ord($value[$i]) - 64 ) * pow(26,$it);

            //simple counter
            $it++;
        }
        return $result - 1;
    }

    /**
     * Fill empty row
     *
     * @param $sheet
     * @param $y
     * @param $dataRowCount
     * @param $headersRowCount
     * @return void
     * @throws Exception
     */
    private function fillEmptyRow($sheet, $y, $dataRowCount = null, $headersRowCount = null)
    {
        // headers row count
        if (!is_numeric($headersRowCount)) {
            $headersRowCount = count($this->headers);
        }

        // data row count
        if (!is_numeric($dataRowCount)) {
            // default row count is 100
            $dataRowCount = 100;
            if (array_key_exists('max_row', $this->extraSetting) && is_numeric($this->extraSetting['max_row'])) {
                $dataRowCount = $this->extraSetting['max_row'];
            }
        }

        while ($y <= $dataRowCount + $headersRowCount) {
            $x = 'A';
            for ($counter = 0; $counter < count($this->headers[$headersRowCount - 1]); $counter++) {
                $style = ['protection' => false];
                // apply global body style on every header
                if (array_key_exists('body_style', $this->extraSetting)) {
                    $style = array_merge($style, $this->extraSetting['body_style']);
                }
                if (array_key_exists('option_range', $this->headers[$headersRowCount - 1][$counter]) && !array_key_exists('lookup_for_column', $this->headers[$headersRowCount - 1][$counter])) {
                    $this->setDropdown($sheet, $x, $y, $this->headers[$headersRowCount - 1][$counter]['option_range']);
                }

                $this->setCell($sheet, $x.$y, '', $style);

                if (array_key_exists('lookup_for_column', $this->headers[$headersRowCount - 1][$counter])) {
                    $this->setLookup($sheet, $x.$y, $this->headers[$headersRowCount - 1][$counter]['lookup_arguments']);
                }
                $x++;
            }
            $y++;
        }
    }

    /**
     * Set dropdown for selected cell
     *
     * @param Worksheet $sheet
     * @param $x cell's x-axis
     * @param $y cell's y-axis
     * @param $optionRange
     * @throws Exception
     */
    public function setDropdown(Worksheet &$sheet, $x, $y, $optionRange)
    {
        // retrieve Named Range
        $nameRange = $sheet->getParent()->getNamedRange('option_column_' . $x);
        if (empty($nameRange)) {
            // format: NamedRange(dropdown_unique_name, sheet, cell_range)
            $sheet->getParent()->addNamedRange(new NamedRange('option_column_'.$x, $this->otherSheet->getSheet(), $optionRange));
            $nameRange = $sheet->getParent()->getNamedRange('option_column_' . $x);
        }

        // setup dropdown
        $objValidation = $sheet->getCell($x.$y)->getDataValidation();
        $objValidation->setType(DataValidation::TYPE_LIST);
        $objValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $objValidation->setAllowBlank(false);
        $objValidation->setShowInputMessage(true);
        $objValidation->setShowErrorMessage(true);
        $objValidation->setShowDropDown(true);
        $objValidation->setErrorTitle('Input error');
        $objValidation->setError('Value is not in list.');
        $objValidation->setPromptTitle('Pick from list');
        $objValidation->setPrompt('Please pick a value from the drop-down list.');
        $objValidation->setFormula1($nameRange->getName());
    }

    /**
     * Set cell value and styling
     *
     * @param Worksheet $sheet
     * @param string $coordinate
     * @param string $value
     * @param array $style
     * @throws Exception
     */
    public function setCell(Worksheet &$sheet, string $coordinate,  $value, $style = [])
    {
        $sheet->setCellValue($coordinate, $value);

        // extra cell's style
        if (count($style) > 0) {
            if (array_key_exists('alignment', $style) && is_string($style['alignment'])) {
                $sheet->getStyle($coordinate)->getAlignment()->setHorizontal($style['alignment']);
            }
            if (array_key_exists('vertical_alignment', $style) && is_string($style['vertical_alignment'])) {
                $sheet->getStyle($coordinate)->getAlignment()->setVertical($style['vertical_alignment']);
            }
            if (array_key_exists('bold', $style) && is_bool($style['bold'])) {
                $sheet->getStyle($coordinate)->getFont()->setBold($style['bold']);
            }
            if (array_key_exists('background', $style) && is_string($style['background'])) {
                if (!array_key_exists('fill_type', $style) || !is_string($style['fill_type'])) {
                    $style['fill_type'] = Fill::FILL_SOLID;
                }
                $sheet->getStyle($coordinate)->getFill()->setFillType($style['fill_type'])->getStartColor()->setARGB($style['background']);
            }
            if (array_key_exists('range', $style) && is_string($style['range'])) {
                $sheet->mergeCells($style['range']);
            }
            if (array_key_exists('width', $style) && is_integer($style['width'])) {
                $matches = [];
                if (preg_match('/^([A-Z]+)([0-9]+)$/i', $coordinate, $matches)) {
                    $sheet->getColumnDimension($matches[1])->setWidth($style['width']);
                }
            } elseif (array_key_exists('auto_size', $style) && is_bool($style['auto_size'])) {
                $matches = [];
                if (preg_match('/^([A-Z]+)([0-9]+)$/i', $coordinate, $matches)) {
                    $sheet->getColumnDimension($matches[1])->setAutoSize($style['auto_size']);
                }
            }
            if (array_key_exists('wrap_text', $style) && is_bool($style['wrap_text'])) {
                $sheet->getStyle($coordinate)->getAlignment()->setWrapText($style['wrap_text']);
            }
            if (array_key_exists('protection', $style) && is_bool($style['protection'])) {
                if ($style['protection']) {
                    $sheet->getStyle($coordinate)->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
                } else {
                    $sheet->getStyle($coordinate)->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
                }
            }
        }
    }

    public function setLookup(Worksheet &$sheet, string $coordinate, $lookupArguments)
    {
        list($x, $y) = sscanf($coordinate, "%[A-Z]%d");
        if (array_key_exists("lookup_column", $lookupArguments) && array_key_exists("lookup_range", $lookupArguments) && array_key_exists("column_index", $lookupArguments)) {
            //dd('=IF('.$lookupArguments['lookup_column'].$y.'="","",VLOOKUP('.$lookupArguments['lookup_column'].$y.',no_read_options!'.$lookupArguments['lookup_range'].','.$lookupArguments['column_index'].',0))');
            $sheet->setCellValue($coordinate, '=IF('.$lookupArguments['lookup_column'].$y.'="","",VLOOKUP('.$lookupArguments['lookup_column'].$y.',no_read_options!'.$lookupArguments['lookup_range'].','.$lookupArguments['column_index'].',0))');
        }
    }
}
