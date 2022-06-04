# Secret santa

Un projet symfony qui permet de gérer un secret santa.  

* L'app tire au sort un utilisateur pour chaque utilisateur.
* Les utilisateurs peuvent avoir une liste d'utilisateurs interdits (qu'ils ne peuvent pas tirer).
* Un utilisateur ne peut être tiré qu'une seule fois.
* Tous les utilisateurs doivent être tirés.
* Les utilisateurs doivent se connecter pour accéder à l'application.
* Un utilisateur ne voit que l'utilisateur qu'il a tiré.
* Les utilisateurs peuvent ajouter des éléments dans leur liste de souhaits.
* Les listes de souhaits sont visibles par tous les utilisateurs.
* Les utilisateurs peuvent suggérer des éléments dans la liste de souhaits des autres utilisateurs. Dans ce cas, ces éléments sont affichés différements.
* Les utilisateurs peuvent marquer comme "achetés" des éléments de la liste de souhaits des autres utilisateurs.
* Les utilisateurs ne voient pas les éléments marqués "achetés" de leur liste de souhaits.

## TODO

[ ] Faire le diagramme de classe  
[ ] Initialiser le projet symony  
[ ] Paramétrer la connection à la base de données  
[ ] Créer les entités  
[ ] Faire les tests unitaires  
[ ] Faire la fonction de tirage au sort  
[ ] Faire les vues




Les ports exposés sont le port 9000 pour le serveur et le port 9080 pour phpmyadmin