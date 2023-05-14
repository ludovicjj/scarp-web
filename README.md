* //tagname : sélectionne tous les éléments qui correspondent au nom de balise tagname.

* //tagname[@attribut='valeur'] : sélectionne tous les éléments qui ont un attribut attribut avec la valeur valeur.
* //tagname[contains(@attribut, 'valeur')] : sélectionne tous les éléments qui ont un attribut attribut contenant la chaîne valeur.
* //tagname[starts-with(@attribut, 'valeur')] : sélectionne tous les éléments qui ont un attribut attribut commençant par la chaîne valeur.
* //tagname[position()=n] : sélectionne l'élément tagname à la position n.
* //tagname[last()] : sélectionne le dernier élément tagname.
* //tagname[@class='valeur'] : sélectionne tous les éléments qui ont la classe valeur.
* //tagname[@id='valeur'] : sélectionne tous les éléments qui ont l'identifiant valeur.
* //tagname[text()='valeur'] : sélectionne tous les éléments tagname contenant le texte exact valeur.
* //tagname[contains(text(),'valeur')] : sélectionne tous les éléments tagname contenant la chaîne de caractères valeur.
* //tagname[contains(text(),'valeur1') or contains(text(),'valeur2')] : sélectionne tous les éléments tagname contenant la chaîne de caractères valeur1 OU valeur2.

* Le sélecteur / est utilisé pour sélectionner un élément enfant direct d'un élément donné. Par exemple, l'expression div/p sélectionne tous les éléments p qui sont des enfants directs d'un élément div.

* Le sélecteur // est utilisé pour sélectionner tous les éléments correspondant à un chemin d'accès relatif, quel que soit leur emplacement dans le document. Par exemple, l'expression //p sélectionne tous les éléments p dans le document, qu'ils soient enfants directs d'un élément donné ou non.

* "parent::" est un axe qui permet de sélectionner l'élément parent direct d'un élément donné, tandis que "ancestor::" est un axe qui permet de sélectionner tous les ancêtres d'un élément donné, en remontant dans la hiérarchie du DOM jusqu'à l'élément racine (c'est-à-dire le nœud <html>).

* Ainsi, "parent::" ne sélectionne que l'élément parent direct, tandis que "ancestor::" sélectionne tous les ancêtres de l'élément, y compris les parents directs, les parents des parents, et ainsi de suite, jusqu'à l'élément racine.