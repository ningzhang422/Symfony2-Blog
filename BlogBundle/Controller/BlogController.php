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

// N'oubliez pas d'ajouter le ArticleType
use Sdz\BlogBundle\Form\ArticleType;
use Sdz\BlogBundle\Form\ArticleEditType;
use Sdz\BlogBundle\Bigbrother\BigbrotherEvents;
use Sdz\BlogBundle\Bigbrother\MessagePostEvent;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


class BlogController extends Controller
{
  public function indexAction($page)
  {
    // On ne sait pas combien de pages il y a
    // Mais on sait qu'une page doit être supérieure ou égale à 1
    // Bien sûr pour le moment on ne se sert pas (encore !) de cette variable
    if ($page < 1) {
      // On déclenche une exception NotFoundHttpException 
      // Cela va afficher la page d'erreur 404
      // On pourra la personnaliser plus tard
      throw $this->createNotFoundException('Page inexistante (page = '.$page.')');
    }
 
    // Pour récupérer la liste de tous les articles : on utilise findAll()
    $articles = $this->getDoctrine()
                     ->getManager()
                     ->getRepository('SdzBlogBundle:Article')
                     ->getArticles(2, $page);
 
    // L'appel de la vue ne change pas
    return $this->render('SdzBlogBundle:Blog:index.html.twig', array(
      'articles' => $articles,
      'page'       => $page,
      'nombrePage' => ceil(count($articles)/2)
    ));
  }
 
 
  public function voirAction(Article $article)
  {
    // À ce stade, la variable $article contient une instance de la classe Article
    // Avec l'id correspondant à l'id contenu dans la route !
 
    // On récupère ensuite les articleCompetence pour l'article $article
    // On doit le faire à la main pour l'instant, car la relation est unidirectionnelle
    // C'est-à-dire que $article->getArticleCompetences() n'existe pas !
    $liste_articleCompetence = $this->getDoctrine()
                                   ->getManager()
                                   ->getRepository('SdzBlogBundle:ArticleCompetence')
                                   ->findByArticle($article->getId());
 
    // Puis modifiez la ligne du render comme ceci, pour prendre en compte les variables :
    return $this->render('SdzBlogBundle:Blog:voir.html.twig', array(
      'article'                 => $article,
      'liste_articleCompetence' => $liste_articleCompetence,
      // Pas besoin de passer les commentaires à la vue, on pourra y accéder via {{ article.commentaires }}
      // 'liste_commentaires'   => $article->getCommentaires()
    ));
  }
  /**
   * @ParamConverter("date", options={"format": "d-m-Y"})
   */
  public function voirListeAction(\Datetime $date)
  {
    // À ce stade, la variable $article contient une instance de la classe Article
    // Avec l'id correspondant à l'id contenu dans la route !

    // On récupère ensuite les articleCompetence pour l'article $article
    // On doit le faire à la main pour l'instant, car la relation est unidirectionnelle
    // C'est-à-dire que $article->getArticleCompetences() n'existe pas !
    $liste_articles = $this->getDoctrine()
                                   ->getManager()
                                   ->getRepository('SdzBlogBundle:Article')
                                   ->getArticlesByDate($date);
 
    // Puis modifiez la ligne du render comme ceci, pour prendre en compte les variables :
    return $this->render('SdzBlogBundle:Blog:voirListe.html.twig', array(
      'liste_articles_by_date'                 => $liste_articles,
      // Pas besoin de passer les commentaires à la vue, on pourra y accéder via {{ article.commentaires }}
      // 'liste_commentaires'   => $article->getCommentaires()
    ));
  }
 
