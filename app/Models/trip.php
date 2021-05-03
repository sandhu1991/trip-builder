<?php

namespace App\Models;

class trip extends Model
{
    public $to;
    public $from;
    public $fullPrice;
    public $oneWay;

    public function set_to($to){

        $this->to = $to;
    }
    public function get_to(){
       
        return $this->to;
    }

    public function set_from($from){

        $this->from = $from;
    }
    public function get_from(){
       
        return $this->from;
    }

    public function set_fullPrice($fullPrice){

        $this->fullPrice = $fullPrice;
    }
    public function get_fullPrice(){
       
        return $this->fullPrice;
    }

    public function set_oneWay($oneWay){

        $this->oneWay = $oneWay;
    }
    public function get_oneWay(){
       
        return $this->oneWay;
    }
}
