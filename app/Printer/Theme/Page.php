<?php


namespace App\Printer\Theme;


use FPDF;

class Page
{
    public const ORIENTATION_PORTRAIT = 'P';
    public const UNIT_INCHES = 'in';
    public const UNIT_POINTS = 'pt';
    public const SIZE_LETTER = 'Letter';

    public function __construct(
        private FPDF $engine,
        public string|null $title = null,
        public string|null $topLeft = null,
        public string|null $topCenter = null,
        public string|null $topRight = null,
        public float $logoHeight = .7,
        public float $leading = .35,
    ) {}

    public function withTime(string $zone = 'UTC', string $format = 'h:i A') : Page {
        $this->topLeft = now()->setTimezone($zone)->format($format);
        return $this;
    }

    public function withDate(string $zone = 'UTC', string $format = 'd/m/Y') : Page {
        $this->topRight = now()->setTimezone($zone)->format($format);
        return $this;
    }

    public function withPageTitle() : Page {
        $this->topCenter = $this->title;
        return $this;
    }

}