  /**
   * @Secure(roles="ROLE_AUTEUR")
   */
  public function ajouterAction()
  {
    
   	
    // On crée un objet Article
	  $article = new Article();
	  $form = $this->createForm(new ArticleType, $article);
	  
	  // On récupère la requête
		$request = $this->get('request');
	   
		if ($request->getMethod() == 'POST') {
		  // On fait le lien Requête <-> Formulaire
		  // À partir de maintenant, la variable $article contient les valeurs entrées dans le formulaire par le visiteur
		  $form->bind($request);
	 
		  // On vérifie que les valeurs entrées sont correctes
		  if ($form->isValid()) {
			// Ici : On traite manuellement le fichier uploadé
  			// $article->getImage()->upload();  
			// On crée l'évènement avec ses 2 arguments
			//var_dump($this->getUser());
			  $event = new MessagePostEvent($article->getContenu(), $this->getUser());
		 
			  // On déclenche l'évènement
			  $this->get('event_dispatcher')
				   ->dispatch(BigbrotherEvents::onMessagePost, $event);
		 
			  // On récupère ce qui a été modifié par le ou les listeners, ici le message
			  $article->setContenu($event->getMessage());
			// On l'enregistre notre objet $article dans la base de données
			$em = $this->getDoctrine()->getManager();
			$em->persist($article);
			$em->flush();
	 
			// Ici, on s'occupera de la création et de la gestion du formulaire
	   
			$this->get('session')->getFlashBag()->add('info', 'Article bien enregistré');
			
			// On redirige vers la page de visualisation de l'article nouvellement créé
			return $this->redirect($this->generateUrl('sdzblog_voir', array('id' => $article->getId())));
		  }
		  
		}
	 
	  // On passe la méthode createView() du formulaire à la vue afin qu'elle puisse afficher le formulaire toute seule
	  return $this->render('SdzBlogBundle:Blog:ajouter.html.twig', array(
		'form' => $form->createView()
	  ));
  }
 
  public function modifierAction(Article $article)
  {
    $form = $this->createForm(new ArticleEditType, $article);
	  
	  // On récupère la requête
		$request = $this->get('request');
	   
		if ($request->getMethod() == 'POST') {
		  // On fait le lien Requête <-> Formulaire
		  // À partir de maintenant, la variable $article contient les valeurs entrées dans le formulaire par le visiteur
		  $form->bind($request);
	 
		  // On vérifie que les valeurs entrées sont correctes
		  // (Nous verrons la validation des objets en détail dans le prochain chapitre)
		  if ($form->isValid()) {
			// On l'enregistre notre objet $article dans la base de données
			$em = $this->getDoctrine()->getManager();
			$em->persist($article);
			$em->flush();
	 
			// Ici, on s'occupera de la création et de la gestion du formulaire
	   
			$this->get('session')->getFlashBag()->add('info', 'Article bien enregistré');
			
			// On redirige vers la page de visualisation de l'article nouvellement créé
			return $this->redirect($this->generateUrl('sdzblog_voir', array('id' => $article->getId())));
		  }
		  
		}
 
    // Ici, on s'occupera de la création et de la gestion du formulaire
 
    return $this->render('SdzBlogBundle:Blog:modifier.html.twig', array(
      'article' => $article,
	  'form' => $form->createView()
    ));
  }
 
  public function supprimerAction(Article $article)
  {
    // On crée un formulaire vide, qui ne contiendra que le champ CSRF
    // Cela permet de protéger la suppression d'article contre cette faille
    $form = $this->createFormBuilder()->getForm();
 
    $request = $this->getRequest();
    if ($request->getMethod() == 'POST') {
      $form->bind($request);
 
      if ($form->isValid()) {
        // On supprime l'article
        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();
 
        // On définit un message flash
        $this->get('session')->getFlashBag()->add('info', 'Article bien supprimé');
 
        // Puis on redirige vers l'accueil
        return $this->redirect($this->generateUrl('sdzblog_accueil'));
      }
    }
 
    // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
    return $this->render('SdzBlogBundle:Blog:supprimer.html.twig', array(
      'article' => $article,
      'form'    => $form->createView()
    ));
  }
 
  public function menuAction($nombre)
  {
    $liste = $this->getDoctrine()
                  ->getManager()
                  ->getRepository('SdzBlogBundle:Article')
                  ->findBy(
                    array(),          // Pas de critère
                    array('date' => 'desc'), // On trie par date décroissante
                    $nombre,         // On sélectionne $nombre articles
                    0                // À partir du premier
                  );
 
    return $this->render('SdzBlogBundle:Blog:menu.html.twig', array(
      'liste_articles' => $liste // C'est ici tout l'intérêt : le contrôleur passe les variables nécessaires au template !
    ));
  }
  
  public function traductionAction($name)
  {
    return $this->render('SdzBlogBundle:Blog:traduction.html.twig', array(
      'name' => $name
    ));
  }
}