<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\flight;
use App\Helpers\trip;
use DateTime;
use Validator;

class FlightController extends Controller
{

    private $connections;
    private $stops;

    /**
        * @OA\Get(
        * path="/api/trips",
        *  summary="Get All Trips",
        *  @OA\Parameter(name="from",
        *    in="query",
        *    description="Depature Airport Code",
        *    @OA\Schema(type="string")
        *  ),
        *  @OA\Parameter(name="departDate",
        *    in="query",
        *    description="Depature Date",
        *    @OA\Schema(type="string")
        *  ),
        *  @OA\Parameter(name="to",
        *    in="query",
        *    description="Arrival Airport Code",
        *    @OA\Schema(type="string")
        *  ),
         *  @OA\Parameter(name="returnDate",
        *    in="query",
        *    description="Return Date",
        *    @OA\Schema(type="string")
        *  ),
        *  @OA\Parameter(name="oneway",
        *    in="query",
        *    description="Flight Type",
        *    @OA\Schema(type="string", default=false)
        *  ),
        *  @OA\Parameter(name="airline",
        *    in="query",
        *    description="Airline Code",
        *    @OA\Schema(type="string")
        *  ),
         *  @OA\Parameter(name="stops",
        *    in="query",
        *    description="Number of stops",
        *    @OA\Schema(type="integer")
        *  ),
        *  @OA\Response(response="200",
        *    description="Validation Response",
        *  )
        * )
    */
    public function getTrips(request $request)
    {

        //add validation rules
        $validator = Validator::make($request->all(), [
            'from'=>'required|string',
            'to'=>'required|string',
            'departDate'=>'required|date_format:Y-m-d|after:today',
            'returnDate'=>'nullable|date_format:Y-m-d|after_or_equal:departDate',
            'stops'=>'nullable|integer',
            'oneway'=>'nullable|string',
            'airline'=>'nullable|string',
        ]);
        
        if($validator->fails()){
            return response()->json($validator->errors());
        }

        //get data from request
        $data = $request->all();

        $from = $data['from'] ?? false;
        $to = $data['to'] ?? false;
        $departDate = $data['departDate'] ?? false;
        $returnDate = $data['returnDate'] ?? false;
        $stops = $data['stops'] ?? null;
        $oneway = (isset($data['oneway']) && $data['oneway'] == 'true') ?  true : false;
        $airline = isset($data['airline']) ?  $data['airline'] : false;

        $results = json_decode(file_get_contents(base_path('resources/sampleData.json')));
        $flights = $results->flights;

        // filter by airline
        if($airline){
            $flights = array_filter($flights,function($item) use($airline){
                return $item->airline == strtoupper($airline);
            });
        }

        if(count($flights) == 0){
            return 'NO trips avaiable for this search';
        }

        // get the trips
        $response = $this->getFlights($flights, $to, $from, $departDate, $returnDate, $oneway, $stops);

        return response()->json(['code'=>200, 'status'=> 'Sucess', 'data'=>$response]);
    }
   
    /**
        * @OA\Get(
        * path="/api/countires",
        *  summary="Get All Countries",
        *  @OA\Parameter(name="code",
        *    in="query",
        *    description="Country Code",
        *    @OA\Schema(type="string")
        *  ),
        *  @OA\Response(response="200",
        *    description="Validation Response",
        *  )
        * )
    */
    public function getCountires(request $request)
    {
        //add validation rules
        $validator = Validator::make($request->all(), [
            'code'=>'required|string',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors());
        }

        $data = $request->all();
        $code = isset($data['code']) ?  $data['code'] : false;

        $results = json_decode(file_get_contents(base_path('resources/sampleData.json')));
        $data = $results->airlines;

        if($code){
            $data = array_filter($data,function($item) use($code){
                return strtoupper($item->code) == strtoupper($code);
            });
        }

        return response()->json(['code'=>200, 'status'=> 'Sucess', 'data'=>$data]);
    }

    /**
        * @OA\Get(
        * path="/api/airports",
        *  summary="Get All Airports",
        *  @OA\Parameter(name="code",
        *    in="query",
        *    description="Airport Code",
        *    @OA\Schema(type="string")
        *  ),
        *  @OA\Response(response="200",
        *    description="Validation Response",
        *  )
        * )
    */
    public function getAirports(request $request)
    {
        //add validation rules
        $validator = Validator::make($request->all(), [
            'code'=>'required|string',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors());
        }

        $data = $request->all();
        $code = isset($data['code']) ?  $data['code'] : false;

        $results = json_decode(file_get_contents(base_path('resources/sampleData.json')));
        $data = $results->airports;

        if($code){
            $data = array_filter($data,function($item) use($code){
                return strtoupper($item->code) == strtoupper($code);
            });
        }

        return response()->json(['code'=>200, 'status'=> 'Sucess', 'data'=>$data]);
    }


