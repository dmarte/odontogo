<?php


namespace App\Printer;

use App\Models\Document;
use App\Models\Item;
use Illuminate\Support\Facades\Storage;
use Konekt\PdfInvoice\InvoicePrinter as Printer;
use Laravel\Nova\Fields\Country;

class BudgetPrinter extends Printer
{
    public function __construct(public Document $model)
    {
        parent::__construct('Letter', $model->currency, request()->user()->locale);

        $this->changeLanguageTerm('from', __('Team'));
        $this->changeLanguageTerm('to', __('Patient'));
        $this->changeLanguageTerm('number', __('Code'));
        $this->changeLanguageTerm('date', __('Date'));
        $this->changeLanguageTerm('due', __('Expiration'));
        $this->changeLanguageTerm('product', __('Service or procedure'));
        $this->changeLanguageTerm('vat', __('Tax'));
        $this->changeLanguageTerm('total', __('Total'));
        $this->columnOpacity = 0;

        $this->setLogo(Storage::disk($model->team->avatar_disk)->path($model->team->avatar_path));
        $this->setColor('#5e5e5e');
        $this->setFontSizeProductDescription(9);
        $this->setType($model->sequence->title);
        $this->setReference($model->sequence_value);
        $this->setDate($model->emitted_at->format('d/m/Y'));
        $this->setDue($model->expire_at->format('d/m/Y'));
        $this->flipflop();

        if ($this->model->description) {
            $this->addParagraph($model->description);
        }

        $this->setFrom(array_filter([
            $model->team->name,
            $model->team->vat ? __(strtolower($model->team->country)."_tax_payer_number").': '.$model->team->vat : null,
            $model->team->address_line_1 ?? null,
            collect((new Country(''))->meta['options'])->firstWhere('value', $model->team->country)['label'] ?? $model->team->country,
            $model->team->phone_primary,
        ]));
        $this->setTo(array_filter([
            $model->receiver->tax_payer_name,
            $model->receiver->tax_payer_number,
            $model->receiver->phone_primary,
            $model->receiver->address_line_1,
            $model->receiver->address_line_2,
        ]));
        $model->items->each(function (Item $item) {
            $this->addItem(
                "{$item->product->code} - {$item->product->name}",
                $item->description,
                $item->quantity,
                false,
                $item->price,
                $item->discounts,
                $item->total
            );
        });

        $this->addTotal(__('Discounts'), $model->discounts);
        $this->addTotal(__('Subtotal'), $model->subtotal);
        $this->addTotal(__('Taxes'), $model->taxes);
        $this->addTotal(__('Total'), $model->total, true);

        $this->addCustomHeader(__('Created by'), $model->author->name);
        $this->addCustomHeader(__('Printed at'), now()->format('d/m/Y H:i A'));
        $this->addCustomHeader(__('Doctor'), $this->model->provider->name);

        $this->setFooternote("{$model->title} / {$model->code}");
    }

