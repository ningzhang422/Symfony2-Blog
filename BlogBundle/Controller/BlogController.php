<?php
// src/Sdz/BlogBundle/Controller/BlogController.php
// Attention à bien ajouter ce use en début de contrôleur

namespace Sdz\BlogBundle\Controller;
 
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Httpfoundation\Response;
use Sdz\BlogBundle\Entity\Article;
use Sdz\BlogBundle\Entity\Image;
use Sdz\BlogBundle\Entity\Commentaire;
use Sdz\BlogBundle\Entity\ArticleCompetence;
class BlogController extends Controller
{
  public function indexAction($page)
  {
    // On ne sait pas combien de pages il y a
    // Mais on sait qu'une page doit être supérieure ou égale à 1
    if( $page < 1 )
    {
      // On déclenche une exception NotFoundHttpException
      // Cela va afficher la page d'erreur 404 (on pourra personnaliser cette page plus tard d'ailleurs)
      throw $this->createNotFoundException('Page inexistante (page = '.$page.')');
    }
	
	// On récupère le service
    $antispam = $this->container->get('sdz_blog.antispam');
    $text = "ning@fdsqf.com,ning@fdsqf.com,ning@fdsqf.com,";
    // Je pars du principe que $text contient le texte d'un message quelconque
    if ($antispam->isSpam($text)) {
      throw new \Exception('Votre message a été détecté comme spam !');
    }
 
    // Ici, on récupérera la liste des articles, puis on la passera au template
	// Dans l'action indexAction() :
	return $this->render('SdzBlogBundle:Blog:index.html.twig', array(
	  'articles' => array(
		array(
		  'titre'   => 'Mon weekend a Phi Phi Island !',
		  'id'      => 1,
		  'auteur'  => 'winzou',
		  'contenu' => 'Ce weekend était trop bien. Blabla…',
		  'date'    => new \Datetime()),
		array(
		  'titre'   => 'Repetition du National Day de Singapour',
		  'id'      => 2,
		  'auteur' => 'winzou',
		  'contenu' => 'Bientôt prêt pour le jour J. Blabla…',
		  'date'    => new \Datetime()),
		array(
		  'titre'   => 'Chiffre d\'affaire en hausse',
		  'id'      => 3, 
		  'auteur' => 'M@teo21',
		  'contenu' => '+500% sur 1 an, fabuleux. Blabla…',
		  'date'    => new \Datetime())
	  )
	));
 
    // Mais pour l'instant, on ne fait qu'appeler le template
    //return $this->render('SdzBlogBundle:Blog:index.html.twig');
  }
   
   
  public function voirAction($id)
  {
    // On récupère l'EntityManager
    $em = $this->getDoctrine()
               ->getManager();
 
    // On récupère l'entité correspondant à l'id $id
    $article = $em->getRepository('SdzBlogBundle:Article')
                  ->find($id);
 
    if ($article === null) {
      throw $this->createNotFoundException('Article[id='.$id.'] inexistant.');
    }
 
    // On récupère les articleCompetence pour l'article $article
    $liste_articleCompetence = $em->getRepository('SdzBlogBundle:ArticleCompetence')
                            ->findByArticle($article->getId());
 
    // Puis modifiez la ligne du render comme ceci, pour prendre en compte les articleCompetence :
    return $this->render('SdzBlogBundle:Blog:voir.html.twig', array(
      'article'          => $article,
      'liste_articleCompetence'  => $liste_articleCompetence,
      // … et évidemment les autres variables que vous pouvez avoir
    ));
  }
   
