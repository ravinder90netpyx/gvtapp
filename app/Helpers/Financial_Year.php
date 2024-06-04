<?php
/**
 * 
 */
namespace App\Helpers;

class Finamcial_Year extends AnotherClass {
	function getfinancialyears($startdate=null, $enddate=null){
		if(empty($startdate)) $startdate='2020-04-01';
		$startYear = date('Y', strtotime($startdate));
		if(date('n', strtotime($startdate))<4) $startYear = $startYear-1;
		
		if(empty($enddate)) $enddate=date('Y-m-d');
	    $endYear = date('Y', strtotime($enddate));
		if(date('n', strtotime($enddate))>3) $endYear = $endYear+1;

	    $financeYears = [];
	    while($startYear < $endYear) array_push($financeYears, $startYear . "-" . (++$startYear));
	    
	    return $financeYears;
	}
}