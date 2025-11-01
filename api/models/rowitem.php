<?php

namespace Models;

/**
 * A data class that holds a simplified view of report data for a single row.
 * It has three columns: Display Name, Current Value and Previous Value.
 * 
 * @category Model
 */
class RowItem{ 
  public string $displayName ='';
  public float $currentValue = 0;
  public float $previousValue = 0;

  public function Add(RowItem $rowItem) {
      $this->currentValue += $rowItem->currentValue;
      $this->currentValue = round($this->currentValue,2);
      $this->previousValue += $rowItem->previousValue;
      $this->previousValue = round($this->previousValue,2);
  }
}
/**
 * A data class that holds a simplified view of report data for a section containing multiple rows.
 * Extends RowItem to include an array of RowItems.
 * 
 * @category Model
 */
class SectionItem extends RowItem{ 
  public array $rows = [];
}