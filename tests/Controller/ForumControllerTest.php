<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Subject;
use App\Repository\UserRepository;

// Classe réalisant des tests fonctionnels. On rajoute toujours test au nom de la classe
// Les test fonctionnels testent le bon fonctionnement de l'application, ils sont donc réalisés principalement sur les contrôleurs
// La classe pour les tests fonctionnels est WebTestCase
class ForumControllerTest extends WebTestCase {

  /**
   * @dataProvider providePublicRoutes
   */
  // Vérifie que toute les routes publics sont accessible
  public function testPublicRoutes($url) {
    // On crée un client c'est dire un objet qui simule le navigateur de l'utilisateur
    $client = static::createClient();
    // Le client lance une requête vers une url du tableau
    $client->request('GET', $url);
    // Quand une route est accessible le serveur doit renvoyer le code 200
    $this->assertEquals(200, $client->getResponse()->getStatusCode());
  }

  // Provider qui fournit les route de l'application ne nécessitant pas de login
  // ["url à visiter"]
  public function providePublicRoutes() {
    return [
      ["/login"],
      ["/register"]
    ];
  }

  /**
   * @dataProvider providePrivateRoutes
   */
  // Vérifie que les routes provées redirigent l'utilisateur nnon connecté
  public function testPrivateRoutesRedirect($url) {
    $client = static::createClient();
    $client->request('GET', $url);
    // Le code serveur pour une redirection est le code 302
    $this->assertEquals(302, $client->getResponse()->getStatusCode());
  }

  /**
   * @dataProvider providePrivateRoutes
   */
  // vérifie qu'un utilisateur connecté a accès aux routes privées
  public function testPrivateRoutesAccess($url) {
    $client = static::createClient();
    // Procédure simplifiée pour connecter un utilisteur lors d'un test
    // On récupére le repository de l'entité User via le container de service car nous ne sommes pas dans le contrôleur
    $userRepository = static::$container->get(UserRepository::class);
    // On récupère l'utilisateur simplement par son nom
    $testUser = $userRepository->findOneBy([
      "username" => 'totopro'
    ]);
    // On le connecte
    $client->loginUser($testUser);

    $client->request('GET', $url);
    // On vérifie que les pages sont accessibles
    $this->assertEquals(200, $client->getResponse()->getStatusCode());
  }

  // Dataprovider qui fournit certainnes des routes reservées aux utilisateurs connectés
  public function providePrivateRoutes() {
    return [
      ["/"],
      ["/subjects"],
      ["/rules"]
    ];
  }

  // Le bon fonctionne de l'affichage des sujets sur la page d'accueil
  // vérifie également que lorsqu'on clique sur un sujet on arrive bien sur sa page
  public function testSubjects() {
    $client = static::createClient();
    // On connecte l'utilsiateur pour accéder à la route
    $userRepository = static::$container->get(UserRepository::class);
    $testUser = $userRepository->findOneBy([
      "username" => 'totopro'
    ]);
    $client->loginUser($testUser);
    // On requête la route, la requête renvoie une instance de la classe WebCrawler pour la page demandée
    // Le crawler permet de parcourir une page HTML via les balise et le css (un peu comme queryselector en JS)
    $crawler = $client->request('GET', "/subjects");
    // Ici on compte le nombre d'élements de classe card
    $cards = $crawler->filter(".card")->count();
    // On vérifie que ce nombre correspond au nombre de sujets en BDD
    // Attention il faut normalement créer une BDD spécifiques pour les tests afin de contrôler via les fixtures le nombre d'éléments contenus et donc affichés
    $this->assertEquals(2, $cards);
    // On récupère le contenu texte du h5 de classe card-title en position 0, autrement-dit le titre du 1er sujet
    $title = $crawler->filter("h5.card-title")->eq(0)->text();
    // On clique sur le bouton voir du premier sujet
    $client->clickLink('Voir');
    // Une fois sur la page de détail on vérifie que le contenu du h2 est le même que celui de l'article sur lequel on a cliqué
    $this->assertSelectorTextContains('html h2', $title);
  }
}