  public function ajouterAction()
  {
    // On récupére l'EntityManager
    $em = $this->getDoctrine()
               ->getManager();
 
    // Création de l'entité Article
    $article = new Article();
    $article->setTitre('Mon dernier weekend');
    $article->setContenu("C'était vraiment super et on s'est bien amusé.");
    $article->setAuteur('winzou');
 
    // Dans ce cas, on doit créer effectivement l'article en bdd pour lui assigner un id
    // On doit faire cela pour pouvoir enregistrer les ArticleCompetence par la suite
    $em->persist($article);
    $em->flush(); // Maintenant, $article a un id défini
 
    // Les compétences existent déjà, on les récupère depuis la bdd
    $liste_competences = $em->getRepository('SdzBlogBundle:Competence')
                            ->findAll(); // Pour l'exemple, notre Article contient toutes les Competences
 
    // Pour chaque compétence
    foreach($liste_competences as $i => $competence)
    {
      // On crée une nouvelle « relation entre 1 article et 1 compétence »
      $articleCompetence[$i] = new ArticleCompetence;
 
      // On la lie à l'article, qui est ici toujours le même
      $articleCompetence[$i]->setArticle($article);
      // On la lie à la compétence, qui change ici dans la boucle foreach
      $articleCompetence[$i]->setCompetence($competence);
 
      // Arbitrairement, on dit que chaque compétence est requise au niveau 'Expert'
      $articleCompetence[$i]->setNiveau('Expert');
 
      // Et bien sûr, on persiste cette entité de relation, propriétaire des deux autres relations
      $em->persist($articleCompetence[$i]);
    }
 
    // On déclenche l'enregistrement
    $em->flush();
     
    // Reste de la méthode qu'on avait déjà écrit
    if ($this->getRequest()->getMethod() == 'POST') {
      $this->get('session')->getFlashBag()->add('info', 'Article bien enregistré');
      return $this->redirect( $this->generateUrl('sdzblog_voir', array('id' => $article->getId())) );
    }
 
    return $this->render('SdzBlogBundle:Blog:ajouter.html.twig');
  }
   
  public function modifierAction($id)
  {
    // On récupère l'EntityManager
    $em = $this->getDoctrine()
               ->getManager();
 
    // On récupère l'entité correspondant à l'id $id
    $article = $em->getRepository('SdzBlogBundle:Article')
                  ->find($id);
 
    if ($article === null) {
      throw $this->createNotFoundException('Article[id='.$id.'] inexistant.');
    }
 
    // On récupère toutes les catégories :
    $liste_categories = $em->getRepository('SdzBlogBundle:Categorie')
                           ->findAll();
 
    // On boucle sur les catégories pour les lier à l'article
    foreach($liste_categories as $categorie)
    {
      $article->addCategorie($categorie);
    }
 
    // Inutile de persister l'article, on l'a récupéré avec Doctrine
 
    // Étape 2 : On déclenche l'enregistrement
    $em->flush();
 
    return new Response('OK');
         
    // Puis modifiez la ligne du render comme ceci, pour prendre en compte l'article :
    //return $this->render('SdzBlogBundle:Blog:modifier.html.twig', array(
      //'article' => $article
    //));
  }
 
  public function supprimerAction($id)
  {
    // On récupère l'EntityManager
    $em = $this->getDoctrine()
               ->getManager();
 
    // On récupère l'entité correspondant à l'id $id
    $article = $em->getRepository('SdzBlogBundle:Article')
                  ->find($id);
 
    if ($article === null) {
      throw $this->createNotFoundException('Article[id='.$id.'] inexistant.');
    }
     
    // On récupère toutes les catégories :
    $liste_categories = $em->getRepository('SdzBlogBundle:Categorie')
                           ->findAll();
     
    // On enlève toutes ces catégories de l'article
    foreach($liste_categories as $categorie)
    {
      // On fait appel à la méthode removeCategorie() dont on a parlé plus haut
      // Attention ici, $categorie est bien une instance de Categorie, et pas seulement un id
      $article->removeCategorie($categorie);
    }
 
    // On n'a pas modifié les catégories : inutile de les persister
     
    // On a modifié la relation Article - Categorie
    // Il faudrait persister l'entité propriétaire pour persister la relation
    // Or l'article a été récupéré depuis Doctrine, inutile de le persister
   
    // On déclenche la modification
    $em->flush();
 
    return new Response('OK');
  }
  public function menuAction()
  {
    // On fixe en dur une liste ici, bien entendu par la suite on la récupérera depuis la BDD !
    $liste = array(
      array('id' => 2, 'titre' => 'Mon dernier weekend !'),
      array('id' => 5, 'titre' => 'Sortie de Symfony2.1'),
      array('id' => 9, 'titre' => 'Petit test')
    );
         
    return $this->render('SdzBlogBundle:Blog:menu.html.twig', array(
      'liste_articles' => $liste // C'est ici tout l'intérêt : le contrôleur passe les variables nécessaires au template !
    ));
  }
}