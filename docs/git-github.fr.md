# Git - Github

Lorsqu'on travaille à plusieurs, on a besoin d'être sûr que toutes les parties
prenantes soient d'accord avant de fusionner son travail avec le projet.

Pour cela, on dispose de `Git` et `Github`.

Nous avons un workflow standard, que vous pouvez retrouver dans les guides 
github ([lien pour y accéder](https://guides.github.com/introduction/flow/)).

Quelques conseils supplémentaires :

- créer une `branche` par feature/page.
- faire des `Pull Request` fréquentes et courtes, afin de ne pas accumuler les 
problèmes à corriger.
- une fois qu'une `Pull Request` est en `review`, n'ajoutez à la branche que 
les correctifs demandés par les `reviewers` (Wizaplace). Pour tout autre ajout,
créez une nouvelle `branche`.

Au cas où, plusieurs git cheatsheet existent, comme par exemple [celle-ci](http://ndpsoftware.com/git-cheatsheet.html#loc=local_repo;)

## Example :

##### 1 - Je suis sur la branche `master` et je vais faire une nouvelle feature/page:

```
$ git checkout -b <nom_de_la_branche>
```

##### 2 - Je fais mes ajouts/modifications/suppressions, puis, régulièrement, je commit:
```
$ git add <nom_du_fichier>
```
ou 
```
$ git add *
```
pour ajouter tous les fichiers modifiés. ( attention, ce sont tous les fichiers **visibles**, pour englober les fichiers cachés, remplacer `*` par `.`)

puis :
```
$ git commit -m <message_du_commit>
```

##### 3 - Une fois que j'ai terminé, ou dès que j'ai besoin d'une review (pour valider un point par exemple):
```
$ git push origin <nom_de_la_branche>
```

##### 4 - Je peux ensuite revenir sur master avant de recommencer la boucle
```
$ git checkout master
```

puis je repasse à l'étape 1
