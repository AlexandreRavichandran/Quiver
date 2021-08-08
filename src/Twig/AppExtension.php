<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('formatViewsNumber', [$this, 'formatViewsNumber']),
        ];
    }

    /**
     * Format views number
     * 'X XXX' format between 0 and 9 999 views
     * 'XXX K' format between 10 000 and 999 K views
     * 
     * @param integer $views
     * @return void
     */
    public function formatViewsNumber($viewsNumber)
    {
        $formattedViewsNumber = '';
        if ($viewsNumber >= 0 && $viewsNumber <= 9999) {
            $formattedViewsNumber = number_format($viewsNumber, 0, ' ', ' ');
        } elseif ($viewsNumber >= 10000 && $viewsNumber < 100000) {
            $thousandValues = str_split($viewsNumber);
            $formattedViewsNumber = $thousandValues[0] . $thousandValues[1] . ' K';
        } elseif ($viewsNumber >= 100000) {
            $thousandValues = str_split($viewsNumber);
            $formattedViewsNumber = $thousandValues[0] . $thousandValues[1] . $thousandValues[2] . ' K';
        }

        return $formattedViewsNumber;
    }
}
