<?php
// Attention à recréer dans le dossier test l'architecture du dossier src
namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Subject;

// Classe réalisant des tests Unitaire. On rajoute toujours test au nom de la classe
// Les test unitaire testent des fonctions, de petits morceaux de codes et comparent les données de sortie avec les données attendues
// La classe pour les tests unitaires et TestCase
class SubjectTest extends TestCase {

  // Méthode de la classe réalise un test extrêment simple
  public function testExemple() {
    // A la fin d'un test il faut réaliser une assertion, cad vérifier que quelquechose est vrai
    // Ici on vérifie que 42 égal 42
    $this->assertEquals(42, 42);
  }

  /**
   * @dataProvider provideMakeUpperCases
   */
  // Teste la méthode makeUpper de la classe Subject
  // Cette fonction utilise un dataprovider (voir annotation au dessus)
  // Un data provider est une fonction qui fourni des données à une fonction de test
  public function testMakeUpper($input, $output) {
    $subject = new Subject();
    // On garde le résultat de la fonction sur l'input (élément en position 0 dans les tableaux du dataprovider)
    $result = $subject->makeUpper($input);
    // On vérifie que le retour de la fonction est égal résultat attendu
    $this->assertEquals($result, $output);
  }

  // Fonction faisant office de dataprovider
  // On appelera la fonction testMakeUpper autant de fois qu'il y'a de sous tableaux
  // A chaque appelle on passe en argument les deux valeurs du tableau, c'est pourquoi testMakeUpper attends deux paramètres $input et $output
  // ["valeur d'entrée à la fonction", "valeur de retour attendue"]
  public function provideMakeUpperCases() {
    return [
      ["test", "#TEST#"],
      ["lorem ipsum", "#LOREM IPSUM#"],
      ["Lorem Ipsum", "#LOREM IPSUM#"],
      [25, false],
      [[], false]
    ];
  }
}
