<?php
namespace grooveround\image\helpers;
/**
 * Resizing constraints and Flipping directions
 *
 * Class ResizingConstraint
 * @package console\modules\helpers
 * @author Deick Fynn <dcfynn@vodamail.co.za>
 */
class ResizingConstraint
{
    // Resizing constraints
    const NONE = 1;
    const WIDTH = 2;
    const HEIGHT = 3;
    const AUTO = 4;
    const INVERSE = 5;
    const PRECISE = 6;

    // Flipping directions
    const HORIZONTAL = 7;
    const VERTICAL = 8;
} 