<?php
namespace Models;

/**
 * Carries the information for a single day's sales.
 * 
 * @category Model
 */
class DepartmentSales{

    /**
     * The number of sales
     * @var int
     */
    protected int $number;
    /**
     * The amoaunt of sales
     * @var float
     */
    protected float $sales;

    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * Static constructor / factory
     */
    public static function create() {
        return new self();
    }

    /**
     * Number setter - fluent style
     */
    public function setNumber(int $number) {
        $this->number = $number;
        return $this;
    }

    /**
     * Sales setter - fluent style
     */
    public function setSales(float $sales) {
        $this->sales = $sales;
        return $this;
    }
}