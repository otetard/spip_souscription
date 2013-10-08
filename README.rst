==========================================
Souscription : dons et adhésions avec SPIP
==========================================

Présentation du *plugin*
========================

Souscription est un *plugin* permettant de mettre en place, sur un
site fonctionnant sous `SPIP <http://www.spip.net/>`, des campagnes
d'adhésions ou de dons. Le paiement est délégué au *plugin* `Bank
<https://github.com/nursit/bank>`, qui prend en charge différentes
plateforme de paiement en ligne (chèque, PayBox, PayPal, virement,
etc.).

Ce *plugin* a été développé pour les besoins d'`Attac France
<http://www.france.attac.org/>` et de `Basta !
<http://www.bastamag.net>`.

Installation
============

Le *plugin* étant encore largement en développement (bien qu'utilisé
en production par Attac France), il n'est pas installable directement
depuis l'interface de gestion des *plugins* de SPIP.

Pour installer le *plugin*, il faut récupérer l'archive de la
`dernière version
<https://github.com/otetard/spip_souscription/releases>` publiée sur
GitHub ou dupliquer le dépôt Git :

  $ git clone https://github.com/otetard/spip_souscription souscription

Il faut aussi récupérer le *plugin* `Bank
<https://github.com/nursit/bank>`, hébergé lui aussi sur GitHub :

  $ git clone https://github.com/nursit/bank

Les deux répertoires ``souscription/`` et ``bank/`` doivent ensuite
être placés dans votre répertoire ``plugins/`` de votre installation
SPIP.

Enfin, vous pouvez activer le *plugin* depuis l'interface de gestion
des *plugins* de SPIP, ce qui permettra de télécharger les autres
dépendances automatiquement.

Configuration
=============

Il est possible de configurer le *plugin* (définition des montants
proposés pour l'adhésion et/ou le dons). Pour cela, il faut se rendre
dans le menu « Configuration » puis « Souscriptions ».

Première utilisation
====================

La première étape pour utiliser le *plugin* souscription est de mettre
en place une campagne, d'adhésions ou de dons. Une campagne peut avoir
des objectifs et il est éventuellement possible de définir des
montants spécifiques.

Pour afficher le formulaire d'adhésion, il faut ensuite ajouter le
code suivant dans un article (en adaptant le numéro de la campagne) :

  <souscription|campagne=1>
