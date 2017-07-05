## La recherche


### Recherche rapide

La recherche rapide est typiquement présente dans le header de la marketplace et correspond à une recherche simple, c'est à dire qu'elle a un nombre d'options réduit.
Elle sert avant tout à réaliser une recherche textuelle sur un produit, par exemple via son nom ou sa marque, l'utilisateur pouvant par la suite affiner et filtrer les résultats obtenus.
Elle se compose principalement d'un champ de recherche de produit, souvent lié à un filtrage par catégorie, mais peut également être complétée par un filtre de géolocalisation.
Du point de vue du template HTML, la recherche prend la forme d'un formulaire qui doit être soumis à l'aide de la méthode `GET`.
La cible (l'attribut `action`) est le contrôleur PHP "SearchController", qui traitera la requête pour finalement rediriger l'utilisateur ves la page de résultats.


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
La liste des catégories et de leurs `id`s respectifs est fournie par l'API via le SDK (`CatalogService::getCategoryTree`).
La recherche rapide étant typiquement située dans le header de la marketplace, il est préférable d'utiliser une extension Twig. Cela évite à chaque contrôleur d'avoir à fournir la liste des catégories à sa vue.
Dans le Starter Kit, la liste des catégories est récupérable dans le template Twig via un appel à `categoryTree()`. 

Le plus simple est d'avoir un élément de type `select` dont les éléments `option` listent les différentes catégories.
<a name="category-tree-example"></a>Exemple avec Twig :
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

> A noter :
En réalité, on récupère ici l'arbre complet des catégories, c'est à dire les catégories de premier niveau mais également toutes les 'branches' descendantes : les sous-catégories et leurs propres enfants, etc.
Toutefois, pour la recherche rapide, il est préférable de n'afficher que les catégories de premier niveau ('catégories root') afin de conserver la simplicité d'utilisation (ce qui est le cas dans l'exemple ci-dessus). 
L'information n'est pas perdue pour autant, l'arbre complet étant utilisé dans le menu des catégories du `header`.

Un élément `select` étant difficile à styliser, il peut tout à fait être envisagé d'utiliser d'autres types d'éléments pour sélectionner une catégorie filtre.
Le seul réel impératif est ici que le contrôleur PHP ("SearchController") reçoive une requête nommée "selected_category_id" dont la valeur corresponde à l'`id` d'une catégorie.
Sur le projet Starter Kit par exemple, le choix de la catégorie se fait dans un menu déroulant personnalisé, géré par un objet `Vue` et passé dans un champ de type `hidden`.

##### La géolocalisation

La géolocalisation permet à l'utilisateur de filtrer les produits recherchés en fonction de leur distance par rapport à une position géographique, souvent celle de l'utilisateur lui-même.
Afin d'indiquer le centre du rayon de recherche, deux champs sont possibles : l'entrée textuelle d'une adresse ou la localisation automatique de l'utilisateur en fonction de son emplacement.
Starter Kit utilise les fonctionnalités de Google Maps [avec la bibliothèque `places`](https://developers.google.com/maps/documentation/javascript/places?hl=fr).

Pour pouvoir utiliser le service, il faut créer un projet [Google Developer](https://console.developers.google.com/apis/dashboard) pour [pouvoir générer une `Google Maps API key`](https://support.google.com/googleapi/answer/6158857?hl=fr).
La clé API pourra ensuite être collée dans le fichier de layout principal afin d'être utilisable dans le code de tout le projet :
```html
{% set googleMapsApiKey="AIzaSyBZTTRwiE94s-iJ7OVJAdl-linxm4LMAJE" %} {# demo key #}
<script src="https://maps.googleapis.com/maps/api/js?key={{ googleMapsApiKey }}&libraries=places" async defer></script>
```

> A noter :
Plutôt que de rentrer la clé directement dans le code, il est conseillé de créer [une variable globale Twig](https://symfony.com/doc/current/templating/global_variables.html).

#### Le traitement de la requête

##### URL

##### La route
