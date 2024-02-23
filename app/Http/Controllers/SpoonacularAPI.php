<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SpoonacularAPI extends Controller
{
    public function get_recipies(Request $request) {
        $data = $request->all();

        ini_set('display_errors', 'On');
        error_reporting(E_ALL);
    
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Origin: *');

        $executionStartTime = microtime(true);



	    $url= 'https://api.spoonacular.com/recipes/findByIngredients?apiKey=' . env('SPOONACULAR_API_KEY') . '&number=10&ranking=1&ingredients=' . join(',', $data);

        // create & initialize a curl session
        $curl = curl_init();

        // set our url with curl_setopt()
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);

        $result = curl_exec($curl);

        curl_close($curl);
        
        echo $result; 
    }
}
