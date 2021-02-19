<?php


namespace App\Printer\Theme;


use FPDF;

class Margin
{
    public function __construct(private FPDF $engine, private float $top, private float $left, private float $right, private float $bottom)
    {
    }

    public function getRowWidth(): float
    {
        return $this->engine->GetPageWidth() - ($this->left + $this->right);
    }

    /**
     * @param  float  $top
     */
    public function setTop(float $top): void
    {
        $this->top = $top;
    }

    /**
     * @param  float  $bottom
     */
    public function setBottom(float $bottom): void
    {
        $this->bottom = $bottom;
    }

    /**
     * @param  float  $left
     */
    public function setLeft(float $left): void
    {
        $this->left = $left;
    }

    /**
     * @param  float  $right
     */
    public function setRight(float $right): void
    {
        $this->right = $right;
    }

    /**
     * @return float
     */
    public function getBottom(): float
    {
        return $this->bottom;
    }

    /**
     * @return float
     */
    public function getLeft(): float
    {
        return $this->left;
    }

    /**
     * @return float
     */
    public function getRight(): float
    {
        return $this->right;
    }

    /**
     * @return float
     */
    public function getTop(): float
    {
        return $this->top;
    }
}
