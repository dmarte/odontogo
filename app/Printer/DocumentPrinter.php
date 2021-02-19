<?php


namespace App\Printer;

use App\Models\Document;
use App\Models\Team;
use App\Printer\Theme\Page;
use App\Printer\Theme\Theme;

class DocumentPrinter extends Theme
{
    private Team $team;

    public function __construct(private Document $model)
    {
        parent::__construct($this->model->team);

        $this->team = $this->model->team;

        $this->page->title = $this->model->title;
        $this->page->withDate(
            zone: $this->team->time_zone,
        );
        $this->page->withTime(
            zone: $this->team->time_zone,
        );
        $this->page->withPageTitle();

        $this->margins->setTop(0.25);
        $this->margins->setLeft(0.25);
        $this->margins->setRight(0.25);

    }

}