    public function Body()
    {
        $width_other = ($this->document['w'] - $this->margins['l'] - $this->margins['r'] - $this->firstColumnWidth - ($this->columns * $this->columnSpacing)) / ($this->columns - 1);
        $cellHeight = 8;
        $bgcolor = (1 - $this->columnOpacity) * 255;
        if ($this->items) {
            foreach ($this->items as $item) {

                if ((empty($item['item'])) || (empty($item['description']))) {
                    $this->Ln($this->columnSpacing);
                }

                if ($item['description']) {
                    //Precalculate height
                    $calculateHeight = new parent();
                    $calculateHeight->addPage();
                    $calculateHeight->setXY(0, 0);
                    $calculateHeight->SetFont($this->font, '', 7);
                    $calculateHeight->MultiCell(
                        $this->firstColumnWidth,
                        3,
                        iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, $item['description']),
                        0,
                        'L',
                        1
                    );
                    $descriptionHeight = $calculateHeight->getY() + $cellHeight + 2;
                    $pageHeight = $this->document['h'] - $this->GetY() - $this->margins['t'] - $this->margins['t'];
                    if ($pageHeight < 35) {
                        $this->AddPage();
                    }
                }

                $cHeight = $cellHeight;
                $this->SetFont($this->font, 'b', 8);
                $this->SetTextColor(50, 50, 50);
                $this->SetFillColor($bgcolor, $bgcolor, $bgcolor);
                $this->Cell(1, $cHeight, '', 0, 0, 'L', 1);
                $x = $this->GetX();
                $this->Cell(
                    $this->firstColumnWidth,
                    $cHeight,
                    iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, $item['item']),
                    0,
                    0,
                    'L',
                    1
                );
                if ($item['description']) {
                    $resetX = $this->GetX();
                    $resetY = $this->GetY();
                    $this->SetTextColor(120, 120, 120);
                    $this->SetXY($x, $this->GetY() + 8);
                    $this->SetFont($this->font, '', $this->fontSizeProductDescription);
                    $this->MultiCell(
                        $this->firstColumnWidth,
                        floor($this->fontSizeProductDescription / 2),
                        iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, $item['description']),
                        0,
                        'L',
                        1
                    );
                    //Calculate Height
                    $newY = $this->GetY();
                    $cHeight = $newY - $resetY + 2;
                    //Make our spacer cell the same height
                    $this->SetXY($x - 1, $resetY);
                    $this->Cell(1, $cHeight, '', 0, 0, 'L', 1);
                    //Draw empty cell
                    $this->SetXY($x, $newY);
                    $this->Cell($this->firstColumnWidth, 2, '', 0, 0, 'L', 1);
                    $this->SetXY($resetX, $resetY);
                }
                $this->SetTextColor(50, 50, 50);
                $this->SetFont($this->font, '', 8);
                $this->Cell($this->columnSpacing, $cHeight, '', 0, 0, 'L', 0);
                $this->Cell($width_other, $cHeight, $item['quantity'], 0, 0, 'C', 1);
                if (isset($this->vatField)) {
                    $this->Cell($this->columnSpacing, $cHeight, '', 0, 0, 'R', 0);
                    if (isset($item['vat'])) {
                        $this->Cell($width_other, $cHeight, iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_B, $item['vat']), 0, 0, 'C', 1);
                    } else {
                        $this->Cell($width_other, $cHeight, '', 0, 0, 'R', 1);
                    }
                }
                $this->Cell($this->columnSpacing, $cHeight, '', 0, 0, 'R', 0);

                $this->Cell($width_other, $cHeight, iconv(
                    self::ICONV_CHARSET_INPUT,
                    self::ICONV_CHARSET_OUTPUT_B,
                    $this->price($item['price'])
                ), 0, 0, 'R', 1);

                if (isset($this->discountField)) {
                    $this->Cell($this->columnSpacing, $cHeight, '', 0, 0, 'R', 0);
                    if (isset($item['discount'])) {
                        $this->Cell(
                            $width_other,
                            $cHeight,
                            iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_B, $item['discount']),
                            0,
                            0,
                            'R',
                            1
                        );
                    } else {
                        $this->Cell($width_other, $cHeight, '', 0, 0, 'R', 1);
                    }
                }
                $this->Cell($this->columnSpacing, $cHeight, '', 0, 0, 'R', 0);
                $this->Cell($width_other, $cHeight, iconv(
                    self::ICONV_CHARSET_INPUT,
                    self::ICONV_CHARSET_OUTPUT_B,
                    $this->price($item['total'])
                ), 0, 0, 'R', 1);
                $this->Ln();
                $this->SetLineWidth(0.5);
                $this->SetDrawColor(...$this->color);
                $this->Line(
                    $this->margins['l'],
                    $this->GetY(),
                    $this->document['w'] - $this->margins['r'],
                    $this->GetY()
                );
                $this->Ln($this->columnSpacing);
            }
        }
        $badgeX = $this->getX();
        $badgeY = $this->getY();

        //Add totals
        if ($this->totals) {
            foreach ($this->totals as $total) {
                $this->SetTextColor(50, 50, 50);
                $this->SetFillColor($bgcolor, $bgcolor, $bgcolor);
                $this->Cell(1 + $this->firstColumnWidth, $cellHeight, '', 0, 0, 'L', 0);
                for ($i = 0; $i < $this->columns - 3; $i++) {
                    $this->Cell($width_other, $cellHeight, '', 0, 0, 'R', 0);
                    $this->Cell($this->columnSpacing, $cellHeight, '', 0, 0, 'R', 0);
                }
                $this->Cell($this->columnSpacing, $cellHeight, '', 0, 0, 'R', 0);
                if ($total['colored']) {
                    $this->SetTextColor(255, 255, 255);
                    $this->SetFillColor($this->color[0], $this->color[1], $this->color[2]);
                }
                $this->SetFont($this->font, 'b', 8);
                $this->Cell(1, $cellHeight, '', 0, 0, 'L', 1);
                $this->Cell(
                    $width_other - 1,
                    $cellHeight,
                    iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_B, $total['name']),
                    0,
                    0,
                    'L',
                    1
                );
                $this->Cell($this->columnSpacing, $cellHeight, '', 0, 0, 'L', 0);
                $this->SetFont($this->font, 'b', 8);
                $this->SetFillColor($bgcolor, $bgcolor, $bgcolor);
                if ($total['colored']) {
                    $this->SetTextColor(255, 255, 255);
                    $this->SetFillColor($this->color[0], $this->color[1], $this->color[2]);
                }
                $this->Cell($width_other, $cellHeight, iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_B, $total['value']), 0, 0, 'R', 1);
                $this->Ln();
                $this->Ln($this->columnSpacing);
            }
        }
        $this->productsEnded = true;
        $this->Ln();
        $this->Ln(3);

        //Badge
        if ($this->badge) {
            $badge = ' '.mb_strtoupper($this->badge, self::ICONV_CHARSET_INPUT).' ';
            $resetX = $this->getX();
            $resetY = $this->getY();
            $this->setXY($badgeX, $badgeY + 15);
            $this->SetLineWidth(0.4);
            $this->SetDrawColor($this->badgeColor[0], $this->badgeColor[1], $this->badgeColor[2]);
            $this->setTextColor($this->badgeColor[0], $this->badgeColor[1], $this->badgeColor[2]);
            $this->SetFont($this->font, 'b', 15);
            $this->Rotate(10, $this->getX(), $this->getY());
            $this->Rect($this->GetX(), $this->GetY(), $this->GetStringWidth($badge) + 2, 10);
            $this->Write(10, iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_B, mb_strtoupper($badge, self::ICONV_CHARSET_INPUT)));
            $this->Rotate(0);
            if ($resetY > $this->getY() + 20) {
                $this->setXY($resetX, $resetY);
            } else {
                $this->Ln(18);
            }
        }

        //Add information
        foreach ($this->addText as $text) {
            if ($text[0] == 'title') {
                $this->SetFont($this->font, 'b', 9);
                $this->SetTextColor(50, 50, 50);
                $this->Cell(0, 10, iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, mb_strtoupper($text[1], self::ICONV_CHARSET_INPUT)), 0, 0,
                    'L', 0);
                $this->Ln();
                $this->SetLineWidth(0.3);
                $this->SetDrawColor($this->color[0], $this->color[1], $this->color[2]);
                $this->Line(
                    $this->margins['l'],
                    $this->GetY(),
                    $this->document['w'] - $this->margins['r'],
                    $this->GetY()
                );
                $this->Ln(4);
            }
            if ($text[0] == 'paragraph') {
                $this->SetTextColor(80, 80, 80);
                $this->SetFont($this->font, '', 8);
                $this->MultiCell(0, 4, iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, $text[1]), 0, 'L', 0);
                $this->Ln(4);
            }
        }
    }

    public function Header()
    {
        if (isset($this->logo) and !empty($this->logo)) {
            $this->Image(
                $this->logo,
                $this->margins['l'],
                $this->margins['t'],
                $this->dimensions[0],
                $this->dimensions[1]
            );
        }

        //Title
        $this->SetTextColor(...$this->color);
        $this->SetFont($this->font, 'B', 20);
        if (isset($this->title) and !empty($this->title)) {
            $this->Cell(
                w: 0,
                h: 5,
                txt: iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, mb_strtoupper($this->title, self::ICONV_CHARSET_INPUT)),
                ln: 1,
                align: 'R',
            );
        }
        $this->SetFont($this->font, '', 9);
        $this->Ln(5);

        $lineheight = 5;
        //Calculate position of strings
        $this->SetFont($this->font, 'B', 9);

        $positionX = ($this->document['w'] - $this->margins['l'] - $this->margins['r']) / 2;

        //Number
        if (!empty($this->reference)) {
            $this->Cell($positionX, $lineheight);
            $this->SetTextColor($this->color[0], $this->color[1], $this->color[2]);
            $this->Cell(
                32,
                $lineheight,
                iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, mb_strtoupper($this->lang['number'], self::ICONV_CHARSET_INPUT).':'),
                0,
                0,
                'L'
            );
            $this->SetTextColor(50, 50, 50);
            $this->SetFont($this->font, '', 9);
            $this->Cell(0, $lineheight, $this->reference, 0, 1, 'R');
        }
        //Date
        $this->Cell($positionX, $lineheight);
        $this->SetFont($this->font, 'B', 9);
        $this->SetTextColor($this->color[0], $this->color[1], $this->color[2]);
        $this->Cell(32, $lineheight,
            iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, mb_strtoupper($this->lang['date'], self::ICONV_CHARSET_INPUT)).':', 0, 0, 'L');
        $this->SetTextColor(50, 50, 50);
        $this->SetFont($this->font, '', 9);
        $this->Cell(0, $lineheight, $this->date, 0, 1, 'R');

        //Time
        if (!empty($this->time)) {
            $this->Cell($positionX, $lineheight);
            $this->SetFont($this->font, 'B', 9);
            $this->SetTextColor($this->color[0], $this->color[1], $this->color[2]);
            $this->Cell(
                32,
                $lineheight,
                iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, mb_strtoupper($this->lang['time'], self::ICONV_CHARSET_INPUT)).':',
                0,
                0,
                'L'
            );
            $this->SetTextColor(50, 50, 50);
            $this->SetFont($this->font, '', 9);
            $this->Cell(0, $lineheight, $this->time, 0, 1, 'R');
        }
        //Due date
        if (!empty($this->due)) {
            $this->Cell($positionX, $lineheight);
            $this->SetFont($this->font, 'B', 9);
            $this->SetTextColor($this->color[0], $this->color[1], $this->color[2]);
            $this->Cell(32, $lineheight,
                iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, mb_strtoupper($this->lang['due'], self::ICONV_CHARSET_INPUT)).':', 0, 0, 'L');
            $this->SetTextColor(50, 50, 50);
            $this->SetFont($this->font, '', 9);
            $this->Cell(0, $lineheight, $this->due, 0, 1, 'R');
        }
        //Custom Headers
        if (count($this->customHeaders) > 0) {
            foreach ($this->customHeaders as $customHeader) {
                $this->Cell($positionX, $lineheight);
                $this->SetFont($this->font, 'B', 9);
                $this->SetTextColor(...$this->color);
                $this->Cell(
                    w: 32,
                    h: $lineheight,
                    txt: iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, mb_strtoupper($customHeader['title'], self::ICONV_CHARSET_INPUT)).':',
                    align: 'L'
                );
                $this->SetTextColor(50, 50, 50);
                $this->SetFont($this->font, '', 9);
                $this->Cell(0, $lineheight, $customHeader['content'], 0, 1, 'R');
            }
        }

        //First page
        if ($this->PageNo() == 1) {
            $dimensions = $this->dimensions[1] ?? 0;
            if (($this->margins['t'] + $dimensions) > $this->GetY()) {
                $this->SetY($this->margins['t'] + $dimensions + 5);
            } else {
                $this->SetY($this->GetY() + 10);
            }
            $this->Ln(5);
            $this->SetFillColor($this->color[0], $this->color[1], $this->color[2]);
            $this->SetTextColor($this->color[0], $this->color[1], $this->color[2]);

            $this->SetDrawColor($this->color[0], $this->color[1], $this->color[2]);
            $this->SetFont($this->font, 'B', 10);
            $width = ($this->document['w'] - $this->margins['l'] - $this->margins['r']) / 2;
            if (isset($this->flipflop)) {
                $to = $this->lang['to'];
                $from = $this->lang['from'];
                $this->lang['to'] = $from;
                $this->lang['from'] = $to;
                $to = $this->to;
                $from = $this->from;
                $this->to = $from;
                $this->from = $to;
            }

            if ($this->display_tofrom === true) {
                if ($this->displayToFromHeaders === true) {
                    $this->Cell($width, $lineheight,
                        iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, mb_strtoupper($this->lang['from'], self::ICONV_CHARSET_INPUT)), 0, 0,
                        'L');
                    $this->Cell(0, $lineheight,
                        iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, mb_strtoupper($this->lang['to'], self::ICONV_CHARSET_INPUT)), 0, 0, 'L');
                    $this->Ln(7);
                    $this->SetLineWidth(0.4);
                    $this->Line($this->margins['l'], $this->GetY(), $this->margins['l'] + $width - 10, $this->GetY());
                    $this->Line(
                        $this->margins['l'] + $width,
                        $this->GetY(),
                        $this->margins['l'] + $width + $width,
                        $this->GetY()
                    );
                } else {
                    $this->Ln(2);
                }

                //Information
                $this->Ln(5);
                $this->SetTextColor(50, 50, 50);
                $this->SetFont($this->font, 'B', 10);
                $this->Cell($width, $lineheight, $this->from[0] ?? 0, 0, 0, 'L');
                $this->Cell(0, $lineheight, iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, $this->to[0] ?? 0), 0, 0, 'L');
                $this->SetFont($this->font, '', 8);
                $this->SetTextColor(100, 100, 100);
                $this->Ln(7);
                for ($i = 1, $iMax = max($this->from === null ? 0 : count($this->from), $this->to === null ? 0 : count($this->to)); $i < $iMax; $i++) {
                    // avoid undefined error if TO and FROM array lengths are different
                    if (!empty($this->from[$i]) || !empty($this->to[$i])) {
                        $this->Cell($width, $lineheight,
                            iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, empty($this->from[$i]) ? '' : $this->from[$i]), 0, 0, 'L');
                        $this->Cell(0, $lineheight, iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, empty($this->to[$i]) ? '' : $this->to[$i]),
                            0, 0, 'L');
                    }
                    $this->Ln(5);
                }
                $this->Ln(-6);
                $this->Ln(5);
            } else {
                $this->Ln(-10);
            }
        }
        //Table header
        if (!isset($this->productsEnded)) {
            $width_other = ($this->document['w'] - $this->margins['l'] - $this->margins['r'] - $this->firstColumnWidth - ($this->columns * $this->columnSpacing)) / ($this->columns - 1);
            $this->SetTextColor(50, 50, 50);
            $this->Ln(12);
            $this->SetFont($this->font, 'B', 9);
            $this->Cell(1, 10, '', 0, 0, 'L', 0);
            $this->Cell(
                $this->firstColumnWidth,
                10,
                iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, mb_strtoupper($this->lang['product'], self::ICONV_CHARSET_INPUT)),
                0,
                0,
                'L',
                0
            );
            $this->Cell($this->columnSpacing, 10, '', 0, 0, 'L', 0);
            $this->Cell($width_other, 10,
                iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, mb_strtoupper($this->lang['qty'], self::ICONV_CHARSET_INPUT)), 0, 0, 'C', 0);
            if (isset($this->vatField)) {
                $this->Cell($this->columnSpacing, 10, '', 0, 0, 'L', 0);
                $this->Cell(
                    $width_other,
                    10,
                    iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, mb_strtoupper($this->lang['vat'], self::ICONV_CHARSET_INPUT)),
                    0,
                    0,
                    'C',
                    0
                );
            }
            $this->Cell($this->columnSpacing, 10, '', 0, 0, 'L', 0);
            $this->Cell($width_other, 10,
                iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, mb_strtoupper($this->lang['price'], self::ICONV_CHARSET_INPUT)), 0, 0, 'C', 0);
            if (isset($this->discountField)) {
                $this->Cell($this->columnSpacing, 10, '', 0, 0, 'L', 0);
                $this->Cell(
                    $width_other,
                    10,
                    iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, mb_strtoupper($this->lang['discount'], self::ICONV_CHARSET_INPUT)),
                    0,
                    0,
                    'C',
                    0
                );
            }
            $this->Cell($this->columnSpacing, 10, '', 0, 0, 'L', 0);
            $this->Cell($width_other, 10,
                iconv(self::ICONV_CHARSET_INPUT, self::ICONV_CHARSET_OUTPUT_A, mb_strtoupper($this->lang['total'], self::ICONV_CHARSET_INPUT)), 0, 0, 'C', 0);
            $this->Ln();
            $this->SetLineWidth(0.3);
            $this->SetDrawColor($this->color[0], $this->color[1], $this->color[2]);
            $this->Line($this->margins['l'], $this->GetY(), $this->document['w'] - $this->margins['r'], $this->GetY());
            $this->Ln(2);
        } else {
            $this->Ln(12);
        }
    }

    public function inline()
    {
        return parent::render("{$this->model->code}.pdf", 'S');
    }

    public function store(string $disk = 'public')
    {
        $disk = $disk ?? config('filesystems.default');

        $path = Storage::disk($disk)->path("{$this->model->team_id}/documents/{$this->model->code}.pdf");

        Storage::disk($disk)->makeDirectory($path);

        parent::render($path, 'F');

        return $path;
    }

    public function remove(string|null $disk = null): bool
    {
        $disk = $disk ?? config('filesystems.default');

        return Storage::disk($disk)->delete("{$this->model->team_id}/documents/{$this->model->code}.pdf");
    }

    public function viewOnline()
    {
        return response($this->render("{$this->model->code}.pdf"), 200, ['Content-Type' => 'application/pdf']);
    }
}
