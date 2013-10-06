<?php
// src/Sdz/BlogBundle/Controller/BlogController.php
// Attention à bien ajouter ce use en début de contrôleur

namespace Sdz\BlogBundle\Controller;
 
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Httpfoundation\Response;
use Sdz\BlogBundle\Entity\Article;
use Sdz\BlogBundle\Entity\Image;
use Sdz\BlogBundle\Entity\Commentaire;
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
 
    if($article === null)
    {
      throw $this->createNotFoundException('Article[id='.$id.'] inexistant.');
    }
 
    // On récupère la liste des commentaires
    $liste_commentaires = $em->getRepository('SdzBlogBundle:Commentaire')
                             ->findAll();
 
    // Puis modifiez la ligne du render comme ceci, pour prendre en compte l'article :
    return $this->render('SdzBlogBundle:Blog:voir.html.twig', array(
      'article'        => $article,
      'liste_commentaires' => $liste_commentaires
    ));
  }
   
  public function ajouterAction()
  {
    // Création de l'entité Article
    $article = new Article();
    $article->setTitre('Mon dernier weekend');
    $article->setContenu("C'était vraiment super et on s'est bien amusé.");
    $article->setAuteur('winzou');
 
    // Création d'un premier commentaire
    $commentaire1 = new Commentaire();
    $commentaire1->setAuteur('winzou');
    $commentaire1->setContenu('On veut les photos !');
 
    // Création d'un deuxième commentaire, par exemple
    $commentaire2 = new Commentaire();
    $commentaire2->setAuteur('Choupy');
    $commentaire2->setContenu('Les photos arrivent !');
 
    // On lie les commentaires à l'article
    $commentaire1->setArticle($article);
    $commentaire2->setArticle($article);
 
    // On récupère l'EntityManager
    $em = $this->getDoctrine()->getManager();
 
    // Étape 1 : On persiste les entités
    $em->persist($article);
    // Pour cette relation pas de cascade, car elle est définie dans l'entité Commentaire et non Article
    // On doit donc tout persister à la main ici
    $em->persist($commentaire1);
    $em->persist($commentaire2);
 
    // Étape 2 : On déclenche l'enregistrement
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
    // Ici, on récupérera l'article correspondant à $id
 
    // Ici, on s'occupera de la création et de la gestion du formulaire
 
    $article = array(
      'id'      => 1,
      'titre'   => 'Mon weekend a Phi Phi Island !',
      'auteur'  => 'winzou',
      'contenu' => 'Ce weekend était trop bien. Blabla…',
      'date'    => new \Datetime()
    );
         
    // Puis modifiez la ligne du render comme ceci, pour prendre en compte l'article :
    return $this->render('SdzBlogBundle:Blog:modifier.html.twig', array(
      'article' => $article
    ));
  }
 
  public function supprimerAction($id)
  {
    // Ici, on récupérera l'article correspondant à $id
 
    // Ici, on gérera la suppression de l'article en question
 
    return $this->render('SdzBlogBundle:Blog:supprimer.html.twig');
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