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
	 
	  // On passe la méthode createView() du formulaire à la vue afin qu'elle puisse afficher le formulaire toute seule
	  return $this->render('SdzBlogBundle:Blog:ajouter.html.twig', array(
		'form' => $form->createView(),
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
 
  public function supprimerAction($id)
  {
    // On récupère l'EntityManager
    $em = $this->getDoctrine()
               ->getEntityManager();
 
    // On récupère l'entité correspondant à l'id $id
    $article = $em->getRepository('SdzBlogBundle:Article')
                  ->find($id);
     
    // Si l'article n'existe pas, on affiche une erreur 404
    if ($article == null) {
      throw $this->createNotFoundException('Article[id='.$id.'] inexistant');
    }
 
    if ($this->get('request')->getMethod() == 'POST') {
      // Si la requête est en POST, on supprimera l'article
       
      $this->get('session')->getFlashBag()->add('info', 'Article bien supprimé');
 
      // Puis on redirige vers l'accueil
      return $this->redirect( $this->generateUrl('sdzblog_accueil') );
    }
 
    // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
    return $this->render('SdzBlogBundle:Blog:supprimer.html.twig', array(
      'article' => $article
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
}