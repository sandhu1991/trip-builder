<?php

namespace App\Helpers;

class trip 
{
    public $departure;
    public $return;
    public $fullPrice;
    public $oneway;

    public function set_departure($departure){

        $this->departure = $departure;
    }
    public function get_departure(){
       
        return $this->departure;
    }

    public function set_return($return){

        $this->return = $return;
    }
    public function get_return(){
       
        return $this->return;
    }

    public function set_fullPrice($fullPrice){

        $this->fullPrice = $fullPrice;
    }
    public function get_fullPrice(){
       
        return $this->fullPrice;
    }

    public function set_oneway($oneway){

        $this->oneway = $oneway;
    }
    public function get_oneway(){
       
        return $this->oneway;
    }

    public function jsonSearlize(){

        $array = [
            'fullPrice'   => $this->get_fullPrice(),
            'departure'    => $this->get_departure(),
            'return'      => $this->get_return(),
            'oneway'      => $this->get_oneway()
        ];
        return $array;
    }


}
