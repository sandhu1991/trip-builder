<?php

namespace App\Helpers;

class flight 
{
    public $date;
    public $flights;
    public $connections;
    public $price;

    public function set_date($date){

        $this->date = $date;
    }
    public function get_date(){
       
        return $this->date;
    }

    public function set_flights($flights){

        $this->flights = $flights;
    }
    public function get_flights(){
       
        return $this->flights;
    }

    public function set_connections($connections){

        $this->connections = $connections;
    }
    public function get_connections(){
       
        return $this->connections;
    }

    public function set_price($price){

        $this->price = $price;
    }
    public function get_price(){
       
        return $this->price;
    }

    public function jsonSearlize(){

        $array = [
            'connections'   => $this->get_connections(),
            'date'          => $this->get_date(),
            'flights'       => $this->get_flights(),
            'price'         => $this->get_price()
        ];
        return $array;
    }


}
