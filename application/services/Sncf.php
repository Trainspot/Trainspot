<?php /* todo:
- autocompletion gares -> data
- correspondances/ou pas entre deux gares                             |
- correspondances/ou pas entre plusieurs gares (chemin possible ?)    |-> microservices

- départs de trains (sur un trajet) dans une tranche horaire | jours/semaine (dates?) -> microservices
- ajouter la durée du trajet à la tranche horaire (déterminer la durée avec correspondances) -> microservices
- rencontre possible entre deux tranches horaires -> ? 
*/


class Sncf
{
	private function getGares()
	{
		$cache = Zend_Registry::get('cache');
        $cache_id = 'gares__eq';
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
					$tmp2[] = array($d[1], $d[3], $d[9], $d[10], $d[0]);
				}
			}
			$data = $tmp2;
            $cache->save($data, $cache_id, array('gare'));
        }
        return $data;
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
		return $gares;
	}

	public function autocompleteGare($name) {
		$gares = $this->prepareAutocompletion($name);
		$data = array();
		foreach ($gares as &$gare)
		{
			$data[] = $gare[0];
		}

	}
}