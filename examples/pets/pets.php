<?php
/*
 * pets.php
 * In this example, we use the Map/Reduce algorithm to generate a single result.
 * 
 * Given a list of pets and associated properties, we will extract:
 *  - number of animals
 *  - average age
 *  - total number of visits
 *  - average number of visits
 *  - total revenue
 *  - average revenue per animal
 *  - average revenue per visit
 */

define('EXAMPLE_DIR', dirname(__FILE__) . '/');
define('SRC_DIR', EXAMPLE_DIR . '../../src/');

require_once SRC_DIR . 'MapReduce.php';

$pets1 = [
	  [ 'name' => 'Bono',  'species' => 'dog',     'birthday' => '2010-01-01', 'visits' => '3', 'revenue' =>  98.00 ]
	, [ 'name' => 'Lenny', 'species' => 'cat',     'birthday' => '2005-02-12', 'visits' => '2', 'revenue' => 128.00 ]
	, [ 'name' => 'Bruce', 'species' => 'dog',     'birthday' => '2008-03-31', 'visits' => '3', 'revenue' => 155.00 ]
	, [ 'name' => 'Sting', 'species' => 'turtle',  'birthday' => '2010-04-06', 'visits' => '2', 'revenue' =>  58.00 ]
	, [ 'name' => 'Jay',   'species' => 'papagay', 'birthday' => '2012-05-16', 'visits' => '1', 'revenue' =>  19.00 ]
];
$pets2 = [
	  [ 'name' => 'Steve', 'species' => 'cat',     'birthday' => '2005-06-22', 'visits' => '3', 'revenue' =>  68.00 ]
	, [ 'name' => 'Mike',  'species' => 'dog',     'birthday' => '2008-07-21', 'visits' => '2', 'revenue' =>  55.00 ]
	, [ 'name' => 'Ben',   'species' => 'dog',     'birthday' => '2009-08-16', 'visits' => '2', 'revenue' =>  71.00 ]
	, [ 'name' => 'Miles', 'species' => 'cat',     'birthday' => '2011-09-14', 'visits' => '4', 'revenue' => 346.00 ]
	, [ 'name' => 'Jack',  'species' => 'dog',     'birthday' => '2009-10-03', 'visits' => '6', 'revenue' => 244.00 ]
];

$mapper = function ($pet) {
	return [
		  'species'            => $pet['species']
		, 'animals'            => 1
		, 'avg_age'            => (@time('Europe/Vienna') - strtotime($pet['birthday'])) / 60 / 60 / 24 / 365.25
		, 'total_visits'       => $pet['visits']
		, 'avg_visits'         => $pet['visits']
		, 'total_revenue'      => $pet['revenue']
		, 'avg_revenue_animal' => $pet['revenue']
		, 'avg_revenue_visit'  => $pet['revenue'] / $pet['visits']
	];
};

date_default_timezone_set ('Europe/Vienna');
$reducer = function ($new_data, $carry) {
	if ( is_null($carry) ) {
		return $new_data;
	}
	
	$species            = $carry['species'];
	$animals            = $carry['animals'] + 1;
	$avg_age            = ( $carry['avg_age'] * $carry['animals'] + $new_data['avg_age'] ) / $animals;
	$total_visits       = $carry['total_visits'] + $new_data['total_visits'];
	$avg_visits         = $total_visits / $animals;
	$total_revenue      = $carry['total_revenue'] + $new_data['total_revenue'];
	$avg_revenue_animal = $total_revenue / $animals;
	$avg_revenue_visit  = $total_revenue / $total_visits;
	
	return compact('species', 'animals', 'avg_age', 'total_visits', 'avg_visits', 'total_revenue', 'avg_revenue_animal', 'avg_revenue_visit');
};

class LogToConsole {
	public function send ($data) {
		if ( !is_null($data) ) {
			print_r($data);
		} else {
			echo "Finished!\n";
		}
	}
}

$output = new LogToConsole();

echo "Getting global data:\n";
$mapreducer = new MapReduce([$pets1, $pets2], $mapper, $reducer, $output);
$mapreducer->run();
echo "\n";

echo "Getting grouped data:\n";
$mapreducer = new MapReduce([$pets1, $pets2], $mapper, $reducer, $output, ['group_by' => 'species']);
$mapreducer->run();
echo "\n";
