## La recherche


### Recherche rapide

La recherche rapide est typiquement présente dans le header de la marketplace et permet de chercher un produit par son nom ou sa marque.
Elle se compose d'un champ de recherche, et optionnellement d'un filtre par catégorie, et/ou d'un filtre de géolocalisation.
Du point de vue du template, la recherche correspond à un formulaire à soumettre à l'aide de la méthode `GET`.
L'attribut `action` cible le contrôleur `PHP` "SearchController", qui traitera la requête avant de rediriger l'utilisateur vers la page de résultats.


#### Les différents éléments

##### La barre de recherche

Il s'agit d'un champ `input` de type `text` dont la valeur de l'attribut `name` est "q" (pour "query") :
```html
<input type="text" name="q">
```

> A noter :
Il est déconseillé de changer la valeur de l'attribut `name` ("q") car elle est spécifiquement ciblée dans plusieurs parties du code, que ce soit au niveau de la bibliothèque de recherche javascript ou encore du contrôleur PHP.
Il est d'ailleurs, de manière générale, fortement déconseillé de modifier les valeurs ou noms des différents éléments configurés par défaut afin d'éviter les erreurs difficilement identifiables et de simplifier la maintenance future de la marketplace. 

##### Le filtre par catégorie

Il s'agit ici d'un champ de formulaire dont la valeur de l'attribut `name` est "selected_category_id" et qui contient l'`id` d'une catégorie ([voir exemple](#category-tree-example)).
La liste des catégories et de leurs `id`s respectifs est fournie par l'`API` via le `SDK` (`CatalogService::getCategoryTree`).
La liste des catégories est récupérée via une extension Twig pour limiter les appels dans chaque contrôleur.
Dans le Starter Kit, la liste des catégories est récupérable dans le template Twig via un appel à `categoryTree()`. 

Exemple du filtre par catégorie avec Twig :
```html
{% set categories = categoryTree() %}

<select name="selected_category_id">
    {% for category in categories %}
        <option value="{{ category.category.id }}">
            {{ category.category.name }}
        </option>
    {% endfor %}
</select>
```

##### La géolocalisation

La géolocalisation permet à l'utilisateur de filtrer les produits recherchés en fonction de leur distance par rapport à une position géographique, souvent celle de l'utilisateur lui-même.
Le centre du rayon de recherche peut être déterminé en entrant une adresse ou à l'aide de l'api de géolocalisation du navigateur web.

Starter Kit utilise les fonctionnalités de Google Maps [avec la bibliothèque `places`](https://developers.google.com/maps/documentation/javascript/places?hl=fr).
Pour pouvoir utiliser ce service , il faut créer un projet [Google Developer](https://console.developers.google.com/apis/dashboard) pour [pouvoir générer une `Google Maps API key`](https://support.google.com/googleapi/answer/6158857?hl=fr).
La clé API ira ensuite dans le fichier de layout principal afin d'être utilisable dans le code de tout le projet, par exemple :
```html
{% set googleMapsApiKey="AIzaSyBZTTRwiE94s-iJ7OVJAdl-linxm4LMAJE" %} {# demo key #}
<script src="https://maps.googleapis.com/maps/api/js?key={{ googleMapsApiKey }}&libraries=places" async defer></script>
```

#### Le traitement de la requête

##### URL

##### La route
