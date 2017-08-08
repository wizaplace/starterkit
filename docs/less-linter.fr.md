Voici les règles appliquées : 

- pas d'hexadécimal invalide pour les couleurs
```less
    // invalide : 
    a { color: #00; }
    a { color: #fff1az; }
    a { color: #12345aa; }

    // valide :
    a { color: #000; }
    a { color: #000f; }
    a { color: #fff1a0; }
    a { color: #123450aa; }
```
- l'opérateur de la fonction `calc` doit avoir un espace de chaque côté
```less
    // invalide : 
    a { top: calc(1px+2px); }
    a { top: calc(1px+ 2px); }

    // valide :
    a { top: calc(1px + 2px); }
    a { top: calc(calc(1em * 2) / 3); }
    a {
        top: calc(var(--foo) +
            var(--bar));
    }
    a {
        top: calc(var(--foo)
            + var(--bar));
    }
```
- pas d'unité inconnue
```less
    // invalide : 
    a {
        width: 10pixels;
    }
    a {
        width: calc(10px + 10pixels);
    }

    // valide :
    a {
        width: 10px;
    }  
    a {
        width: 10Px;
    }  
    a {
        width: 10pX;
    }  
    a {
        width: calc(10px + 10px);
    }
```
- pas de propriété inconnue
```less
    // invalide : 
    a {
        colr: blue;
    }
    a {
        my-property: 1;
    }

    // valide :
    a {
        color: green;
    }
    a {
        fill: black;
    }
    a {
        -moz-align-self: center;
    }
    a {
        -webkit-align-self: center;
    }
    a {
        align-self: center;
    }
```
- pas d'utilisation d' `!important` dans les `@keyframes` ([ignoré par certains navigateurs](https://developer.mozilla.org/en-US/docs/Web/CSS/@keyframes#!important_in_a_keyframe))
```less
    // invalide : 
    @keyframes important1 {
        from {
            margin-top: 50px;
        }
        to {
            margin-top: 100px !important;
        }
    }
    @keyframes important1 {
        from {
            margin-top: 50px;
        }
        to {
            margin-top: 100px!important;
        }
    }
    @keyframes important1 {
        from {
            margin-top: 50px;
        }
        to {
            margin-top: 100px ! important;
        }
    }

    // valide :
    a { color: pink !important; }
    @keyframes important1 {
        from {
            margin-top: 50px;
        }
        to {
            margin-top: 100px;
        }
    }
```
- pas de duplication de propriétés
```less
    // invalide : 
    a { color: pink; color: orange; }
    a { color: pink; background: orange; color: orange }

    // valide :
    a { color: pink; }
    a { color: pink; background: orange; }
```
- pas de bloc vide
```less
    // invalide : 
    a {}
    a { }
    @media print { a {} }

    // valide :
    a { color: pink; }
    @media print { a { color: pink; } }
```
- pas de sélecteur inconnu
```less
    // invalide : 
    a:unknown {}
    a:UNKNOWN {}
    a:hoverr {}

    // valide :
    a:hover {}
    a:focus {}
    :not(p) {}
    input:-moz-placeholder {}}
```
- pas d'élément inconnu
```less
    // invalide : 
    a::pseudo {}
    a::PSEUDO {}
    a::element {}

    // valide :
    a::before {}
    ::selection {}
    input::-moz-placeholder {}
```
- pas plus d'une propriété par ligne
```less
    // invalide : 
    a { color: pink; top: 3px; }
    a,
    b { color: pink; top: 3px; }

    // valide :
    a { color: pink; }
    a,
    b { color: pink; }
    a {
        color: pink;
        top: 3px;
    }
```
- nom des classes et des id en lowercase et hyphens: [regex à valider ?](https://regex101.com/r/SQJwfy/7)
```less
    // invalide : 
    .Foo{}
    #foo_bar {}
    .foo-BAR {}

    // valide :
    #foo {}
    .foo-bar {}
```
- pas de saut de ligne entre les sélecteurs
```less
    // invalide : 
    a
    
    b {
        color: red;
    }
    a,
    
    b {
        color: red;
    }
    a
    
    >
    b {
        color: red;
    }
    a
    >
    
    b {
        color: red;
    }

    // valide :
    a b {
        color: red;
    }
    a
    b {
        color: red;
    }
    a,
    b {
        color: red;
    }
    a > b {
        color: red;
    }
    a
    >
    b {
        color: red;
    }
```
- pas d'espace avant une virgule, toujours un espace après une virgule
```less
    // invalide : 
    a { transform: translate(1 ,1) }
    a { transform: translate(1 , 1) }

    // valide :
    a { transform: translate(1, 1) }
```
- toujours une nouvelle ligne à l'intérieur des parenthèses en multi-ligne
```less
    // invalide : 
    a { transform: translate(1,
        1) }

    // valide :
    a { transform: translate(1, 1) }
    a { transform: translate( 1, 1 ) }
    a {
        transform: translate(
            1, 1
        );
    }
    a {
        transform: translate(
            1,
            1
        );
    }
```
- pas d'espace avant ou après une parenthèse
```less
    // invalide : 
    a { transform: translate( 1, 1 ); }
    a { transform: translate(1, 1 ); }

    // valide :
    a { transform: translate(1, 1); }
```
- toujours un retour à la ligne après une virgule en multiligne, jamais avant
```less
    // invalide : 
    a { background-size: 0
        , 0; }

    // valide :
    a { background-size: 0, 0; }
    a { background-size: 0,
        0; }
```
- toujours un espace après les deux points `:`, jamais avant
```less
    // invalide : 
    a { color :pink }
    a { color : pink }
    a { color:pink }

    // valide :
    a { color: pink }
```
- toujours une nouvelle ligne après un point-virgule `;`, jamais avant
```less
    // invalide : 
    a { color: pink; top: 0; }
    a {
        color: pink; /* end-of-line comment
        containing a newline */
        top: 0;
    }

    // valide :
    a {
        color: pink;
        top: 0;
    }
    a {
        color: pink; /* end-of-line comment */
        top: 0;
    }
```
- jamais d'espace avant un point-virgule `;`, un espace après en `single-line`
```less
    // invalide : 
    a { color: pink;}

    // valide :
    a { color: pink; }
```
- une accolade fermante `}` est toujours à la ligne en multi-ligne
```less
    // invalide : 
    a { 
        color: pink;
        font-weight: bold; }

    // valide :
    a { 
        color: pink;
        font-weight: bold; 
    }
```
- il y a toujours une nouvelle ligne après une accolade fermante `}`
```less
    // invalide : 
    a { color: pink; }b { color: red; }
    a { color: pink;
    } b { color: red; }

    // valide :
    a { color: pink; }
    b { color: red; }
```
- toujours un espace avant une accolade ouvrante `{`
```less
    // invalide : 
    a{ color: pink; }
    a
    { color: pink; }

    // valide :
    a { color: pink; }
    a { 
        color: pink; 
    }
```
- toujours un espace après une accolade ouvrante `{` en `single-line`
```less
    // invalide : 
    a {color: pink; }

    // valide :
    a { color: pink; }
```
- toujours une nouvelle ligne après une accolade ouvrante `{` en multi-ligne
```less
    // invalide : 
    a { color: pink; 
        font-weight: bold;
    }

    // valide :
    a {  
        color: pink;
        font-weight: bold;
    }
```
- toujours l'utilisation des `::` pour les sélecteurs
```less
    // invalide : 
    a:before { color: pink; }
    a:after { color: pink; }
    a:first-letter { color: pink; }
    a:first-line { color: pink; }

    // valide :
    a::before { color: pink; }
    a::after { color: pink; }
    a::first-letter { color: pink; }
    a::first-line { color: pink; }
    input::placeholder { color: pink; }
    li::marker { font-variant-numeric: tabular-nums; }
```
- indentation de 4 espaces
```less
    // invalide : 
    a {
      color: pink;
    }

    // valide :
    a {
        color: pink;
    }
```
- pas plus d'une ligne vide à la suite
```less
    // invalide : 
    a::before { color: pink; }


    a::after { color: pink; }

    // valide :
    a::before { color: pink; }

    a::after { color: pink; }
```
- pas d'espaces à la fin d'une ligne
- toujours une ligne vide à la fin d'un fichier
