<?php

namespace Models;

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
class SectionItem extends RowItem{ 
  public array $rows = [];
}