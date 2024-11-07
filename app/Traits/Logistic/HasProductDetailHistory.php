<?php

namespace App\Traits\Logistic;

trait HasProductDetailHistory{
    abstract public function masterTable();
    abstract public function remarksTableInfo():array;
}
