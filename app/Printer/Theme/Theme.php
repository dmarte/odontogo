<?php


namespace App\Printer\Theme;

use App\Models\Team;
use FPDF;
use Illuminate\Support\Facades\Storage;

class Theme
{
    protected Margin $margins;
    protected Page $page;
    private FPDF $engine;

    public function __construct(
        private Team $team,
        private string $orientation = Page::ORIENTATION_PORTRAIT,
        private string $unit = Page::UNIT_INCHES,
        private string $size = Page::SIZE_LETTER,
    ) {
        $this->engine = new FPDF(
            orientation: $orientation,
            size: $size,
            unit: $unit,
        );

        $this->page = new Page($this->engine);
        $this->margins = new Margin(
            engine: $this->engine,
            top: 0,
            left: 0,
            right: 0,
            bottom: 0
        );
    }

    private function encode(string $text)
    {
        return iconv('UTF-8', 'ISO-8859-1', $text);
    }

    private function hex2rgb($hex)
    {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgb = [$r, $g, $b];

        return $rgb;
    }

    public function buildTable()
    {
        $headers = [
            [
                'txt'   => __('Service'),
                'align' => 'L',
            ],
            [
                'txt'   => __('Quantity'),
                'align' => 'C',
            ],
        ];
        $height = 0.2;
        // Colores, ancho de línea y fuente en negrita
//        $this->engine->SetFillColor(255,0,0);
        $this->engine->SetTextColor(...$this->hex2rgb('#5e5e5e'));
        $this->engine->SetDrawColor(...$this->hex2rgb('#5e5e5e'));
        $this->engine->SetLineWidth(.02);
        $this->engine->SetFont('', 'B');

        $max = $this->margins->getRowWidth();
        $width = $max / count($headers);
        foreach ($headers as $header) {
            $this->engine->Cell(
                w: $width,
                h: $height,
                txt: $header['txt'],
                border: 1,
                align: $header['align'],
            );
        }

        $this->engine->Ln();

        // Restauración de colores y fuentes
        $this->engine->SetFillColor(224, 235, 255);
        $this->engine->SetTextColor(0);
        $this->engine->SetFont('');

        $this->engine->Cell($this->margins->getRowWidth(), 0, '', 'T');
    }

    public function buildTopPage()
    {
        if ($this->page->title) {
            $this->engine->SetFont(
                family: 'Courier',
                size: 8,
            );
            $size = $this->margins->getRowWidth() / 3;
            $this->engine->SetTextColor(...$this->hex2rgb('#6e6e6e'));
            $this->engine->Cell(
                w: $size,
                txt: $this->encode((string) $this->page->topLeft),
                align: 'L'
            );
            $this->engine->Cell(
                w: $size,
                txt: $this->encode((string) $this->page->topCenter),
                align: 'C'
            );
            $this->engine->Cell(
                w: $size,
                txt: $this->encode((string) $this->page->topRight),
                align: 'R',
            );
            $this->engine->Ln($this->page->leading);
        }
    }

    public function buildLogo()
    {
        $this->engine->Cell(
            w: $this->margins->getRowWidth() / 2,
            h: $this->page->logoHeight,
        );
        $this->engine->Image(
            file: Storage::disk($this->team->avatar_disk)->path($this->team->avatar_path),
            x: $this->margins->getLeft(),
            h: $this->page->logoHeight,
        );
    }

    public function buildRightHeader(string $title)
    {
        $this->engine->Cell(
            w: $this->margins->getRowWidth() / 2,
            h: $this->page->leading,
            txt: $this->encode($title),
            border: 1,
            align: 'R',
        );
        $this->engine->Cell(
            w: $this->margins->getRowWidth() / 2,
            h: $this->page->leading,
            txt: $this->encode($title),
            border: 1,
            align: 'R',
        );

        $this->engine->Ln(0.5);
    }

    public function buildHeader()
    {
        $this->buildLogo();
        $this->buildRightHeader($this->page->title);
    }

    public function build()
    {

        $this->engine->SetTitle($this->page->title);
        $this->engine->SetTopMargin($this->margins->getTop());
        $this->engine->SetLeftMargin($this->margins->getLeft());
        $this->engine->SetRightMargin($this->margins->getRight());
        $this->engine->AddPage();

        $this->buildTopPage();
        $this->buildHeader();
        $this->buildTable();
    }

    public function render()
    {
        $this->build();

        return response(
            content: $this->engine->Output('I', "{$this->page->title}.pdf"),
            headers: ['Content-Type' => 'application/pdf']
        );
    }
}
