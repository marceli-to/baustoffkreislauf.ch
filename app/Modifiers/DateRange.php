<?php

namespace App\Modifiers;

use Carbon\Carbon;
use Statamic\Modifiers\Modifier;

class DateRange extends Modifier
{
  /**
   * Format a start date with an optional end date as a range,
   * e.g. "4.–5. Juni 2027" or "30. Juni – 2. Juli 2027"
   *
   * @param mixed $value The start date
   * @param array $params The end date (variable name or value)
   * @param array $context The context
   * @return string
   */
  public function index($value, $params, $context)
  {
    $start = $this->toCarbon($value);

    if (!$start)
    {
      return '';
    }

    $endParam = $params[0] ?? null;
    if (is_string($endParam) && array_key_exists($endParam, $context))
    {
      $endParam = $context[$endParam];
    }
    $end = $this->toCarbon($endParam);

    if (!$end || $end->lte($start) || $end->isSameDay($start))
    {
      return $start->isoFormat('Do MMMM YYYY');
    }

    if ($start->isSameMonth($end))
    {
      return $start->isoFormat('Do') . '–' . $end->isoFormat('Do MMMM YYYY');
    }

    if ($start->isSameYear($end))
    {
      return $start->isoFormat('Do MMMM') . ' – ' . $end->isoFormat('Do MMMM YYYY');
    }

    return $start->isoFormat('Do MMMM YYYY') . ' – ' . $end->isoFormat('Do MMMM YYYY');
  }

  private function toCarbon($value)
  {
    if ($value instanceof \Statamic\Fields\Value)
    {
      $value = $value->value();
    }

    if (empty($value))
    {
      return null;
    }

    if ($value instanceof Carbon)
    {
      return $value->copy();
    }

    try
    {
      return Carbon::parse($value);
    }
    catch (\Exception $e)
    {
      return null;
    }
  }
}
