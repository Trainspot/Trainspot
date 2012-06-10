<?php /* todo:
ok - autocompletion gares 													-> data

later - correspondances/ou pas entre deux gares                             |
later - correspondances/ou pas entre plusieurs gares (chemin possible ?)    |-> microservices

ok - départs de trains (sur un trajet) dans une tranche horaire | jours/semaine (dates?)      |
- ajouter la durée du trajet à la tranche horaire (déterminer la durée avec correspondances)  |-> microservices
- rencontre possible entre deux tranches horaires                                             |  
*/


class Sncf
{
	private $_timespan = 900;

	// returns [name, uic, lat, lng, ligne]
	private function getGares()
	{
		$cache = Zend_Registry::get('cache');
        $cache_id = 'gares_eq';
        if ( ! ($data = $cache->load($cache_id)))
        {
            require_once APPLICATION_PATH . '/../library/Webshell.php';
			$webshell = Webshell::getInstance();
			$res = Zend_Json::decode($webshell->exec('@sncf data entrees_sorties_lignes_C_et_L'));
			$data = $res[0]['data'];
			array_shift($data);
			$tmp = array();
			$tmp2 = array();
			foreach ($data as $d)
			{
				if ( isset($d[1], $d[3], $d[9], $d[10], $d[0]) && ! in_array($d[1], $tmp))
				{
					$tmp[] = $d[1];
					$tmp2[] = array($d[1], 'DUA' . substr($d[3], 0, strlen($d[3]) - 1), $d[9], $d[10], $d[0]);
				}
			}
			$data = $tmp2;
            $cache->save($data, $cache_id, array('gare'));
        }
        return $data;
	}

	public function hasCorrespondance($start, $end)
	{
		return false; // /!\ ignore correspondances for now
/*
		$cache = Zend_Registry::get('cache');
        $cache_id = 'gares__correspondance' . rand();
        if ( ! ($data = $cache->load($cache_id)))
        {
            require_once APPLICATION_PATH . '/../library/Webshell.php';
			$webshell = Webshell::getInstance();
			$res = Zend_Json::decode($webshell->exec('@sncf linelist -Action LineList -StopAreaExternalCode "' . $start . ';' . $end . '|and"'));
			$data = $res[0]['data'];
var_dump('pl');
			var_dump($data);die();

            $cache->save($data, $cache_id, array('gare'));
        }
        return $data;*/
	}

	public function prepareAutocomplete($name)
	{
		$namelen = strlen($name);
		if ($namelen < 1)
			return array();

		$gares = $this->getGares();

		$gares = array_filter($gares, function($gare) use($name, $namelen) {
			return strtolower(substr($gare[0], 0, $namelen)) == strtolower($name);
		});

		return array_values($gares);
	}

	// gare & gare2: stoparea uic; returns vehiclejourneylist
	public function searchTrains($gare, $gare2, $timestamp, $ponctual = false)
	{
		$cache = Zend_Registry::get('cache');
        $cache_id = 'sncf_searchtrain_' . $timestamp . '_' . $gare . $gare2 . $ponctual;
        if ( ! ($data = $cache->load($cache_id)))
        {
        	$timestart = date('H|i', $timestamp - $this->_timespan);
        	$timeend = date('H|i', $timestamp + $this->_timespan);

        	$extra = "";
        	if ($ponctual)
        	{
        		$datestart = date('Y|m|d', $timestamp);
        		$extra = " -Date '" . $datestart . "'";
        	}

            require_once APPLICATION_PATH . '/../library/Webshell.php';
			$webshell = Webshell::getInstance();
			$res = Zend_Json::decode($webshell->exec('@sncf vehiclejourneylist -Action VehicleJourneyList -StopAreaExternalCode "' . $gare2 . ';' . $gare . '|and" -StartTime "' . $timestart . '" -EndTime "' . $timeend . '"' . $extra));
			$data = $res[0]['data']['ActionVehicleJourneyList']['VehicleJourneyList'];

            $cache->save($data, $cache_id, array('gare'));
        }
        return $data;
	}

	// travel: vehiclejourney, from & to: stoparea uic
	public function searchDepartArrival($travel, $from, $to)
	{
		$stops = $travel['StopList']['Stop'];
		$result = array();
		foreach ($stops as $stop)
		{
			if ($stop['StopPoint']['StopArea']['@attributes']['StopAreaExternalCode'] == $from)
			{
				$timestop = $stop["StopArrivalTime"];
				$timestop = mktime($timestop['Hour'], $timestop['Minute']);
				$result['arrival'] = $timestop;
			}
			elseif ($stop['StopPoint']['StopArea']['@attributes']['StopAreaExternalCode'] == $to)
			{
				$timestop = $stop["StopArrivalTime"];
				$timestop = mktime($timestop['Hour'], $timestop['Minute']);
				$result['depart'] = $timestop;
			}
		}
		return $result;
	}

	// travel: vehiclejourney, from: stoparea uic
	public function searchTravel($travel, $from, $timestamp)
	{
		$stops = $travel['StopList']['Stop'];
		foreach ($stops as $stop)
		{
			if ($stop['StopPoint']['StopArea']['@attributes']['StopAreaExternalCode'] == $from)
			{
				$timestop = $stop["StopArrivalTime"];
				$timestop = mktime($timestop['Hour'], $timestop['Minute']);
				$difftime = abs($timestamp - $timestop);
				if ($difftime < 60 * 10)
					return true;
			}
		}
		return false;
	}

	public function searchTravels($travels, $from, $timestamp)
	{
		$results = array();
		foreach ($travels as $travel)
			$results[] = $this->searchTravel($travel, $from, $timestamp);
		return $results;
	}

	public function autocompleteGare($name) {
		$gares = $this->prepareAutocomplete($name);
		$data = array();
		foreach ($gares as $gare)
		{
			$data[] = $gare[0];
		}
		return $data;
	}
}