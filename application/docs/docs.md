Trainspot API
=============

L'API de Trainspot permet de récupérer les informations de votre compte, vos trajets ou rencontres possibles que propose l'application. Elle fonctionne en mode REST.

Authentification
----------------

Vous pouvez vous authentifier en ajoutant un paramètre GET nommé "__token__" à chaque appel de l'API, qui vous est fourni sur la [page développeur du site](http://trainspot.fr/developers).

Il est conseillé d'ajouter aussi un paramètre optionnel GET nommé "__v__" qui contient le numéro de version. La version actuelle est "__1__" et sera incrémenté dans les prochaines mises à jour.


Vos informations
----------------

Renvoie les informations de votre compte.

	GET: /api/self


Vos trajets
-----------

Renvoie la liste de vos trajets habituels ou ponctuels.

	GET: /api/trajet


Vos interets
------------

Renvoie la liste de vos interets.

	GET: /api/interet

Parametres GET:
 - _mode_: Peut avoir les valeurs "demande" et "propose", selon les centres d'interets voulus.


Vos rencontres possibles
------------------------

Renvoie la liste de vos rencontres possibles avec les autres utilisateurs.

	GET: /api/matching