    //private methods for the class
    private function getFlights($flights, $to, $from, $departDate, $returnDate, $oneway = false, $stops = 0)
    {
        $data = []; 

        $depatureTrips=[];

        $landingFlights = array_filter($flights,function($item) use($to, $from){
            return strtoupper($item->arrival_airport) == strtoupper($to);
        });


        foreach ($landingFlights as $key => $flight) {

            if(strtoupper($flight->departure_airport) == strtoupper($from) && strtoupper($flight->arrival_airport) == strtoupper($to)){

                $depatureTrips[] = $flight;

            }else{

                $this->connections = [];
                $this->stops = 1;

                $response = $this->getConnections($flights, $from, $flight, $stops);
                
                if(count($this->connections) > 0){

                    $depatureTrips[] = array_reverse($this->connections);
                }
            }
        }

        // return if it is oneway trip
        if($oneway){

            foreach($depatureTrips as $depature){
            
                $stops = 0;
                if(is_array($depature)){
                    $stops = count($depature)-1;
                    $fullPrice = 0;
                    foreach ($depature as $depart) {

                        $fullPrice += $depart->price;
                    }
                }else{
                    $fullPrice = $depature->price;
                }

                // set depature flight Flight
                $depatureFlight = new flight();
                $depatureFlight->set_date($departDate);
                $depatureFlight->set_flights($depature);
                $depatureFlight->set_connections($stops);
                $depatureFlight->set_price(number_format($fullPrice, 2, '.', ''));

                // setup whole trip
                $trip = new trip();
                $trip->set_departure($depatureFlight);
                $trip->set_fullPrice(number_format($depatureFlight->get_price(), 2, '.', ''));
                $trip->set_oneway(true);

                $data[] = $trip; 
            }
            return $data;
        }

        $returnTrips = array_filter($flights,function($item) use($to, $from){
            return strtoupper($item->departure_airport) == strtoupper($to) && strtoupper($item->arrival_airport) == strtoupper($from);
        });

        // var_dump("returnTrips",$returnTrips);die();

        foreach($depatureTrips as $depature){

            foreach($returnTrips as $return){

                $stops = 0;
                if(is_array($depature)){

                    $stops = count($depature)-1;
                    $fullPrice = 0;
                    foreach ($depature as $depart) {

                        $fullPrice += $depart->price;
                    }
                }else{
                    $fullPrice = $depature->price;
                }

                // set depature flight Flight
                $depatureFlight = new flight();
                $depatureFlight->set_date($departDate);
                $depatureFlight->set_flights($depature);
                $depatureFlight->set_connections($stops);
                $depatureFlight->set_price(number_format($fullPrice, 2, '.', ''));

                // set return flight Flight
                $returnFlight = new flight();
                $returnFlight->set_date($returnDate);
                $returnFlight->set_flights($return);
                $returnFlight->set_connections(0);
                $returnFlight->set_price($return->price);
                
                // setup whole trip
                $trip = new trip();
                $trip->set_departure($depatureFlight);
                $trip->set_return($returnFlight);
                $trip->set_fullPrice($depatureFlight->get_price() + $returnFlight->get_price());
                $trip->set_oneway(false);
                
                $data[] = $trip;
            }

        }

        return $data;
    }

    public function getConnections($flights, $from, $flight, $stops)
    {
        $this->connections[] = $flight;

        $landingFlights = array_filter($flights,function($item) use($flight, $from){
            return strtoupper($item->arrival_airport) == strtoupper($flight->departure_airport);
        });

        if(!$landingFlights){
            $this->connections= []; 
            $this->stops = 0;
        }

        if(isset($stops) && $this->stops > $stops){
            
            $this->connections= []; 
            return;
        }
        
        foreach ($landingFlights as $key => $value) {

            if(strtoupper($value->departure_airport) == strtoupper($from)){

                $this->connections[] = $value;
                return;
            }else{
                $this->stops += 1;
                $this->getConnections($flights, $from, $value, $stops );
            }
        }
        return;
    }

}