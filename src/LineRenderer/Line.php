<?php

declare(strict_types=1);

namespace Jfcherng\Diff\LineRenderer;

use Jfcherng\Diff\Contract\LineRenderer\AbstractLineRenderer;
use Jfcherng\Diff\Contract\Renderer\RendererConstant;
use Jfcherng\Utility\MbString;

final class Line extends AbstractLineRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render(MbString $mbOld, MbString $mbNew): void
    {
        [$start, $end] = $this->getChangedExtentRegion($mbOld, $mbNew);

        // two strings are the same
        if ($end === 0) {
            return;
        }

        // two strings are different, we do rendering
        $mbOld->str_enclose_i(
            RendererConstant::HTML_CLOSURES,
            $start,
            $end + $mbOld->strlen() - $start + 1,
        );
        $mbNew->str_enclose_i(
            RendererConstant::HTML_CLOSURES,
            $start,
            $end + $mbNew->strlen() - $start + 1,
        );
    }

    /**
     * Given two strings, determine where the changes in the two strings begin,
     * and where the changes in the two strings end.
     *
     * @param MbString $mbOld the old megabytes line
     * @param MbString $mbNew the new megabytes line
     *
     * @return int[] Array containing the starting position (non-negative) and the ending position (negative)
     *               [0, 0] if two strings are the same
     */
    protected function getChangedExtentRegion(MbString $mbOld, MbString $mbNew): array
    {
        // two strings are the same
        // most lines should be this cases, an early return could save many function calls
        if ($mbOld->getRaw() === $mbNew->getRaw()) {
            return [0, 0];
        }

        // calculate $start
        $start = 0;
        $startMax = min($mbOld->strlen(), $mbNew->strlen());
        while (
            $start < $startMax // index out of range
            && $mbOld->getAtRaw($start) === $mbNew->getAtRaw($start)
        ) {
            ++$start;
        }

        // calculate $end
        $end = -1; // trick
        $endMin = $startMax - $start;
        while (
            -$end <= $endMin // index out of range
            && $mbOld->getAtRaw($end) === $mbNew->getAtRaw($end)
        ) {
            --$end;
        }

        return [$start, $end];
    }
}
