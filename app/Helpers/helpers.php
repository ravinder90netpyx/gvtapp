<?php
namespace App\Helpers;

use Carbon\CarbonPeriod;
use Carbon\Carbon;

class helpers{
	function get_financial_years($startdate=null, $enddate=null){
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
	// format YYYY-MM
	function get_financial_month_year($from_date, $to_date, $format = 'Y-m'){
		$m_arr=[];
		foreach (CarbonPeriod::create(Carbon::parse($from_date), '1 month', Carbon::parse($to_date)) as $month) {
            $m_arr[] = $month->format($format);
        }
		return $m_arr;
	}

	function get_api_key($key,$param){
		$data = '';
		switch ($key) {
			case 'whatsapp':
				if($param == 'apikey'){
					$data = '4ssd1jldzf7mhiprkmwt5iwff6iuafqv';
				}
				break;
			
			default:
				$data = "Not Found";
				break;
		}
		return $data;
	}
}