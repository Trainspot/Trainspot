<?php /* todo:
ok - autocompletion gares -> data

later - correspondances/ou pas entre deux gares                             |
later - correspondances/ou pas entre plusieurs gares (chemin possible ?)    |-> microservices

- départs de trains (sur un trajet) dans une tranche horaire | jours/semaine (dates?) -> microservices
- ajouter la durée du trajet à la tranche horaire (déterminer la durée avec correspondances) -> microservices
- rencontre possible entre deux tranches horaires -> ? 
*/


class Sncf
{
	private $_timespan = 600;

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

	public function searchTrains($gare, $gare2, $timestamp, $ponctual = false)
	{
		$cache = Zend_Registry::get('cache');
        $cache_id = 'sncf_searchtrain_' . $timestamp . '_' . $gare[1] . $gare2[1] . $ponctual;
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
			$res = Zend_Json::decode($webshell->exec('@sncf vehiclejourneylist -Action VehicleJourneyList -StopAreaExternalCode "' . $gare[1] . ';' . $gare2[1] . '|and" -StartTime "' . $timestart . '" -EndTime "' . $timeend . '"' . $extra));
			$data = $res[0]['data']['ActionVehicleJourneyList']['VehicleJourneyList'];

            $cache->save($data, $cache_id, array('gare'));
        }
        return $data;
	}

	public function searchTravel($travel, $from)
	{

	}

	public function searchTravels($travels, $from)
	{
		$results = array();
		foreach ($travels as $travel)
			$results[] = $this->searchTravel($travel, $from);
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